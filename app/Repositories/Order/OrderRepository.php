<?php
/**
 * Date 06/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Repositories\Order;

use App\Http\Requests\Order\OrderRequest;
use App\Models\Order;
use App\Models\Product;
use App\Transformer\OrderTransformer;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Lcobucci\JWT\Token\Parser;
use Ssiva\LaravelNotify\Events\OrderStatusUpdated;

class OrderRepository implements OrderInterface
{
    public function getAll(): Collection
    {
        return Order::with(['payment', 'orderStatus', 'user'])->all();
    }

    public function getByUUID($uuid): Model|Collection|Builder|array|null
    {
        return Order::with(['payment', 'orderStatus', 'user'])->find($uuid);
    }

    public function delete($uuid): bool
    {
        if (!$user = Order::find($uuid)) {
            return false;
        }
        return $user->delete();
    }

    public function processOrder($data): array
    {
        $items = $data;
        if(!is_array($data)){
            $items = json_decode("[$items]", true);
        }
        $orderDetails = ['amount' => 0];
        foreach ($items as $item) {
            $item = (object) $item;
            if ($product = Product::find($item->uuid)) {
                $item->price = $product->price;
                $item->uuid = $product->uuid;
                $item->product = $product->title;
                $amount = $item->price * $item->quantity;
                $orderDetails['products'][] = $item;
                $orderDetails['amount'] = $orderDetails['amount'] + $amount;
            }
        }
        return $orderDetails;
    }

    public function update($uuid, array $data): bool|array
    {
        $order = Order::find($uuid);
        if ($order->update($data)) {
            // Trigger order status change here
            if($orderUpdate = array_key_exists('order_status_uuid', $order->getChanges())){
                event(new OrderStatusUpdated($order->uuid, $order->orderStatus->title, $order->updated_at));
            }
            return $data;
        }
        return false;
    }

    public function create(array $data)
    {
        return Order::create($data);
    }

    public function getPaginated(array $data = []): LengthAwarePaginator
    {
        $orders = Order::with([
            'payment',
            'orderStatus',
            'user',
        ]);
        return $orders->orderBy($data['sortBy'], $data['desc'])->paginate((int)$data['limit'], page: $data['page']);
    }

    public function getOrderSummaries(array $data = [], $shipped = false): array
    {
        $data = $this->getDateRange($data);
        $orders = Order::when($data['dateRange'], function ($query, $value) {
            return $query->when($value['from'], function ($query, $val) {
                return $query->where('created_at', '>=', $val);
            })->when($value['to'], function ($query, $val) {
                return $query->where('created_at', '<=', $val);
            });
        })->when($data['customerUuid'], function ($query, $val) {
            return $query->where('user_uuid', $val);
        })->when($data['orderUuid'], function ($query, $val) {
            return $query->where('uuid', $val);
        })->when($shipped, function ($query) {
            return $query->whereNotNull('shipped_at');
        })->with([
            'orderStatus' => function ($query) {
                return $query->select('uuid', 'title', 'created_at', 'updated_at');
            },
            'user' => function ($query) {
                return $query->select([
                    'uuid',
                    'first_name',
                    'last_name',
                    'email',
                    'email_verified_at',
                    'avatar',
                    'address',
                    'phone_number',
                    'is_marketing',
                    'created_at',
                    'updated_at',
                    'last_login_at',
                ]);
            },
        ]);
        $orders->orderBy($data['sortBy'], $data['desc']);
        return (new OrderTransformer())->transformPaginator(
            $orders->paginate((int)$data['limit'], page: $data['page'])
        );
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function getDateRange(array $data): array
    {
        if ($data['fixRange']) {
            if ($data['fixRange'] === 'today') {
                $data['dateRange'] = [
                    'from' => Carbon::today()->toDateString(),
                    'to' => Carbon::tomorrow()->toDateString(),
                ];
            } elseif ($data['fixRange'] === 'monthly') {
                $data['dateRange'] = [
                    'from' => Carbon::now()->startOfMonth()->toDateString(),
                    'to' => Carbon::now()->endOfMonth()->toDateString(),
                ];
            } elseif ($data['fixRange'] === 'yearly') {
                $data['dateRange'] = [
                    'from' => Carbon::now()->startOfYear()->toDateString(),
                    'to' => Carbon::now()->endOfYear()->toDateString(),
                ];
            }
        }
        return $data;
    }

    /**
     * @param OrderRequest $request
     *
     * @return array
     */
    public function getOrderRequest(OrderRequest $request): array
    {
        $token = app(Parser::class, ['token' => $request->bearerToken()]);
        $request->merge(['user_uuid' => $token->claims()->get('user_uuid')]);
        return $request->except(['products']);
    }

    public function generateInvoice($order): bool|string
    {
        $customer = $order->user;
        $payment = $order->payment;
        $products = $order->products;
        $paymentType = $order->payment->paymentType;
        $details = json_decode($order->payment->details);
        $address = json_decode($order->address);
        // $filename = "Invoice {$order->uuid}-{$customer->uuid}.pdf";
        $filename = "$order->uuid.pdf";
        // Set up the PDF document
        $pdf = new Dompdf();
        $arguments = compact('order', 'customer', 'payment', 'products', 'paymentType', 'details', 'address');
        $view = view('order.invoice', $arguments);
        // Load external CSS styles
        $pdf->getOptions()->setIsFontSubsettingEnabled(true);
        $pdf->getOptions()->setIsPhpEnabled(true);
        $pdf->getOptions()->setIsHtml5ParserEnabled(true);
        $pdf->getOptions()->setChroot(public_path('app'));
        $pdf->loadHtml($view);
        // Customize the PDF settings
        $pdf->setPaper('A4');
        // Generate the PDF and return the response
        $pdf->render();

        $invoice = file_put_contents($filename, $pdf->output());

        if ($orderInvoice = Storage::disk()->putFileAs('pet-shop/invoices', $filename, $filename)) {
            unlink(public_path($filename));
            return $orderInvoice;
        }
        return false;
    }


    public function updateOrderPayment($order, array $data)
    {
        $payment = $order->payment;
        if ($payment->update($data)) {
            return $order;
        }
        return false;
    }

}

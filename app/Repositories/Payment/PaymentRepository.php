<?php
/**
 * Date 06/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Repositories\Payment;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;
use Ssiva\LaravelStripe\Processors\StripePaymentProcessor;
use Stripe\Exception\ApiErrorException;

class PaymentRepository implements PaymentInterface
{
    public function getAll(): Collection
    {
        return Payment::all();
    }

    public function getByUUID($uuid)
    {
        return Payment::find($uuid);
    }

    public function delete($uuid): bool
    {
        if (!$user = Payment::find($uuid)) {
            return false;
        }
        return $user->delete();
    }

    /**
     * @throws ApiErrorException
     */
    public function create(array $data)
    {
        $payment = Payment::create($data);
        return $this->processPayment($data['order_uuid'], $payment);
    }

    /**
     * @throws ApiErrorException
     */
    public function update($uuid, array $data)
    {
        $payment = Payment::find($uuid)->update($data);
        return $this->processPayment($data['order_uuid'], $payment);
    }

    public function getPaginated(array $data = [])
    {
        return Payment::orderBy($data['sortBy'], $data['desc'])
            ->paginate((int)$data['limit'], page: $data['page']);
    }

    /**
     * @param $order_uuid
     * @param $payment
     * @return mixed
     * @throws ApiErrorException
     */
    public function processPayment($order_uuid, $payment): mixed
    {
        $order = Order::find($order_uuid)->orderPayment()->associate($payment);
        $processor = new StripePaymentProcessor();
        $checkoutData = $processor->createStripeCheckout($order->products, $order_uuid);
        $order->stripe = $checkoutData;

        return $order;
    }
}

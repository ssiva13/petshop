<?php
/**
 * Date 07/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Transformer;

use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;

class OrderTransformer extends TransformerAbstract
{
    public function transform(Order $order): array
    {
        return [
            'amount' => $order->amount,
            'created_at' => $order->created_at,
            'delivery_fee' => $order->delivery_fee,
            'products' => $order->products,
            'shipped_at' => $order->shipped_at,
            'uuid' => $order->uuid,
            'order_status' => $order->orderStatus,
            'user' => $order->user,
        ];
    }

    public function transformPaginator(LengthAwarePaginator $paginator): array

    {
        return [
            'current_page' => $paginator->currentPage(),
            'data' => $this->getCollection($paginator),
            'first_page_url' => $paginator->url(1),
            'from' => $paginator->previousPageUrl(),
            'last_page' => $paginator->lastPage(),
            'last_page_url' => $paginator->url($paginator->lastPage()),
            'links' => $paginator->linkCollection(),
            'next_page_url' => $paginator->nextPageUrl(),
            'path' => $paginator->path(),
            'per_page' => $paginator->perPage(),
            'prev_page_url' => $paginator->previousPageUrl(),
            'to' => $paginator->nextPageUrl(),
            'total' => $paginator->total(),
        ];
    }

    /**
     * @param \Illuminate\Pagination\LengthAwarePaginator $paginator
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCollection(LengthAwarePaginator $paginator): Collection
    {
        return collect($paginator->getCollection())->map(function ($order) {
            return $this->transform($order);
        });
    }
}

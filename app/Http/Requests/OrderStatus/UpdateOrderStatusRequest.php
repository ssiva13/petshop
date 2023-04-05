<?php
/**
 * Date 06/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Http\Requests\OrderStatus;

class UpdateOrderStatusRequest extends OrderStatusRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:50',
            'slug' => 'required|string|max:50',
        ];
    }

}

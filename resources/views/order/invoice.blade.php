@php use Carbon\Carbon; @endphp

    <!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

    <title> Invoice {{ $order->uuid }}-{{ $customer->uuid }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    {{--    @vite(['resources/sass/app.scss', 'resources/js/app.js'])--}}
</head>
<body>
<style>
    h5 {
        font-weight: bold;
        margin-bottom: 10px;
    }

    hr {
        border: none;
        border-top: 1px solid #ccc;
        margin-top: 15px;
        margin-bottom: 15px;
    }

    .font-bolder {
        font-weight: bold;
    }

    /* Style the invoice container */
    .invoice-container {
        width: 90%;
        margin: 0 auto;
        border: 2px double rgba(0, 74, 87, 0.44);
    }

    /* Style the invoice table */
    .invoice-table-bordered {
        border: 2px solid rgba(0, 74, 87, 0.44);
        mso-border-shadow: yes;
    }

    .invoice-table-order {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1rem;
    }

    .invoice-table-order th,
    .invoice-table-order td {
        padding: 8px;
        vertical-align: top;
        border: 0;
        width: 50%;
    }

    .invoice-table-order th {
        text-align: left;
        background-color: #f2f2f2;
    }


    /*////////////////*/
    .invoice-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1rem;
    }

    .invoice-summary-table {
        width: 50%;
        margin-left: 50%;
        border-collapse: collapse;
        margin-bottom: 1rem;
        border: 0;
        text-align: left;
    }

    .invoice-summary-table th,
    .invoice-summary-table td,
    .invoice-table th,
    .invoice-table td {
        padding: 8px;
        border: 1px solid #ccc;
    }

    .invoice-table th {
        text-align: left;
        background-color: #f2f2f2;
    }

    .invoice-table-group-divider tbody tr:last-child td {
        border-bottom: none;
    }

    .invoice-12 {
        width: 100%;
        flex: 0 0 auto;
        margin: 0 auto;
        font-size: 12px;
    }

    hr.hr-5 {
        border: 0;
        border-top: 3px double #8c8c8c;
    }

    .hr-5 {
        border: 0;
        border-top: 3px double #8c8c8c;
    }

</style>
<div id="invoice">
    <div class="invoice-container">

        <div class="invoice-12">
            <table class="invoice-table-order invoice-table-group-divider">
                <tr>
                    <th style="background: none"> {{ config('app.name') }} Invoice</th>
                    <td><span class="font-bolder">Invoice No:</span> {{ $order->uuid }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <span
                            class="font-bolder">Date:</span> {{ Carbon::parse($order->created_at)->toFormattedDateString() }}
                    </td>
                </tr>
            </table>

        </div>

        <hr class="hr-5">

        <div class="invoice-12" style=" ">
            <table class="invoice-table-order invoice-table-group-divider">
                <tr>
                    <td>
                        <table class="invoice-table-order invoice-table-bordered">
                            <thead>
                            <tr>
                                <th colspan="2">
                                    <h5>Customer Details:</h5>
                                </th>
                            </tr>
                            <tr>
                                <td><span class="font-bolder">Name: </span></td>
                                <td>{{ $customer->first_name }} {{ $customer->last_name }}</td>
                            </tr>
                            <tr>
                                <td><span class="font-bolder">ID: </span></td>
                                <td>{{ $customer->uuid }}</td>
                            </tr>
                            <tr>
                                <td><span class="font-bolder">Phone Number: </span></td>
                                <td>{{ $customer->phone_number }}</td>
                            </tr>
                            <tr>
                                <td><span class="font-bolder">Email: </span></td>
                                <td>{{ $customer->email }}</td>
                            </tr>
                            <tr>
                                <td><span class="font-bolder">Address: </span></td>
                                <td>{{ $customer->address }}</td>
                            </tr>
                            </thead>
                        </table>
                    </td>
                    <td>
                        <table class="invoice-table-order invoice-table-bordered">
                            <thead>
                            <tr>
                                <th colspan="2">
                                    <h5>Billing/Shipping Details:</h5>
                                </th>
                            </tr>
                            <tr>
                                <td><span class="font-bolder">Billing: </span></td>
                                <td>{{ $address->billing }}</td>
                            </tr>
                            <tr>
                                <td><span class="font-bolder">Shipping: </span></td>
                                <td>{{ $address->shipping }}</td>
                            </tr>
                            <tr>
                                <td colspan="2"><span class="font-bolder">Payment Details</span></td>
                            </tr>
                            <tr>
                                <td><span class="font-bolder">Payment Method: </span></td>
                                <td>{{ $paymentType->title }}</td>
                            </tr>

                            @if($payment->type === 'cash_on_delivery')
                                <tr>
                                    <td><span class="font-bolder">Customer Name: </span></td>
                                    <td>{{ $details->first_name }} {{ $details->last_name }}</td>
                                </tr>
                                <tr>
                                    <td><span class="font-bolder">Address: </span></td>
                                    <td>{{ $details->address }}</td>
                                </tr>
                            @elseif($payment->type === 'credit_card')
                                <tr>
                                    <td><span class="font-bolder">Customer Name: </span></td>
                                    <td>{{ $details->holder_name }}</td>
                                </tr>
                                <tr>
                                    <td><span class="font-bolder">Number: </span></td>
                                    <td>{{ str_repeat("*", 12) . substr($details->number, 12) }}</td>
                                </tr>
                            @elseif($payment->type === 'bank_transfer')
                                <tr>
                                    <td><span class="font-bolder">Customer Name: </span></td>
                                    <td>{{ $details->name }}</td>
                                </tr>
                                <tr>
                                    <td><span class="font-bolder">SWIFT Code: </span></td>
                                    <td>{{ $details->swift }}</td>
                                </tr>
                                <tr>
                                    <td><span class="font-bolder">Bank Account: </span></td>
                                    <td>{{ str_repeat("*", 8) . substr($details->iban, 8) }}</td>
                                </tr>
                            @endif
                            </thead>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <hr class="hr-5">

        <div class="invoice-12">
            <table class="invoice-table invoice-table-group-divider">
                <thead>
                <tr>
                    <th>#</th>
                    <th>ID</th>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
                </thead>
                <tbody>
                @foreach($products as $count => $product)
                    <tr>
                        <td> {{ $count+1 }}</td>
                        <td> {{ $product['uuid'] }}</td>
                        <td> {{ $product['product'] }}</td>
                        <td> {{ number_format($product['price'], 2) }} USD</td>
                        <td> {{ $product['quantity'] }}</td>
                        <td> {{ number_format($product['quantity'] * $product['price'], 2) }} USD</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <hr class="hr-5">

        <div class="invoice-12">
            <table class="invoice-summary-table ">
                <thead>
                <tr>
                    <th><span class="text-black font-bolder">Subtotal</span></th>
                    <td>{{ number_format($order->amount, 2) }} USD</td>
                </tr>
                <tr>
                    <th><span class="text-black font-bolder"> Delivery fee</span></th>
                    <td>{{ number_format($order->delivery_fee, 2) }} USD</td>
                </tr>
                <tr>
                    <th><span class="text-black font-bolder">TOTAL</span></th>
                    <td>{{ number_format( $order->delivery_fee + $order->amount, 2) }} USD</td>
                </tr>
                </thead>
            </table>

        </div>

    </div>


</div>
</body>
</html>

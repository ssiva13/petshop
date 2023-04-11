<?php
/**
 * Date 10/04/2023
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

return [
    'default_currency' => env('DEFAULT_CURRENCY', 'EUR'),
    'exchange_rate_url' => env(
        'CURRENCY_EXCHANGE_URL',
        'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml'
    ),
];

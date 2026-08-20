<?php

if (!function_exists('generate_order_number')) {
    function generate_order_number($prefix = 'ORD'): string
    {
        $prefix = config('app.order_prefix', $prefix);
        return $prefix . '-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    }
}

if (!function_exists('generate_invoice_number')) {
    function generate_invoice_number($prefix = 'INV'): string
    {
        $prefix = config('app.invoice_prefix', $prefix);
        return $prefix . '-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    }
}

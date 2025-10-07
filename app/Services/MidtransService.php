<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Notification;
use Midtrans\Snap;

class MidtransService {

    // 
    public function __construct()
    {
        Config::$serverKey = config('midtrans.serverKey');
        Config::$clientKey = config('midtrans.clientKey');
        Config::$isProduction = config('midtrans.isProduction');
        Config::$isSanitized = config('midtrans.isSanitized');
    }

    // fungsi snap token
    public function createSnapToken(array $params)
    {
        try {
            return Snap::getSnapToken($params);
        } catch (\Exception $e) {
            Log::error('Failed to create snap token: ' .$e->getMessage());
            throw $e;
        }
    }

    // fungsi untuk menangani notifikasi
    public function handleNotification()
    {
        try {
            $notification = new Notification();
            return [
                'order_id' => $notification->order_id,
                'transaction_status' => $notification->transaction_status,
                'gross_amount' => $notification->gross_amount,
                'custom_field1' => $notification->custom_field1,
                'custom_field2' => $notification->custom_field2,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to handle notification: ' . $e->getMessage());
            throw $e;
        }
    }
}
<?php

namespace App\Services;

use App\Models\Installment;
use App\Models\MortgageRequest;
use App\Services\MidtransService;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    // fungsi untuk create payment
    public function createPayment(MortgageRequest $mortgageRequest)
    {
        // data untuk yang di butuhkan
        $sub_total_amount = $mortgageRequest->monthly_amount;
        $insurance = 900000;
        $total_tax_amount = round($sub_total_amount * 0.11 );
        $gross_amount = $sub_total_amount + $insurance + $total_tax_amount;

        // data yang di kirim ke midtrans
        $params = [
            'transaction_details' => [
                'order_id' => 'ORDER-' .uniqid(),
                'gross_amount' => round($gross_amount),
            ],
            'customer_details'=> [
                'frist_name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'phone' => auth()->user()->phone,
            ],
            'item_details' => [
                [
                    'id' => $mortgageRequest->id,
                    'price' => $gross_amount,
                    'quantity' => 1,
                    'name' => 'Mortgage Payment Of ' .$mortgageRequest->house->name,
                ],
            ],
            'custom_field1' => auth()->user()->id,
            'custom_field2' => $mortgageRequest->id,
        ];

        // deklarasi snap token
        return $this->midtransService->createSnapToken($params);
    }

    // fungsi peroses notification midtrans
    public function processNotification()
    {
        Log::info('Starting payment notification processing');
        $notification = $this->midtransService->handleNotification();
        Log::info("Notification received: ", $notification);
        // data yang di kirim dari midtrans
        $transactionStatus = $notification['transaction_status'];
        $grossAmount = $notification['gross_amount'];
        Log::info("Transaction status: {$transactionStatus}, Amount: {$grossAmount}");
        // kondisi untuk status pembayaran
        if($transactionStatus == 'settlement' || $transactionStatus == 'capture'){
            $mortgageRequestId = $notification['custom_field2'];
            Log::info("Creating installment for request ID: {$mortgageRequestId}");
            
            $mortgageRequest = MortgageRequest::find($mortgageRequestId);
            if($mortgageRequest){
                $this->createInstallment($mortgageRequest, $grossAmount);
            } else {
                Log::error('MortgageRequest not found for ID: ' . $mortgageRequest);
            }
        }
    }

    // fungsi untuk mengembalikan data ke tabel installments
    public function createInstallment(MortgageRequest $mortgageRequest, $gross_amount)
    {
        $lastInstalment = $mortgageRequest->installments()
            ->where('is_paid', true)
            ->orderBy('no_of_payment', 'desc')
            ->first();
        $previous_remaining = $lastInstalment ? $lastInstalment->remaining_loan_amount : $mortgageRequest->loan_interest_total_amount;
        // calculate installment amount
        $sub_total_amount = $mortgageRequest->monthly_amount;
        $insurance = 900000;
        $total_tax_amount = round($sub_total_amount * 0.11 );
        
        $remaining_loan = max($previous_remaining - $sub_total_amount, 0);
        return Installment::create([
            'mortgage_request_id' => $mortgageRequest->id,
            'no_of_payment' => $mortgageRequest->installments()->count() + 1,
            'total_taxt_amount' => $total_tax_amount,
            'sub_total_amount' => $sub_total_amount,
            'insurance_amount' => $insurance,
            'grand_total_amount' => $gross_amount,
            'is_paid' => true,
            'payment_type' => 'Midtrans',
            'remaining_loan_amount' => $remaining_loan,
        ]);
    }

}
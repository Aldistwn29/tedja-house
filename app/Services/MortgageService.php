<?php

namespace App\Services;

use App\Models\Installment;
use App\Models\Interest;
use App\Models\MortgageRequest;
use Illuminate\Support\Facades\Auth;

class MortgageService
{
    // fungsi untuk handle request
    public function handleRequest($request)
    {
        $validatedData = $request->validate([
            'dp_percentage' => 'required|integer|min:0|max:100',
            'interest_id' => 'required|integer|exists:interests,id',
            'document' => 'required|file|mimes:pdf,png,docx|max:2048',
        ]);

        $interest = Interest::findOrFail($validatedData['interest_id']);
        $house = $interest->house;

        $mortgageDetails = $this->calculatedMortgageDetails($house, $interest, $validatedData['dp_percentage']);
        $document_path = $this->UploadImage($request);
        return $this->createMortgageRequest($mortgageDetails, $document_path);
    }
    // fungsi untuk calculated
    public function calculatedMortgageDetails($house, $interest, $dpPercentage)
    {
        $housePrice = $house->price;
        $dpTotalAmount = $housePrice * ($dpPercentage / 100);
        $loanTotalAmount = $housePrice - $dpTotalAmount;
        $durationYears = $interest->duration;
        $totalPayment = $durationYears * 12;
        $monthlyInterestRate = $interest->interest / 100 / 12;

        // Amortization formula for monthly payments
        $numerator = $loanTotalAmount * $monthlyInterestRate * pow(1 + $monthlyInterestRate, $totalPayment);
        $denominator = pow(1 + $monthlyInterestRate, $totalPayment) - 1;
        $monthlyAmount = $denominator > 0 ? $numerator / $denominator : 0;

        $loanInterestTotalAmount = $monthlyAmount * $totalPayment;

        return compact(
            'house',
            'interest',
            'housePrice',
            'dpTotalAmount',
            'dpPercentage',
            'loanTotalAmount',
            'monthlyAmount',
            'loanInterestTotalAmount',
        );
    }
    // Fungsi untuk upload image
    public function UploadImage($request)
    {
        if ($request->hasFile('document')) {
            return $request->file('document')->store('document', 'public');
        }
        return null;
    }

    public function createMortgageRequest($details, $documentPath)
    {
        $mortgageRequest = MortgageRequest::create([
            'user_id' => Auth::id(),
            'house_id' =>$details['house']->id,
            'interest_id'=> $details['interest']->id,

            'interest'=> $details['interest']->interest,
            'duration'=> $details['interest']->duration,
            'bank_name'=> $details['interest']->bank->name,

            'dp_percentage'=> $details['dpPercentage'],
            'house_price'=> $details['housePrice'],
            
            'dp_total_amount'=>$details['dpTotalAmount'],

            'loan_total_amount'=>$details['loanTotalAmount'],
            'loan_interest_total_amount'=>$details['loanInterestTotalAmount'],

            'monthly_amount'=>$details['monthlyAmount'],

            'status'=>'Waiting for Bank',

            'document'=>$documentPath,
        ]);

        session(['interest_id' => $details['interest']->id]);
        return $mortgageRequest;
    }
    // fungsi untuk mendaptkan data dari session interest
    public function getInterstFromSession()
    {
        $interestId = session('interest_id');
        return $interestId ? Interest::findOrFail($interestId) : null;
    }

    public function getMortgageUser($userId)
    {
        return MortgageRequest::with(['house', 'house.city', 'house.category'])
            ->where('user_id', $userId)
            ->get();
    }

    public function getMortgageDetails(MortgageRequest $mortgageRequest)
    {
        $mortgageRequest->load(['house.city', 'house.category', 'installments']);
        $monthlyPayment = $mortgageRequest->monthly_amount;
        $insurance = 900000;
        $totalTaxAmount = round($monthlyPayment * 0.11);
        return compact('mortgageRequest', 'totalTaxAmount', 'insurance');
    }

    public function getInstallmentDetails(Installment $installment)
    {
        return $installment->load(['mortgageRequest.house.city']);
    }

    public function getInstallmentPaymentDetails(MortgageRequest $mortgageRequest)
    {
        $remainingLoanAmount = $mortgageRequest->remaining_loan_amount;
        $mortgageRequest->load(['house.city', 'house.category']);
        $monthlyPayment = $mortgageRequest->monthly_amount;
        $insurance = 900000;
        $totalTaxAmount = round($monthlyPayment * 0.11);
        $grandTotalAmount = $monthlyPayment + $insurance + $totalTaxAmount;
        $remainingLoanAmountAfterPayment = $remainingLoanAmount - $monthlyPayment;

        return compact(
            'mortgageRequest',
            'grandTotalAmount',
            'monthlyPayment',
            'totalTaxAmount',
            'insurance',
            'remainingLoanAmount',
            'remainingLoanAmountAfterPayment'
        );
    }

    public function getMorgageRequest($mortgageRequest)
    {
        return MortgageRequest::findOrFail($mortgageRequest);
    }
}

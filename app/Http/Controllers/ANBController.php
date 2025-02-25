<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class ANBController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getAccessToken()
    {
        $response = Http::asForm()->post(config('anb.auth_url'), [
            'grant_type'    => 'client_credentials',
            'client_id'     => config('anb.client_id'),
            'client_secret' => config('anb.client_secret'),
        ]);



        if ($response->successful()) {
            $data = $response->json();
            $user = User::updateOrCreate(
                ['anb_account_number' => '123456789'],
                [
                    'name' => 'Maryam',
                    'email' => 'maryam@gmil.com',
                    'password' => Hash::make('12345'),
                    'anb_token' => $data['access_token'],
                    'anb_token_expiry' => now()->addSeconds((int) $data['expires_in']),
                    'anb_refresh_token' => $data['refresh_token'] ?? null,
                ]
            );
        }
        return response()->json(['error' => 'Failed to retrieve access token'], 400);
    }

    public function withdrawFromBank(Request $request)
    {
        $accountNumber = $request->input('account_number');
        $amount = $request->input('amount');
        $user = User::where('anb_account_number', $accountNumber)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        if (now()->greaterThan($user->anb_token_expiry)) {
            return response()->json(['error' => 'Access Token expired. Please refresh token'], 401);
        }
        $response = Http::withToken($user->anb_token)->post('https://sandbox.anb.com.sa/v1/transactions/debit', [
            'account_number' => $user->anb_account_number,
            'amount' => $amount,
            'currency' => 'SAR',
            'description' => 'Withdraw from ANB'
            
        ]);


        if ($response->successful()) {
            
            return response()->json($response->json());
        }

        return response()->json(['error' => 'Transaction failed'], 400);
    }


    public function sendPayment(Request $request)
    {
        $debitAccount = $request->input('debitAccount');
        $amount = $request->input('amount');
        $user = User::where('anb_account_number', $debitAccount)->first();
    
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        if (now()->greaterThan($user->anb_token_expiry)) {
            return response()->json(['error' => 'Access Token expired. Please refresh token'], 401);
        }
        $paymentData = [
            "sequenceNumber" => time(),
            "valueDate" => now()->format('ymd'),
            "currency" => "SAR",
            "amount" => $amount,
            "orderingParty" => "SWAGGER",
            "feeIncluded" => false,
            "orderingPartyAddress1" => "An Nafel",
            "orderingPartyAddress2" => "Riyadh",
            "orderingPartyAddress3" => "Saudi Arabia",
            "debitAccount" => $debitAccount, 
            "destinationBankBIC" => "ARNBSARI",
            "channel" => "ANB",
            "creditAccount" => "0108061198800019",
            "beneficiaryName" => "Saud",
            "beneficiaryAddress1" => "KSA",
            "beneficiaryAddress2" => "Riyadh",
            "narrative" => "ANB To ANB Transfer",
            "transactionComment" => "ANB to ANB works",
            "purposeOfTransfer" => "38"
        ];
        $response = Http::withToken($user->anb_token)
            ->post('https://sandbox.anb.com.sa/v1/payment/json', $paymentData);
    
        if ($response->successful()) {
            return response()->json($response->json());
        }
    
        return response()->json(['error' => 'Transaction failed'], $response->status());
    }
    
    

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

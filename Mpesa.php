<?php
require __DIR__ . './vendor/autoload.php';

use Carbon\Carbon;

if (isset($_GET['amount']))
{
    stkPush($_GET[amount]);
}

function lipaNamMpesaPass()
{
    //Get the timestamp
    $timestamp = Carbon::rawParse('now') -> format(YmdHms);
    //Passkey
    $passKey = "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919";
    $businessShortCode = 174397;
    //Generate Password
    $mpesaPassword = base64_encode($businessShortCode.$passKey.$timestamp);

    return $mpesaPassword;
}

function newAccessToken()
{
    $consumer_key = "2sh2YA1fTzQwrZJthIrwLMoiOi3nhhal";
    $consumer_secret = "CKaCnw224K4Lc56w";
    $credentials = base64_encode($consumer_key.":".$consumer_secret);
    $url = "https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Basic " . $credentials, "Content-Type:application/json"));
    curl_setopt($curl, CURLINFO_HEADER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $curl_response = curl_exec($curl);
    $access_token = json_decode($curl_response);

    return $access_token->access_token;
}

function stkPush($amount)
{
    $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
    $curl_post_data = [
      'BusinessShortCode' => 174379,
        'Password' => lipaNamMpesaPass(),
        'Timestamp' => Carbon::rawParse('now')->format('YmdHms'),
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => $amount,
        'PartyA' => "254790765441",
        'PartyB' => 174379,
        'PhoneNumber' => "254790765441",
        'CallBackUrl' => 'https://60a8b840129d.ngrok.io/callback',
        'AccountReference' => "Payment Trial",
        'TransactionDesc' => "lipa na M-PESA"
    ];

    $data_string = json_encode($curl_post_data);

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer '.newAccessToken()));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURL_POSTFIELDS, $data_string);
    $curl_response = curl_exec($curl);
    print_r($curl_response);
}
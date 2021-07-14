<?php

namespace App\Http\Controllers;

use App\Order;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function token(Request $request)
    {
        $token = $this->_bkash_Get_Token();
        
        $request->session()->put('token', $token['id_token']);

        echo $token['id_token'];
    }

    protected function _bkash_Get_Token()
    {
        $array = $this->_get_config_file();

        $data = array(
            'app_key' => $array["app_key"],
            'app_secret' => $array["app_secret"]
        );

        $url = curl_init($array["tokenURL"]);
        $posttoken = json_encode($data);
        $header = array(
            'Content-Type:application/json',
            'password:' . $array["password"],
            'username:' . $array["username"]
        );

        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $posttoken);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        $resultdata = curl_exec($url);
        curl_close($url);

        return json_decode($resultdata, true);
    }

    protected function _get_config_file()
    {
        $path = storage_path() . "/app/public/config.json";
        return json_decode(file_get_contents($path), true);
    }

    public function createpayment(Request $request)
    {

        $array = $this->_get_config_file();

        $amount = $request->amount;
        $invoice = $request->invoice; // must be unique
        $intent = "sale";
        $createpaybody = array('amount' => $amount, 'currency' => 'BDT', 'merchantInvoiceNumber' => $invoice, 'intent' => $intent);
        $url = curl_init($array["createURL"]);

        $createpaybodyx = json_encode($createpaybody);

        $header = array(
            'Content-Type:application/json',
            'authorization:' . $request->session()->get('token'),
            'x-app-key:' . $array["app_key"]
        );

        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $createpaybodyx);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);

        $response = curl_exec($url);
        curl_close($url);
        echo $response;
    }

    public function executepayment(Request $request)
    {
        $array = $this->_get_config_file();

        $paymentID = $request->paymentID;

        $url = curl_init($array["executeURL"] . $paymentID);

        $header = array(
            'Content-Type:application/json',
            'authorization:' . $request->session()->get('token'),
            'x-app-key:' . $array["app_key"]
        );

        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);

        $response = curl_exec($url);
        curl_close($url);

        $this->_updateOrderStatus($response);

        echo $response;
    }

    protected function _updateOrderStatus($response)
    {
        $response = json_decode($response);

        if ($response && $response->paymentID != null && $response->transactionStatus == 'Completed') {
            Order::firstWhere([
                'invoice' => $response->merchantInvoiceNumber
            ])->update([
                'status' => 'Processing', 'trxID' => $response->trxID
            ]);
        }
    }
}

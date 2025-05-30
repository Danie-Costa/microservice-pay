<?php


namespace App\Services;


use Illuminate\Support\Facades\Log;
class MercadoPagoService
{
    protected $accessToken;

    public function __construct()
    {
        $this->accessToken = getenv('MERCADOPAGO_ACCESS_TOKEN'); // ou use env('...') se estiver dentro de Laravel
    }

    public function newPayment($title, $quantity, $unit_price, $internalReference, $vendor = null) 
    {
       
        $url = "https://api.mercadopago.com/checkout/preferences";
        // $back_urls = [
        //     "success" => route('payment.success', ['order' => $internalReference]),
        //     "failure" => route('payment.failure', ['order' => $internalReference]),
        //     "pending" => route('payment.pending', ['order' => $internalReference])
        // ];
        $back_urls = [
            "success" => 'https://google.com',
            "failure" => "https://google.com",
            "pending" => "https://google.com"
        ];

        $data = [
            "items" => [
                [
                    "title" => $title,
                    "quantity" => (int) $quantity,
                    "unit_price" => (int)  $unit_price,
                    "currency_id" => "BRL"
                ]
            ],
            "payment_methods" => [
                "excluded_payment_types" => [
                    ["id" => "credit_card"],
                    ["id" => "ticket"],
                    ["id" => "debit_card"]
                ]
            ],
            "back_urls" => $back_urls ,
            "auto_return" => "approved",
            "external_reference" => $internalReference,
            
        ];
            // Por padrão usa o token do marketplace
        $accessToken = $this->accessToken;
        if ($vendor) {
            $valorTotal = (float) $data['items'][0]['unit_price'];

            $applicationFee = round($valorTotal * 0.5, 2); // 3% de comissão
            $valorVendedor = $valorTotal - $applicationFee;
            $data['items'][0]['unit_price'] = $valorTotal;
            $data['collector_id'] = (int) $vendor->mp_user_id;
            $data['marketplace_fee'] = $applicationFee;
            $accessToken = $vendor->mp_access_token;
        }


        $payload = json_encode($data);

Log::info('Informa������o registrada no log.'.json_encode( $payload));
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . $accessToken
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            return [
                'status' => 500,
                'error' => curl_error($ch)
            ];
        }

        curl_close($ch);

        if ($httpCode !== 201) {
            return [
                'status' => $httpCode,
                'response' => json_decode($response, true)
            ];
        }

        $result = json_decode($response, true);

        return $result['id'] ?? null;
    }



    public function getPaymentFromMercadoPago($paymentId)
    {
        $url = "https://api.mercadopago.com/v1/payments/{$paymentId}";

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $this->accessToken",
                "Content-Type: application/json",
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            return json_decode($response);
        }
        return null;
    }
}

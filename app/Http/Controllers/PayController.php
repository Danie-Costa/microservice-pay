<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Vendor;
use App\Models\Payment;
use App\Http\Controllers\Controller;
use App\Services\MercadoPagoService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayController extends Controller
{
    public function listByVendor(Request $request, $vendorId)
    {
        $project = $request->project;
        $vendor = $project->vendor()->find($vendorId);
        if (!$vendor) {
            return response()->json(['error' => 'Vendor not found'], 404);
        }
        $payments = $vendor->payment()->paginate(20);

        $payments->getCollection()->transform(function ($payment) {
            return $payment->makeHidden([ 'vendor_id', 'external_reference', 'email', 'name', 
            'phone', 'cpf', 'title', 'quantity', 'price', 'fee', 'price_fee', 'status', 
            'success_url', 'failure_url', 'pending_url',
            ]);
        });
        return response()->json($payments);
    }
    
    public function store(Request $request, $vendorId)
    {
        $project = $request->project;
        $vendor = $project->vendor()->find($vendorId);
        if (!$vendor) {
            return response()->json(['error' => 'Vendor not found'], 404);
        }
        
        $request->validate([
            'external_reference' => 'required|string|max:255',
            'email' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'cpf' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'quantity' => 'required|integer',
            'price' => 'required',
            'success_url' => 'required',
            'failure_url' => 'required',
            'pending_url' => 'required',
        ]);
        
        if (!$this->validateCpf($request->cpf)) {
            return response()->json(['error' => 'Invalid CPF'], 400);
        }

        $urls = [$request->success_url, $request->failure_url, $request->pending_url];
        if (!$this->validateUrls($urls)) {
            return response()->json(['error' => 'Invalid URLs'], 400);
        }

        $request['project_id'] = $project->id;
        $request['fee'] = $vendor->fee;     
        $request['price_fee'] = $request->price * ($vendor->fee / 100);     
        $request['internal_reference'] = uniqid();

 
        $mpService = new MercadoPagoService();
        $request['preference_id'] = $mpService->newPayment($request->title, $request->quantity, $request->price, $request->internal_reference,$vendor); 
        Log::info('Informação registrada no log.'.json_encode($request['preference_id']));
        $vendorPayment = $vendor->payment()->create($request->except(['status','vendor_id','mp_user_id','mp_access_token',
        'mp_refresh_token','mp_public_key','mp_expires_in','mp_token_created_at']));
        $vendorPayment->fee = $vendorPayment->fee ; // Convert fee to percentage and normalize to two decimal places
        $vendorPayment = $vendorPayment->makeHidden([
            'vendor_id', 'project_id','id'
            ]);
        return response()->json($vendorPayment, 201);
    }

    public function show(Request $request, $vendorId, $id)
    {
        $project = $request->project;
        $vendor = $project->payment()->find($id);

        if ($vendor) {
            $vendor = $vendor->makeHidden([
                'mp_user_id',
                'mp_access_token',
                'mp_refresh_token',
                'mp_public_key',
                'mp_expires_in',
                'mp_token_created_at'
            ]);
        }

        return response()->json($vendor);
    }

    public function success(Request $request, $internalReference)
    {
        $payment = Payment::where('internal_reference', $internalReference)->first();
        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }
        return redirect($payment->success_url);
    }
    public function failure(Request $request, $internalReference)
    {
        $payment = Payment::where('internal_reference', $internalReference)->first();
        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }
        return redirect($payment->failure_url);
    }
    public function pending(Request $request, $internalReference)
    {
        $payment = Payment::where('internal_reference', $internalReference)->first();
        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }
        return redirect($payment->pending_url);
    }
    public function notification(Request $request)
    {
        Log::info('Informação registrada no log.');
        $paymentId = $request->input('data.id');
        if (!$paymentId) {
            return response()->json(['message' => 'Notificação sem ID de pagamento'], 400);
        }
        $mpService = new MercadoPagoService();
        $paymentData = $mpService->getPaymentFromMercadoPago($paymentId);
        if (!$paymentData) {
            return response()->json(['message' => 'Falha ao buscar dados do pagamento'], 500);
        }

        $payment = Payment::where('internal_reference', $paymentData->external_reference)->first();
        if (!$payment) {
            return response()->json(['message' => 'Pagamento não encontrado'], 404);
        }
Log::info('Informação registrada no log.'.$paymentData->status);
     
        $payment->update([
            'payment_id' => $paymentId,
            'status' => $paymentData->status,
        ]);
        $notificationService = new NotificationService($payment->projectToken());
        $notificationService->sendWebhookNotification($payment, [
            'payment_id' => $payment->payment_id,
            'status' => $payment->status,
            'reference' => $payment->external_reference,
        ]);
        
        return response()->json(['message' => 'Pagamento atualizado com sucesso'], 200);
    }
    
    private function validateCpf($cpf)
    {
        $cpf = preg_replace('/\D/', '', $cpf);
        if (strlen($cpf) != 11) {
            return false;
        }
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        return true;
    }

    private function validateUrls(array $urls)
    {
        foreach ($urls as $url) {
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                return false;
            }
        }
        return true;
    }
}
// Redirecionamento customizado (comentado se estiver em background)
// return redirect($payment->webhookUrl());

// Caso queira enviar notificação, descomente e implemente NotificationService
/*
$notificationService = new NotificationService($payment->projectToken());
$data = [
    "channel" => "payment",
    "event" => $payment->external_reference,
    "data" => ['url' => route('payment.notification', ['internalReference' => $payment->internal_reference])],
    "destinatario" => "usuário",
    "conteudo" => "Notificação de pagamento",
];

if ($notificationService->notify($data)) {
    return response()->json(['message' => 'Notificação enviada com sucesso'], 200);
}
*/
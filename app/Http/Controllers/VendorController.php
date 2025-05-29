<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Vendor;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
class VendorController extends Controller
{
    public function index(Request $request)
    {
        $project = $request->project;
        $vendors = $project->Vendor()->paginate(20);

        $vendors->getCollection()->transform(function ($vendor) {
            return $vendor->makeHidden([
                'mp_user_id',
                'mp_access_token',
                'mp_refresh_token',
                'mp_public_key',
                'mp_expires_in',
                'mp_token_created_at'
            ]);
        });

        return response()->json($vendors);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:vendors,email',
        ]);
        if($request->has('fee') && ($request->fee < 3) && ($request->fee < 97)){
            return response()->json(['error' => 'A taxa deve ser maior que 3% e menor que 97%'], 422);
        }
        $project = $request->project;
        $vendor = $project->Vendor()->create($request->except(['project_id','mp_user_id',
        'mp_access_token','mp_refresh_token','mp_public_key','mp_expires_in','mp_token_created_at']));
        return response()->json($vendor, 201);
    }

    public function show(Request $request, $id)
    {
        $project = $request->project;
        $vendor = $project->Vendor()->find($id);

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

    public function update(Request $request, $id)
    {
        $project = $request->project;
        $vendor = $project->Vendor()->find($id);
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:vendors,email,' . $vendor->id,
        ]);
        $vendor->update($request->except(['project_id','mp_user_id',
        'mp_access_token','mp_refresh_token','mp_public_key','mp_expires_in','mp_token_created_at']));

        return response()->json($vendor);
    }

    public function destroy(Request $request, $id)
    {
        $project = $request->project;
        $vendor = $project->Vendor()->find($id);
        $vendor->delete();

        return response()->json(null, 204);
    }
    public function authUrl(Request $request)
    {
       
        $clientId = config('services.mercadopago.client_id');
        $redirectUri = route('vendor.oauth.callback'); // Gera URL completa do callback

        $authUrl = "https://auth.mercadopago.com.br/authorization?" . http_build_query([
            'client_id'     => $clientId,
            'response_type' => 'code',
            'platform_id'   => 'mp',
            'redirect_uri'  => $redirectUri,
        ]);

        return redirect()->away($authUrl);
    }

    public function oauthCallback(Request $request)
    {
        if (!$request->has('code')) {
            return response()->json(['error' => 'Código de autorização não recebido.'], 400);
        }

        $code = $request->get('code');

        $response = Http::asForm()->post('https://api.mercadopago.com/oauth/token', [
            'grant_type'    => 'authorization_code',
            'client_id'     => config('services.mercadopago.client_id'),
            'client_secret' => config('services.mercadopago.client_secret'),
            'code'          => $code,
            'redirect_uri'  => route('vendor.oauth.callback'),
        ]);
   
        if ($response->failed()) {
            return response()->json(['error' => 'Falha ao trocar o code pelo token.'], 500);
        }

        
        $data = $response->json();
 

        $vendor = Vendor::find($vendorId);
        $vendor->mp_user_id          = $data['user_id'];
        $vendor->mp_access_token     = $data['access_token'];
        $vendor->mp_refresh_token    = $data['refresh_token'];
        $vendor->mp_expires_in       = $data['expires_in'];
        $vendor->mp_public_key       = $data['public_key'];
        $vendor->mp_token_created_at = Carbon::now();

        $vendor->save();

        return response()->json(['success' => true, 'vendor' => $vendor]);
    }
}

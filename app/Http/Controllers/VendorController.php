<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Vendor;

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
}

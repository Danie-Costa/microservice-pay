<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;

class ProjectAssetsController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'project_token' => 'required|string',
            'external_reference' => 'required|string',
        ]);

        $project = Project::where('token', $request->input('project_token'))->first();

        if (!$project) {
            return response()->json(['error' => 'Projeto não encontrado'], 404);
        }

        $externalReference = $request->input('external_reference');

        return response()->json([
            $externalReference   
        ]);
    }
}

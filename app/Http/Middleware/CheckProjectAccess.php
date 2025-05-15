<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckProjectAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken() ?? $request->input('token') ?? $request->query('token');
        if (!$token) {
            return response()->json(['error' => 'Token not provided'], 401);
        }
        $project = Project::where('token', $token)->first();

        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        $request->merge(['project' => $project]);

        return $next($request);
    }
} 

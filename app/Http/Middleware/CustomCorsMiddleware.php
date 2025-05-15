<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CustomCorsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Definir os cabeçalhos CORS
        $response = $next($request);

        // Aqui você pode ajustar conforme as suas necessidades
        $response->headers->set('Access-Control-Allow-Origin', '*'); // Ou coloque seu domínio específico
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With, Authorization');
        
        return $response;
    }
}


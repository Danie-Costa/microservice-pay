<?php 
namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * Os URIs que devem ser ignorados pela verificação CSRF.
     *
     * @var array
     */
    protected $except = [
        'webhook/notification',

    ];
}

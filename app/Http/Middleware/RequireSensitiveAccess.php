<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class RequireSensitiveAccess
{
    /**
     * Handle an incoming request.
     * Requiere re-autenticación con contraseña compartida para acceder a módulos sensibles.
     * Caché de 5 minutos: si ya se autenticó recientemente, no vuelve a pedir.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $cacheKey = 'sensitive_access_' . $request->user()->id;

        // Verificar si hay una verificación reciente en sesión (5 minutos)
        if (session()->has($cacheKey)) {
            $verifiedAt = session($cacheKey);

            // Si fue verificado hace menos de 5 minutos, permitir acceso
            if (now()->diffInMinutes($verifiedAt) < 5) {
                return $next($request);
            }
        }

        // Mostrar modal de verificación
        return response()->view('auth.verify-sensitive', [
            'intended_url' => $request->url(),
        ]);
    }
}

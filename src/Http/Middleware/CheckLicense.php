<?php

namespace LicenseGuard\Client\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use LicenseGuard\Client\LicenseChecker;
use Symfony\Component\HttpFoundation\Response;

class CheckLicense
{
    public function __construct(private LicenseChecker $checker)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('license.enabled', true)) {
            return $next($request);
        }

        if ($this->isExcluded($request)) {
            return $next($request);
        }

        $result = $this->checker->check();

        if ($result['allowed'] ?? false) {
            return $next($request);
        }

        // Bloqué : page de blocage (mêmes variables que la vue d'origine).
        return response()->view('license::blocked', [
            'message'    => $result['message'] ?: 'Votre abonnement est expiré.',
            'renewalUrl' => $result['renewal_url'] ?? null,
            'status'     => $result['status'] ?? 'expired',
        ], (int) config('license.block_status', 503));
    }

    private function isExcluded(Request $request): bool
    {
        foreach ((array) config('license.except', []) as $pattern) {
            if ($request->is($pattern) || $request->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    }
}

<?php

namespace LicenseGuard\Client;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LicenseChecker
{
    private const CACHE_KEY = 'license_check';

    /**
     * Vérifie l'état de la licence. Renvoie un tableau normalisé :
     * ['allowed' => bool, 'status' => string, 'message' => string, 'renewal_url' => ?string].
     *
     * Comme le middleware d'origine : on ne met en cache QUE les résultats
     * autorisés ; un état bloqué est re-vérifié au prochain chargement.
     */
    public function check(): array
    {
        if ($cached = Cache::get(self::CACHE_KEY)) {
            return $cached;
        }

        // Pas configuré : politique fail-open/closed sans appel réseau.
        if (! config('license.api_url') || ! config('license.key')) {
            return $this->fallback('unconfigured', 'Licence non configurée.');
        }

        try {
            $payload  = $this->buildPayload();
            $response = Http::timeout((int) config('license.timeout', 5))
                ->acceptJson()
                ->post(config('license.api_url'), $payload);

            $data   = $response->successful() || in_array($response->status(), [401, 403], true)
                ? (array) $response->json()
                : throw new \RuntimeException('Réponse inattendue : ' . $response->status());

            $result = [
                'allowed'     => (bool) ($data['allowed'] ?? false),
                'status'      => $data['status'] ?? 'unknown',
                'message'     => $data['message'] ?? '',
                'renewal_url' => $data['renewal_url'] ?? null,
            ];

            // On ne mémorise que l'état AUTORISÉ.
            if ($result['allowed']) {
                Cache::put(self::CACHE_KEY, $result, now()->addHours((int) config('license.cache_hours', 6)));
            }

            return $result;
        } catch (\Throwable $e) {
            Log::warning('License: serveur injoignable', ['error' => $e->getMessage()]);

            return $this->fallback('unreachable', 'Service de vérification injoignable.');
        }
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function fallback(string $status, string $message): array
    {
        return [
            'allowed'     => (bool) config('license.fail_open', true),
            'status'      => $status,
            'message'     => $message,
            'renewal_url' => null,
        ];
    }

    private function buildPayload(): array
    {
        $payload = [
            'license_key'     => config('license.key'),
            'domain'          => request()->getHost(),
            'app_url'         => config('app.url'),
            'laravel_version' => app()->version(),
            'php_version'     => PHP_VERSION,
            'timestamp'       => now()->toIso8601String(),
        ];

        $payload['signature'] = $this->sign($payload);

        return $payload;
    }

    /** HMAC-SHA256 sur la chaîne canonique (ordre identique côté serveur). */
    private function sign(array $data): string
    {
        $canonical = implode('|', [
            $data['license_key'],
            $data['domain'],
            $data['app_url'],
            $data['laravel_version'],
            $data['php_version'],
            $data['timestamp'],
        ]);

        return hash_hmac('sha256', $canonical, (string) config('license.secret'));
    }
}

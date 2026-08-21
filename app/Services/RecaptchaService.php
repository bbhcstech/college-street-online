<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Verifies Google reCAPTCHA v3 tokens server-side.
 *
 * reCAPTCHA v3 runs invisibly (no puzzle for the visitor) and returns a
 * score from 0.0 (bot) to 1.0 (human). We reject anything below the
 * configured threshold. This is combined in ContactController with a
 * honeypot field and IP-based rate limiting, so the contact form has three
 * independent layers of bot protection rather than relying on Google alone.
 */
class RecaptchaService
{
    public function verify(?string $token, string $remoteIp): array
    {
        $secret = config('services.recaptcha.secret_key');

        // If no keys are configured yet (fresh install), don't hard-fail —
        // log a warning and allow through so the form isn't broken during
        // setup. Once RECAPTCHA_SECRET_KEY is set in .env this activates.
        if (empty($secret)) {
            Log::warning('reCAPTCHA secret key not configured; skipping verification.');

            return ['success' => true, 'score' => null];
        }

        if (empty($token)) {
            return ['success' => false, 'score' => 0];
        }

        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secret,
                'response' => $token,
                'remoteip' => $remoteIp,
            ]);

            $data = $response->json();

            $success = (bool) ($data['success'] ?? false);
            $score = (float) ($data['score'] ?? 0);
            $threshold = (float) config('services.recaptcha.score_threshold', 0.5);

            return [
                'success' => $success && $score >= $threshold,
                'score' => $score,
            ];
        } catch (\Throwable $e) {
            Log::error('reCAPTCHA verification failed: '.$e->getMessage());

            // Fail closed on network errors to be safe against bots, but
            // fail open would also be defensible — adjust to your risk
            // tolerance.
            return ['success' => false, 'score' => 0];
        }
    }
}

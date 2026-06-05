<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class EnsureTwoFactorVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->two_factor_enabled) {
            
            $isVerified = false;

            // 1. Check Session (for Web/Inertia)
            if (Session::get('two_factor_verified', false)) {
                $isVerified = true;
            }

            // 2. Check UserLoginSession (persistent per-device verification)
            if (!$isVerified) {
                $tokenId = $this->resolveTokenId($request);
                if ($tokenId) {
                    $session = \App\Models\UserLoginSession::where('access_token_id', $tokenId)
                        ->where('user_id', $user->id)
                        ->whereNotNull('two_factor_verified_at')
                        ->first();
                    
                    if ($session) {
                        $isVerified = true;
                        // Sync back to session for web performance
                        Session::put('two_factor_verified', true);
                    }
                }
            }

            if (!$isVerified) {
                // If it's an API request, return a specific error or challenge
                if ($request->expectsJson()) {
                    return response()->json([
                        'two_factor_required' => true,
                        'message' => __('Two-factor authentication required.'),
                    ], 403);
                }

                // If it's a web request, redirect to the challenge page
                if (!$request->is('auth/two-factor*')) {
                    return redirect()->route('two-factor.challenge');
                }
            }
        }

        return $next($request);
    }

    /**
     * Resolve the token ID from the request (similar logic to SecuritySettingsController)
     */
    private function resolveTokenId(Request $request): ?string
    {
        $user = $request->user();
        if (!$user) return null;

        // API Token (Passport)
        $token = $user->token();
        if ($token instanceof \Illuminate\Database\Eloquent\Model) {
            return (string) $token->getKey();
        }

        // Web Cookie Session (Transient Token)
        $jwtCookie = $request->cookie(\Laravel\Passport\Passport::cookie());
        if ($jwtCookie) {
            try {
                $encrypter = app(\Illuminate\Contracts\Encryption\Encrypter::class);
                $raw = \Laravel\Passport\Passport::$decryptsCookies
                    ? \Illuminate\Cookie\CookieValuePrefix::remove($encrypter->decrypt($jwtCookie, \Laravel\Passport\Passport::$unserializesCookies))
                    : $jwtCookie;

                $decoded = (array) \Firebase\JWT\JWT::decode(
                    $raw,
                    new \Firebase\JWT\Key(\Laravel\Passport\Passport::tokenEncryptionKey($encrypter), 'HS256')
                );

                if (isset($decoded['csrf'])) {
                    return 'cookie:'.hash('sha256', (string)$decoded['csrf']);
                }
            } catch (\Throwable $e) {
                return null;
            }
        }

        return null;
    }
}
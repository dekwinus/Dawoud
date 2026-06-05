<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\TwoFactorOtpService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class TwoFactorWebController extends Controller
{
    protected $otpService;

    public function __construct(TwoFactorOtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Show the 2FA challenge page.
     */
    public function challenge(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->two_factor_enabled) {
            return redirect()->intended('/admin/dashboard');
        }

        if (Session::get('two_factor_verified', false)) {
            return redirect()->intended('/admin/dashboard');
        }

        // If no code exists or it's expired, generate a new one
        if (!$user->two_factor_code || $user->two_factor_expires_at->isPast()) {
            $otp = $this->otpService->generateOtp($user);
            $this->otpService->sendOtp($user, $otp);
        }

        return Inertia::render('Auth/TwoFactorChallenge', [
            'email' => $user->email,
        ]);
    }

    /**
     * Verify the 2FA code.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();

        if ($this->otpService->verifyOtp($user, $request->code)) {
            Session::put('two_factor_verified', true);

            // Persist in UserLoginSession for device memory
            $tokenId = $this->resolveTokenId($request);
            if ($tokenId) {
                \App\Models\UserLoginSession::updateOrCreate(
                    ['access_token_id' => $tokenId, 'user_id' => $user->id],
                    ['two_factor_verified_at' => now()]
                );
            }

            return redirect()->intended('/admin/dashboard');
        }

        return back()->withErrors(['code' => __('The provided code is invalid or has expired.')]);
    }

    /**
     * Resolve the token ID from the request
     */
    private function resolveTokenId(Request $request): ?string
    {
        $user = Auth::user();
        if (!$user) return null;

        $token = $user->token();
        if ($token instanceof \Illuminate\Database\Eloquent\Model) {
            return (string) $token->getKey();
        }

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

    /**
     * Resend the 2FA code.
     */
    public function resend(Request $request)
    {
        $user = Auth::user();

        $otp = $this->otpService->generateOtp($user);
        $this->otpService->sendOtp($user, $otp);

        return back()->with(['message' => __('A new verification code has been sent to your email.')]);
    }
}
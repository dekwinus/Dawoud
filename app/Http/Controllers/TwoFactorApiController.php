<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TwoFactorOtpService;
use App\Models\UserLoginSession;
use Illuminate\Support\Facades\Auth;

class TwoFactorApiController extends Controller
{
    protected $otpService;

    public function __construct(TwoFactorOtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Get the 2FA status for the current user.
     */
    public function status(Request $request)
    {
        return response()->json([
            'enabled' => (bool) $request->user()->two_factor_enabled,
        ]);
    }

    /**
     * Enable 2FA for the current user.
     */
    public function enable(Request $request)
    {
        $user = $request->user();
        $user->update(['two_factor_enabled' => true]);

        return response()->json([
            'success' => true,
            'message' => __('Two-factor authentication enabled successfully.'),
        ]);
    }

    /**
     * Disable 2FA for the current user.
     */
    public function disable(Request $request)
    {
        $user = $request->user();
        $user->update(['two_factor_enabled' => false]);

        return response()->json([
            'success' => true,
            'message' => __('Two-factor authentication disabled successfully.'),
        ]);
    }

    /**
     * Verify the 2FA code for an API session.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();

        if ($this->otpService->verifyOtp($user, $request->code)) {
            // Persist in UserLoginSession
            $tokenId = $this->resolveTokenId($request);
            if ($tokenId) {
                UserLoginSession::updateOrCreate(
                    ['access_token_id' => $tokenId, 'user_id' => $user->id],
                    ['two_factor_verified_at' => now()]
                );
            }

            return response()->json([
                'success' => true,
                'message' => __('Two-factor authentication verified successfully.'),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('The provided code is invalid or has expired.'),
        ], 422);
    }

    /**
     * Resend verification code.
     */
    public function resend(Request $request)
    {
        $user = $request->user();
        $otp = $this->otpService->generateOtp($user);
        $this->otpService->sendOtp($user, $otp);

        return response()->json([
            'success' => true,
            'message' => __('A new verification code has been sent to your email.'),
        ]);
    }

    /**
     * Resolve the token ID from the request
     */
    private function resolveTokenId(Request $request): ?string
    {
        $user = $request->user();
        if (!$user) return null;

        $token = $user->token();
        if ($token instanceof \Illuminate\Database\Eloquent\Model) {
            return (string) $token->getKey();
        }

        return null;
    }
}
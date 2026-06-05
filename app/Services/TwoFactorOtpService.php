<?php

namespace App\Services;

use App\Models\User;
use App\Mail\TwoFactorOtpMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class TwoFactorOtpService
{
    /**
     * Generate a new OTP for the user.
     *
     * @param User $user
     * @return string
     */
    public function generateOtp(User $user)
    {
        $otp = rand(100000, 999999);
        
        $user->update([
            'two_factor_code' => $otp,
            'two_factor_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        return (string) $otp;
    }

    /**
     * Send the OTP via email.
     *
     * @param User $user
     * @param string $otp
     * @return void
     */
    public function sendOtp(User $user, string $otp)
    {
        Mail::to($user->email)->send(new TwoFactorOtpMail($otp));
    }

    /**
     * Verify the OTP for the user.
     *
     * @param User $user
     * @param string $otp
     * @return bool
     */
    public function verifyOtp(User $user, string $otp)
    {
        if ($user->two_factor_code === $otp && 
            $user->two_factor_expires_at && 
            $user->two_factor_expires_at->isFuture()) {
            
            $user->update([
                'two_factor_code' => null,
                'two_factor_expires_at' => null,
            ]);

            return true;
        }

        return false;
    }
}
<?php

namespace App\Http\Controllers\Api\V1\SendEmail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Mail\OtpMail;

class OTPController extends Controller
{
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Generate a 6-digit OTP
        $otp = $this->generateOtp();

        // Store OTP in cache with a 10-minute expiration
        $this->storeOtp($request->email, $otp);

        // Send OTP to user's email
        Mail::to($request->email)->send(new OtpMail($otp));

        return response()->json(['message' => 'OTP sent to your email.']);
    }

    private function generateOtp()
    {
        return rand(100000, 999999);
    }

    private function storeOtp($email, $otp)
    {
        Cache::put('otp_' . $email, $otp, now()->addMinutes(10)); // OTP valid for 10 minutes
    }
}

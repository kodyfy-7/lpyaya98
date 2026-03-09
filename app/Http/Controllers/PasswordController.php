<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResendVerificationOtpRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\VerifyEmailRequest;
use App\Mail\ForgotPasswordMail;
use App\Mail\ResendVerificationOtpMail;
use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PasswordController extends Controller
{
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        try {
            $user = User::whereRaw('LOWER("email") = ?', [strtolower($request->email)])
                ->select('id', 'name', 'email')
                ->first();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email address does not exist.',
                ], 400);
            }

            $otp = $this->generateOtp();

            EmailVerification::create([
                'userId' => $user->id,
                'otp' => $otp,
                'otpExpiresAt' => Carbon::now()->addMinutes(10),
            ]);

            $verificationLink = "{$request->input('baseUrl')}?otp={$otp}&email={$user->email}";
            $userName = $user->name;
            $userEmail = $user->email;

            app()->terminating(function () use ($userName, $userEmail, $verificationLink) {
                try {
                    Mail::to($userEmail)->send(new ForgotPasswordMail(
                        name: $userName,
                        email: $userEmail,
                        resetUrl: $verificationLink,
                    ));
                } catch (\Throwable $e) {
                    Log::error('Forgot password mail failed: '.$e->getMessage());
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'You have initiated a reset password, check your mailbox for verification link.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'System optimization in progress, please wait',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $user = User::whereRaw('LOWER("email") = ?', [strtolower($request->email)])->first();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email address does not exist.',
                ], 400);
            }

            $emailVerification = EmailVerification::where('userId', $user->id)
                ->where('otp', $request->otp)
                ->whereNull('expiredAt')
                ->first();

            if (! $emailVerification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired OTP.',
                ], 400);
            }

            if (Carbon::now()->isAfter($emailVerification->otpExpiresAt)) {
                return response()->json([
                    'success' => false,
                    'message' => 'OTP has expired.',
                ], 400);
            }

            $emailVerification->update(['expiredAt' => Carbon::now()]);

            $user->update([
                'password' => Hash::make($request->password),
                'emailVerifiedAt' => Carbon::now(),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $user->createToken('auth_token')->plainTextToken,
                    'user' => [
                        'userId' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'emailVerifiedAt' => $user->emailVerifiedAt,
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'System optimization in progress, please wait',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function resendVerificationOtp(ResendVerificationOtpRequest $request)
    {
        try {
            $user = User::whereRaw('LOWER("email") = ?', [strtolower($request->email)])
                ->select('id', 'email', 'emailVerifiedAt', 'name')
                ->first();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email address does not exist.',
                ], 400);
            }

            if ($user->emailVerifiedAt) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email address is already verified.',
                ], 400);
            }

            $otp = $this->generateOtp();

            EmailVerification::create([
                'userId' => $user->id,
                'otp' => $otp,
                'otpExpiresAt' => Carbon::now()->addMinutes(10),
            ]);

            $verificationLink = "{$request->input('baseUrl')}?email={$request->input('email')}&otp={$otp}";
            $userEmail = $user->email;

            app()->terminating(function () use ($userEmail, $verificationLink) {
                try {
                    Mail::to($userEmail)->send(new ResendVerificationOtpMail(
                        email: $userEmail,
                        verificationLink: $verificationLink,
                    ));
                } catch (\Throwable $e) {
                    Log::error('Resend verification OTP mail failed: '.$e->getMessage());
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Verification email sent successfully.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'System optimization in progress, please wait',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function verifyEmail(VerifyEmailRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User with this email does not exist.',
                ], 404);
            }

            $user->update([
                'emailVerifiedAt' => now(),
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email address confirmed successfully',
                'userId' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'token' => $user->createToken('auth_token')->plainTextToken,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        try {
            $user = User::find($request->userId);

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.',
                ], 404);
            }

            $user->update([
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password saved successfully.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function generateOtp(): int
    {
        return random_int(100000, 999999);
    }
}

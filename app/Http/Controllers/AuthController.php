<?php

namespace App\Http\Controllers;

use App\Mail\AdminRegistrationNotificationMail;
use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    /**
     * Login user
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $email = strtolower($request->email);

        $user = User::with([
            'role',
            'member.area',
            'member.zone',
            'member.parish',
            'member.province',
        ])->whereRaw('LOWER(email) = ?', [$email])->first();

        // ...

        if (! $user->isSuperAdmin && ! $user->emailVerifiedAt) {
            if (! $user->member) {
                return response()->json([
                    'userId' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'emailVerifiedAt' => $user->emailVerifiedAt,
                    'role' => $user->role,
                    'isSuperAdmin' => $user->isSuperAdmin,
                    'membership' => $user->member,
                    'token' => $user->createToken('auth_token')->plainTextToken,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Your account is still under review. Please contact Special Duties Department for activation.',
            ], 400);
        }

        if ($user->deactivatedAt) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is still under review. Please contact Special Duties Department for activation.',
            ], 400);
        }

        return response()->json([
            'userId' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'emailVerifiedAt' => $user->emailVerifiedAt,
            'role' => $user->role,
            'isSuperAdmin' => $user->isSuperAdmin,
            'membership' => $user->member,
            'token' => $user->createToken('auth_token')->plainTextToken,
        ]);
    }

    /**
     * Register user
     */
    public function register(Request $request)
    {
        $request->validate([
            'firstName' => 'required|string',
            'lastName' => 'required|string',
            'dob' => 'nullable|date',
            'email' => 'required|email',
            'gender' => 'required|string',
            'password' => 'required|string|min:6',
            'phoneNumber' => 'required|string',
            'education' => 'nullable|string',
            'occupation' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $existingUser = User::with('member')->where('email', $request->email)->first();

            if ($existingUser) {
                if ($existingUser->member) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User with the same email already exists.',
                    ], 400);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Registration successful',
                    'userId' => $existingUser->id,
                    'name' => $existingUser->name,
                    'email' => $existingUser->email,
                    'membership' => null,
                    'token' => $existingUser->createToken('auth_token')->plainTextToken,
                ], 201);
            }

            $user = User::create([
                'name' => "{$request->firstName} {$request->lastName}",
                'dateOfBirth' => $request->dob,
                'email' => $request->email,
                'gender' => $request->gender,
                'password' => Hash::make($request->password),
                'phoneNumber' => $request->phoneNumber,
                'education' => $request->education,
                'occupation' => $request->occupation,
                'address' => $request->address,
                'isAdmin' => true,
                'roleId' => null,
            ]);

            DB::commit();

            $userName = $user->name;
            $userEmail = $user->email;
            $adminEmail = config('mail.admin_email', config('mail.from.address'));

            app()->terminating(function () use ($userName, $userEmail, $adminEmail) {
                // Welcome mail to new user
                try {
                    Mail::to($userEmail)->send(new WelcomeMail(name: $userName));
                } catch (\Throwable $e) {
                    Log::error('Welcome mail failed: '.$e->getMessage());
                }

                // Notify admin of new registration
                try {
                    Mail::to($adminEmail)->send(new AdminRegistrationNotificationMail(
                        user: ['name' => $userName, 'email' => $userEmail]
                    ));
                } catch (\Throwable $e) {
                    Log::error('Admin registration notification mail failed: '.$e->getMessage());
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'userId' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'token' => $user->createToken('auth_token')->plainTextToken,
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate token (JWT or Laravel Sanctum/Passport)
     */
    private function generateToken($userId, $member = null)
    {
        // Example using Laravel Sanctum
        $user = User::find($userId);

        return $user->createToken('auth_token')->plainTextToken;
    }
}

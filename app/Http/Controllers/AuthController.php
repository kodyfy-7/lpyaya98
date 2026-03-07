<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationNotificationMail;
use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email address',
            ], 400);
        }

        if (! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password',
            ], 400);
        }

        if (! $user->is_super_admin && ! $user->email_verified_at) {
            if (! $user->member) {
                $token = $this->generateToken($user->id, $user->member);

                return response()->json([
                    'userId' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'emailVerifiedAt' => $user->email_verified_at,
                    'role' => $user->role,
                    'isSuperAdmin' => $user->is_super_admin,
                    'membership' => $user->member,
                    'token' => $token,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Your account is still under review. Please contact Special Duties Department for activation.',
            ], 400);
        }

        if ($user->deactivated_at) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is still under review. Please contact Special Duties Department for activation.',
            ], 400);
        }

        $token = $this->generateToken($user->id, $user->member);

        return response()->json([
            'userId' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'emailVerifiedAt' => $user->email_verified_at,
            'role' => $user->role,
            'isSuperAdmin' => $user->is_super_admin,
            'membership' => $user->member,
            'token' => $token,
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
            'dob' => 'required|date',
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

                $token = $this->generateToken($existingUser->id);

                return response()->json([
                    'success' => true,
                    'message' => 'Registration successful',
                    'userId' => $existingUser->id,
                    'name' => $existingUser->name,
                    'email' => $existingUser->email,
                    'membership' => null,
                    'token' => $token,
                ], 201);
            }

            $hashedPassword = Hash::make($request->password);

            $user = User::create([
                'name' => $request->firstName.' '.$request->lastName,
                'date_of_birth' => $request->dob,
                'email' => $request->email,
                'gender' => $request->gender,
                'password' => $hashedPassword,
                'phone_number' => $request->phoneNumber,
                'education' => $request->education,
                'occupation' => $request->occupation,
                'address' => $request->address,
                'is_admin' => true,
                'role_id' => null,
                'email_verified_at' => null,
            ]);

            // Send welcome email
            // Mail::to($user->email)->send(new WelcomeMail($user->name));

            // Notify admin
            $adminEmail = env('ADMIN_EMAIL', env('MAIL_USERNAME'));
            // Mail::to($adminEmail)->send(new RegistrationNotificationMail($user));

            DB::commit();

            $token = $this->generateToken($user->id);

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'userId' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'token' => $token,
            ], 201);

        } catch (\Exception $e) {
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

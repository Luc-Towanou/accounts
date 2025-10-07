<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\otpMail;
use App\Models\User;
use App\Notifications\ConfirmationInscription;
use App\Notifications\PasswordResetConfirmation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    public function register(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
    ]);

    DB::beginTransaction();

    try {
        $otp = rand(100000, 999999); // générer OTP

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($otp),
            'otp' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(10), // expire après 10 minutes
        ]);

        // Envoi du mail (peut échouer)
        Mail::to($user->email)->send(new otpMail($otp));

        DB::commit();

        return response()->json([
            'message' => 'Veuillez vérifier votre mail et nous renvoyer le code OTP que vous venez de recevoir.'
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'message' => 'Erreur lors de l\'inscription. Veuillez réessayer.',
            'error' => $e->getMessage()
        ], 500);
    }
}
    // public function register(Request $request)
    // {
    //     $request->validate([
    //     'name' => 'required|string|max:255',
    //     'email' => 'required|email|unique:users,email',
    //     // 'password' => 'required|min:6|confirmed',
        
    //     ]);
    //     // dd($request);
    //     $otp = rand(100000, 999999); // générer  OTP
    //     // dd($otp);
    //     $user = User::create([
    //     'name' => $request->name,
    //     'email' => $request->email,
    //     'password' => Hash::make($otp),
    //     'otp' => $otp,
    //     'otp_expires_at' => Carbon::now()->addMinutes(10) // expirer après 10 minutes
    //     ]);
    //     //  dd($user);
    //     Mail::to($user->email)->send(new otpMail($otp));

    // return response()->json(['message' => 'Veuillez verifier votre mail en nous renvoyant le code otp que vous venez de recevoir.']);
    // }
    //
    // public function register(Request $request)
    // {
    //     $request->validate([
    //     'name' => 'required|string|max:255',
    //     'email' => 'required|email|unique:users,email',
    //     // 'password' => 'required|min:6|confirmed',
        
    //     ]);

    //     $otp = rand(100000, 999999); // générer  OTP
    //     $user = User::create([
    //     'name' => $request->name,
    //     'email' => $request->email,
    //     'password' => Hash::make($request->password),
    //     'otp' => $otp,
    //     'otp_expires_at' => Carbon::now()->addMinutes(10) // expirer après 10 minutes
    //     ]);
    //         Mail::to($user->email)->send(new otpMail($otp));

    // return response()->json(['message' => 'Inscription réussie, vérifiez votre email pour le code OTP.']);
    // }
    /**
     * Verification Mail par Otp
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function verifymailByOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::where('email', $request->email)->where('otp', $request->otp)->first();

        if (!$user) {
            return response()->json(['message' => 'Code OTP invalide.'], 400);
        }

        if (Carbon::now()->gt($user->otp_expires_at)) {
            return response()->json(['message' => 'Code OTP expiré.'], 400);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'email_verified_at' => Carbon::now(),
            'otp' => null,
            'otp_expires_at' => null
        ]);

        $user->notify(new ConfirmationInscription());
                // Connexion + génération du token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Email confirmé avec succès. vous pouvez desormais vous connecter.',
            'user' => $user,
            'token' => $token
            ]);
    }

    /**
     * Summary of login
     * @unauthenticated
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
        {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:6'
            ]);
            $user = User::where('email', $request->email)->first();
            if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
            'email' => ['Les identifiants sont incorrects.'],
            ]);
            }
            $token = $user->createToken('api_token')->plainTextToken;
            
            return response()->json([
            'user' => $user,
            'token' => $token
            ]);
        }

    public function loginByOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ]);

        $user = User::where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Code OTP ou email invalide.'], 401);
        }

        if (!$user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Votre mail n\'a pas encore été vérifié '], 403);
        }

        if (Carbon::now()->gt($user->otp_expires_at)) {
            return response()->json(['message' => 'Code OTP expiré.'], 401);
        }

        // Invalider l’OTP après utilisation
        $user->update([
            'otp' => null,
            'otp_expires_at' => null,
            'email_verified_at' => $user->email_verified_at ?? Carbon::now(),
        ]);

        // Connexion + génération du token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie 🎉',
            'user' => $user,
            'token' => $token
        ]);
    }
    public function logout(Request $request)
    {
    $request->user()->tokens()->delete();
    return response()->json(['message' => 'Déconnexion réussie.'], 200);
    } 

    
    
    /**
     * Renvoie de l'Otp pour Connexion apres inscription
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function resendOtp(Request $request)
{
    $request->validate(['email' => 'required|email']);

    
    $user = User::where('email', $request->email)->first();
    if (!$user) {
        return response()->json(['message' => 'User non trouvé.'], 404);
    }

    
    // Générer un nouvel OTP
    $otp = rand(100000, 999999);
    $user->update([
        'otp' => $otp,
        'otp_expires_at' => Carbon::now()->addMinutes(10),
    ]);
    // dd($request);
    Mail::to($user->email)->send(new OtpMail($otp));
    // dd($request);
    return response()->json(['message' => 'Un nouveau code OTP a été envoyé.']);
    }

       

    /**
     * Envoie de l'Otp pour Mot de passe oublié
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
            public function sendResetOtp(Request $request)
           { 
            try {
                DB::beginTransaction();

                $request->validate(['email' => 'required|email']);

                $user = User::where('email', $request->email)->first();

                if (!$user) {
                    return response()->json(['message' => 'Aucun compte trouvé avec cet email.'], 404);
                }

                $otp = rand(100000, 999999);
                $user->update([
                    'otp' => $otp,
                    'otp_expires_at' => now()->addMinutes(10),
                ]);

                Mail::to($user->email)->send(new OtpMail($otp));

                DB::commit();

                return response()->json(['message' => 'Un code OTP de réinitialisation a été envoyé par email.']);
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Erreur lors de l\'envoi OTP : ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
                return response()->json([
                    'message' => 'Erreur lors de l\'envoi du code OTP.',
                    'error' => $e->getMessage()
                ], 500);
            }
        }
        // {
        //     $request->validate(['email' => 'required|email']);

        //     $user = User::where('email', $request->email)->first();

        //     if (!$user) {
        //         return response()->json(['message' => 'Aucun compte trouvé avec cet email.'], 404);
        //     }

        //     $otp = rand(100000, 999999);
        //     $user->update([
        //         'otp' => $otp,
        //         'otp_expires_at' => now()->addMinutes(10),
        //     ]);

        //     Mail::to($user->email)->send(new OtpMail($otp)); 

        //     return response()->json(['message' => 'Un code OTP de réinitialisation a été envoyé par email.']);
        // }



    /**
     * Mot de passe oublié
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ResetPasswordbyOtp(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'email' => 'required|email',
                'otp' => 'required',
                'password' => 'required|string|min:6|confirmed',
            ]);

            $user = User::where('email', $request->email)
                        ->where('otp', $request->otp)
                        ->first();

            if (!$user) {
                return response()->json(['message' => 'Code OTP ou email invalide.'], 401);
            }

            if (now()->greaterThan($user->otp_expires_at)) {
                return response()->json(['message' => 'Code OTP expiré.'], 400);
            }

            $user->password = Hash::make($request->password);
            $user->otp = null;
            $user->otp_expires_at = null;
            $user->save();

            // Envoi notification
            $user->notify(new PasswordResetConfirmation());

            DB::commit();

            return response()->json(['message' => 'Mot de passe réinitialisé avec succès.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erreur reset password OTP : ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Erreur lors de la réinitialisation du mot de passe.',
                'error' => $e->getMessage()
            ], 500);
        }
}

    public function validateToken(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            return response()->json([
                'valid' => true,
                'message' => 'Token is valid',
                'user' => $user,
            ]);
        }

        return response()->json([
            'valid' => false,
            'message' => 'Invalid or expired token',
        ], 401);
    }
    // {
    //     $request->validate([
    //         'email' => 'required|email',
    //         'otp' => 'required',
    //         'password' => 'required|string|min:6|confirmed',

    //     ]);

    //     // dd($request);

    //     $user = User::where('email', $request->email)
    //                 ->where('otp', $request->otp)
    //                 ->first();

    //     // if (!$user) {
    //     //     return response()->json(['message' => 'Code OTP invalide.'], 400);
    //     // }

                        
    //     if (!$user) {
    //         // dd($user->otp);
    //         return response()->json(['message' => 'Code OTP ou email invalide.'], 401);
    //     }

    //     if (now()->greaterThan($user->otp_expires_at)) {
    //         return response()->json(['message' => 'Code OTP expiré.'], 400);
    //     }
        
    //     $user->password = Hash::make($request->password);
    //     $user->otp = null;
    //     $user->otp_expires_at = null;
    //     $user->save();
        
    //     //Envoi de la notification
    //     $user->notify(new PasswordResetConfirmation());

    //     return response()->json(['message' => 'Mot de passe réinitialisé avec succès.'], );

    // }


}

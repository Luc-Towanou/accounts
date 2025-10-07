<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    //
    public function me(Request $request)
    {
    $user = Auth::user();

    return response()->json([
        // 'id' => $user->id,
        'prenom' => $user->prenom,
        'name' => $user->name,
        'email' => $user->email,
        'avatar' => $user->avatar,
        'role' => $user->role,
        'statut_compte' => $user->statut_objet,
    ]);
    }
    public function update(Request $request)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json(['message' => 'Utilisateur non authentifié'], 401);
    }

    $request->validate([
        'name' => 'nullable|string|max:255',
        'email' => 'nullable|email|unique:utilisateurs,email,' . $user->id,
        'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:6192',
        'password' => 'required|string|min:6', // mot de passe actuel
        // 'new_password' => 'nullable|string|min:6|confirmed', // nouveau mot de passe
    ]);

    DB::beginTransaction();

    try {
        // Vérification du mot de passe actuel
        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Mot de passe actuel incorrect'], 403);
        }

        // Mise à jour de l'avatar si fourni
        if ($request->hasFile('avatar')) {
            $imageUpload = Cloudinary::upload($request->file('avatar')->getRealPath(), [
                'folder' => 'accounts'
            ]);
            $user->avatar = $imageUpload->getPublicId();
        }

        // Mise à jour des champs modifiables
        if ($request->filled('name')) {
            $user->name = $request->name;
        }

        
        if ($request->filled('email')) {
            if ($user->modifiable_at && $user->modifiable_at > Carbon::now()) {
                $tempsrestant = Carbon::now()->diffForHumans($user->modifiable_at, [
                    'syntax' => Carbon::DIFF_RELATIVE_TO_NOW,
                    'short' => true, // optionnel : "in 1h", "in 2d"
                ]);
                
                return response()->json([
                    'message' => ' Vous devez encore attendre ' . $tempsrestant . ' avant de modifier votre email.'
                ], 403);
            }
            $otp = rand(100000, 999999); // générer OTP
            $user->otp = $otp;
            $user->otp_expires_at = Carbon::now()->addMinutes(10);
            $user->email = $request->email;
            $user->email_verified_at = null; //annuler la date de verification du mail
        }


        $user->save();

        DB::commit();
        $message = 'Profil mis à jour avec succès.';

        if ($request->filled('email')) {
            $message .= ' Un code de vérification a été envoyé à votre nouvelle adresse email. Veuillez le valider pour finaliser le changement.';
        }
        return response()->json([
            'message' => $message,
            'user' => $user->fresh()
        ]);
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('Erreur lors de la mise à jour du profil : ' . $e->getMessage(), [
            'exception' => $e,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Une erreur est survenue lors de la mise à jour du profil.',
            'error' => $e->getMessage()
        ], 500);
    }
}

    // public function update(Request $request)
    //  {
    //      $user = Auth::user(); 
    //      if (!$user) {
    //          return response()->json(['message' => 'Utilisateur non authentifié'], 401);
    //      }
 
    //     $request->validate([
    //          'nom' => 'nullable|string|max:255',
    //          'email' => 'nullable|email|unique:utilisateurs,email,' . $user->id,
    //          'password'=>'required|string|min:6|',
    //          'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:6192',
    //      ]);


 
    //       if ($request->hasFile('avatar')) {
    //         // $user->avatar = Cloudinary::upload($request->file('avatar')->getRealPath())->getSecurePath();   

    //         // Upload image principale
    //         $imageUpload = Cloudinary::upload($request->file('avatar')->getRealPath(), [
    //             'folder' => 'accounts'
    //         ]);
    //         $user->avatar = $imageUpload->getPublicId();
    //     }

    //     if ($request->has('nom')) $user->nom = $request->nom;
    //     if ($request->has('email')) $user->email = $request->email;
    //     // if ($request->filled('password')) $user->password = Hash::make($request->password);

    //     $user->save();
 
    //      return response()->json([
    //         'message' => 'Profil mis à jour avec succès.',
    //         'user' => $user->fresh()
    //     ]);
    // }
}

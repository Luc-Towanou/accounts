<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    //
    public function me(Request $request)
    {
    $user = Auth::user();

    return response()->json([
        // 'id' => $user->id,
        'prenom' => $user->prenom,
        'nom' => $user->nom,
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
             'nom' => 'nullable|string|max:255',
             'email' => 'nullable|email|unique:utilisateurs,email,' . $user->id,
             'password'=>'nullable|string|min:6|',
             'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:6192',
         ]);
 
          if ($request->hasFile('avatar')) {
            // $user->avatar = Cloudinary::upload($request->file('avatar')->getRealPath())->getSecurePath();   

            // Upload image principale
            $imageUpload = Cloudinary::upload($request->file('avatar')->getRealPath(), [
                'folder' => 'accounts'
            ]);
            $user->avatar = $imageUpload->getPublicId();
        }

        if ($request->has('nom')) $user->nom = $request->nom;
        if ($request->has('email')) $user->email = $request->email;
        if ($request->filled('password')) $user->password = Hash::make($request->password);

        $user->save();
 
         return response()->json([
            'message' => 'Profil mis à jour avec succès.',
            'user' => $user->fresh()
        ]);
    }
}

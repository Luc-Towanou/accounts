<?php

namespace App\Http\Controllers\Config\Mail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Header;

class MailConfigController extends Controller
{
    //
    /**
     * 
     * 
     * @param \Illuminate\Http\Request $request
     * @get
     * @header
     * @return \Illuminate\Http\JsonResponse
     * @unauthenticate
     */
    #[Get('api/mail-config')]
    // #[Header(name: 'X-Admin-Key', required: true, description: 'Clé d’accès admin définie dans .env')]
   
    public function show(Request $request)
    {
        $request->validate([
            'X_Admin_Key' => 'required|string',
        ]);
        // Vérification de la clé d'accès (optionnel mais recommandé)
        if ($request->X_Admin_Key !== env('ADMIN_KEY')) {
            return response()->json(['error' => 'Accès refusé'], 403);
        } 

        return Response::json([
            'MAIL_MAILER' => env('MAIL_MAILER'),
            'MAIL_SCHEME' => env('MAIL_SCHEME'),
            'MAIL_HOST' => env('MAIL_HOST'),
            'MAIL_PORT' => env('MAIL_PORT'),
            'MAIL_USERNAME' => env('MAIL_USERNAME'),
            'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION'),
            'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS'),
            'MAIL_FROM_NAME' => env('MAIL_FROM_NAME'),
            'MAIL_PASSWORD' => env('MAIL_PASSWORD'), // masqué pour la sécurité
        ]);
    }
}

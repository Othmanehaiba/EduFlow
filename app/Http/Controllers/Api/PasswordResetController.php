<?php

// Controller pour la réinitialisation du mot de passe
// Étape 1 : demander un code par email
// Étape 2 : utiliser le code pour changer le mot de passe

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;       // Pour accéder directement à la BDD
use Illuminate\Support\Facades\Hash;     // Pour chiffrer le mot de passe
use Illuminate\Support\Facades\Mail;     // Pour envoyer des emails
use Illuminate\Support\Str;              // Pour générer des chaînes aléatoires

class PasswordResetController extends Controller
{
    // -------------------------------------------------------
    // POST /forgot-password
    // L'utilisateur entre son email pour recevoir un lien de reset
    // -------------------------------------------------------
    public function forgotPassword(Request $request)
    {
        // Valider que l'email est fourni et existe dans la BDD
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        // Générer un token aléatoire (64 caractères)
        $token = Str::random(64);

        // Supprimer l'ancien token s'il en existe un pour cet email
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Insérer le nouveau token dans la table password_reset_tokens
        DB::table('password_reset_tokens')->insert([
            'email'      => $request->email,
            'token'      => Hash::make($token), // on chiffre le token
            'created_at' => now(),               // date de création
        ]);

        // Envoyer un email simple avec le token
        // En production, utilise une belle vue, ici on fait simple
        Mail::raw(
            'Voici votre token de réinitialisation : ' . $token .
            "\n\nCe token expire dans 60 minutes.",
            function ($message) use ($request) {
                $message->to($request->email)
                        ->subject('Réinitialisation de mot de passe - EduFlow');
            }
        );

        return response()->json([
            'message' => 'Email de réinitialisation envoyé.', 
            200
        ]);
    }

    // -------------------------------------------------------
    // POST /reset-password
    // L'utilisateur entre le token + nouveau mot de passe
    // -------------------------------------------------------
    public function resetPassword(Request $request)
    {
        // Valider les données reçues
        $request->validate([
            'email'    => ['required', 'email'],
            'token'    => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'], // 'confirmed' vérifie que password et password_confirmation sont identiques
        ]);

        // Chercher le token dans la base de données
        $record = DB::table('password_reset_tokens')
                    ->where('email', $request->email)
                    ->first();

        // Si aucun token trouvé → erreur
        if (!$record) {
            return response()->json([
                'message' => 'Token invalide ou email incorrect.',
            ], 400);
        }

        // Vérifier que le token n'est pas expiré (60 minutes)
        $createdAt = \Carbon\Carbon::parse($record->created_at);
        if ($createdAt->addMinutes(60)->isPast()) {
            return response()->json([
                'message' => 'Token expiré. Veuillez en demander un nouveau.',
            ], 400);
        }

        // Vérifier que le token fourni correspond au token chiffré en BDD
        if (!Hash::check($request->token, $record->token)) {
            return response()->json([
                'message' => 'Token invalide.',
            ], 400);
        }

        // Trouver l'utilisateur et mettre à jour son mot de passe
        $user = User::where('email', $request->email)->firstOrFail();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Supprimer le token utilisé (ne peut pas servir deux fois)
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'Mot de passe réinitialisé avec succès.',
        ]);
    }
}
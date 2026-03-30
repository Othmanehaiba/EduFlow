<?php

// Ce controller gère les paiements via Stripe
// Il crée une session de paiement Stripe et gère le webhook de confirmation

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Payment;
use Illuminate\Http\Request;
use Stripe\Stripe;           // La classe principale de Stripe
use Stripe\Checkout\Session; // Pour créer une session de paiement
use Stripe\Webhook;          // Pour vérifier les webhooks Stripe

class PaymentController extends Controller
{
    // -------------------------------------------------------
    // POST /payment/checkout/{courseId}
    // Créer une session de paiement Stripe pour un cours
    // -------------------------------------------------------
    public function checkout(int $courseId)
    {
        // Récupérer le cours ou retourner 404 si non trouvé
        $course = Course::findOrFail($courseId);

        // Configurer la clé secrète Stripe depuis le fichier .env
        Stripe::setApiKey(config('services.stripe.secret'));

        // Créer une session de paiement Stripe
        // Stripe va générer une page de paiement sécurisée
        $session = Session::create([
            'payment_method_types' => ['card'], // accepter les cartes bancaires

            'line_items' => [[
                'price_data' => [
                    'currency'     => 'eur', // monnaie : euros
                    'unit_amount'  => $course->price * 100, // Stripe veut le prix en centimes
                    'product_data' => [
                        'name'        => $course->title,       // nom du cours
                        'description' => $course->description, // description
                    ],
                ],
                'quantity' => 1, // on achète 1 cours
            ]],

            'mode' => 'payment', // paiement unique (pas abonnement)

            // URL vers laquelle Stripe redirige après paiement réussi
            'success_url' => config('app.url') . '/payment/success?session_id={CHECKOUT_SESSION_ID}',

            // URL vers laquelle Stripe redirige si l'utilisateur annule
            'cancel_url' => config('app.url') . '/payment/cancel',
        ]);

        // Créer un enregistrement de paiement dans notre base de données
        // avec le statut "pending" (en attente de confirmation Stripe)
        $payment = Payment::create([
            'student_id'        => auth('api')->id(), // l'étudiant connecté
            'course_id'         => $courseId,
            'stripe_session_id' => $session->id,      // ID de session Stripe
            'amount'            => $course->price,
            'status'            => 'pending',          // en attente
        ]);

        // Retourner l'URL Stripe et l'ID de paiement
        return response()->json([
            'payment_id'  => $payment->id,   // à utiliser pour s'inscrire ensuite
            'checkout_url' => $session->url,  // URL de la page de paiement Stripe
        ]);
    }

    // -------------------------------------------------------
    // POST /payment/webhook
    // Webhook Stripe : Stripe appelle cette route après paiement
    // IMPORTANT : Cette route NE doit PAS avoir le middleware auth:api
    // -------------------------------------------------------
    public function webhook(Request $request)
    {
        // Récupérer la clé secrète du webhook depuis .env
        $webhookSecret = config('services.stripe.webhook_secret');

        // Récupérer le corps brut de la requête (Stripe l'envoie en JSON brut)
        $payload = $request->getContent();

        // Récupérer la signature Stripe depuis les en-têtes
        $sigHeader = $request->header('Stripe-Signature');

        try {
            // Vérifier que la requête vient vraiment de Stripe (sécurité)
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);

        } catch (\Exception $e) {
            // Si la signature est invalide, rejeter la requête
            return response()->json(['message' => 'Webhook invalide'], 400);
        }

        // Gérer l'événement "checkout.session.completed"
        // Cet événement se déclenche quand le paiement est réussi
        if ($event->type === 'checkout.session.completed') {

            // Récupérer les données de la session Stripe
            $session = $event->data->object;

            // Trouver le paiement dans notre base de données
            // grâce à l'ID de session Stripe
            $payment = Payment::where('stripe_session_id', $session->id)->first();

            if ($payment) {
                // Mettre à jour le statut à "paid" (payé)
                $payment->update([
                    'status'                => 'paid',
                    'stripe_payment_intent' => $session->payment_intent,
                ]);
            }
        }

        // Retourner 200 pour dire à Stripe que tout est OK
        return response()->json(['message' => 'Webhook traité'], 200);
    }
}
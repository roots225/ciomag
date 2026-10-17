<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaymentMethodAdminController extends Controller
{
    public static function defaultConfig(): array
    {
        return [
            'visa' => [
                'enabled' => true,
                'display_name' => 'Carte Bancaire Visa',
                'provider' => 'stripe', // 'stripe' or 'braintree'
                'mode' => 'sandbox',    // 'sandbox' or 'live'
                'currency' => 'EUR',
                'three_d_secure' => true,
                'allow_subscriptions' => true,
                'allow_shop_orders' => true,
                'allow_events' => true,
                'statement_descriptor' => 'CIOMAG MEDIA',
            ],
            'stripe' => [
                'publishable_key' => 'pk_test_51CiomagVisaStripeDemoKey',
                'secret_key' => 'sk_test_51CiomagVisaStripeSecretDemo',
                'webhook_secret' => 'whsec_ciomag_stripe_visa_webhook',
            ],
            'braintree' => [
                'environment' => 'sandbox',
                'merchant_id' => 'ciomag_bt_merchant_demo',
                'public_key' => 'bt_pub_ciomag_visa_key',
                'private_key' => 'bt_priv_ciomag_visa_secret',
                'merchant_account_id' => 'ciomag_eur_visa',
            ],
            'updated_at' => now()->toIso8601String(),
        ];
    }

    public function index(): View
    {
        $saved = Setting::getValue('payment_methods', []);
        $defaults = self::defaultConfig();

        $config = [
            'visa' => array_merge($defaults['visa'], $saved['visa'] ?? []),
            'stripe' => array_merge($defaults['stripe'], $saved['stripe'] ?? []),
            'braintree' => array_merge($defaults['braintree'], $saved['braintree'] ?? []),
            'updated_at' => $saved['updated_at'] ?? null,
        ];

        return view('admin.payment-methods.index', compact('config'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:120'],
            'provider' => ['required', 'in:stripe,braintree'],
            'mode' => ['required', 'in:sandbox,live'],
            'currency' => ['required', 'in:EUR,USD,XOF'],
            'statement_descriptor' => ['nullable', 'string', 'max:22'],
            // Stripe credentials
            'stripe_publishable_key' => ['nullable', 'string', 'max:255'],
            'stripe_secret_key' => ['nullable', 'string', 'max:255'],
            'stripe_webhook_secret' => ['nullable', 'string', 'max:255'],
            // Braintree credentials
            'braintree_merchant_id' => ['nullable', 'string', 'max:255'],
            'braintree_public_key' => ['nullable', 'string', 'max:255'],
            'braintree_private_key' => ['nullable', 'string', 'max:255'],
            'braintree_merchant_account_id' => ['nullable', 'string', 'max:255'],
        ]);

        $payload = [
            'visa' => [
                'enabled' => $request->boolean('visa_enabled'),
                'display_name' => $validated['display_name'],
                'provider' => $validated['provider'],
                'mode' => $validated['mode'],
                'currency' => $validated['currency'],
                'three_d_secure' => $request->boolean('three_d_secure'),
                'allow_subscriptions' => $request->boolean('allow_subscriptions'),
                'allow_shop_orders' => $request->boolean('allow_shop_orders'),
                'allow_events' => $request->boolean('allow_events'),
                'statement_descriptor' => $validated['statement_descriptor'] ?? 'CIOMAG MEDIA',
            ],
            'stripe' => [
                'publishable_key' => $validated['stripe_publishable_key'] ?? '',
                'secret_key' => $validated['stripe_secret_key'] ?? '',
                'webhook_secret' => $validated['stripe_webhook_secret'] ?? '',
            ],
            'braintree' => [
                'environment' => $validated['mode'],
                'merchant_id' => $validated['braintree_merchant_id'] ?? '',
                'public_key' => $validated['braintree_public_key'] ?? '',
                'private_key' => $validated['braintree_private_key'] ?? '',
                'merchant_account_id' => $validated['braintree_merchant_account_id'] ?? '',
            ],
            'updated_at' => now()->toIso8601String(),
        ];

        Setting::updateOrCreate(
            ['key' => 'payment_methods'],
            [
                'value' => $payload,
                'description' => 'Configuration de la méthode de paiement Carte Visa (Stripe / Braintree)',
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]
        );

        $providerLabel = $validated['provider'] === 'stripe' ? 'Stripe' : 'Braintree';

        return back()->with('success', "Méthode de paiement Visa ({$providerLabel}) enregistrée avec succès.");
    }

    public function testConnection(Request $request): RedirectResponse
    {
        $saved = Setting::getValue('payment_methods', self::defaultConfig());
        $provider = $request->input('provider', $saved['visa']['provider'] ?? 'stripe');
        $mode = $saved['visa']['mode'] ?? 'sandbox';

        if ($provider === 'stripe') {
            $pubKey = $saved['stripe']['publishable_key'] ?? '';
            $secKey = $saved['stripe']['secret_key'] ?? '';

            if (empty($pubKey) || empty($secKey)) {
                return back()->with('error', 'Test Stripe échoué : veuillez renseigner la clé publique (Publishable Key) et la clé secrète (Secret Key).');
            }

            return back()->with('success', "Connexion passerelle Visa via Stripe ({$mode}) vérifiée avec succès.");
        }

        $merchantId = $saved['braintree']['merchant_id'] ?? '';
        $publicKey = $saved['braintree']['public_key'] ?? '';
        $privateKey = $saved['braintree']['private_key'] ?? '';

        if (empty($merchantId) || empty($publicKey) || empty($privateKey)) {
            return back()->with('error', 'Test Braintree échoué : veuillez renseigner le Merchant ID, la Public Key et la Private Key.');
        }

        return back()->with('success', "Connexion passerelle Visa via Braintree ({$mode}) vérifiée avec succès.");
    }
}

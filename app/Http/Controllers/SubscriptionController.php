<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use App\Http\Controllers\Admin\PaymentMethodAdminController;
use App\Mail\SubscriptionOtpMail;
use App\Models\EmailOtpCode;
use App\Models\Post;
use App\Models\Role;
use App\Models\Setting;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    /**
     * Display the subscription offers and premium preview.
     */
    public function index(): View
    {
        $plans = SubscriptionPlan::query()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();

        $premiumPosts = Post::query()
            ->articles()
            ->published()
            ->where('is_premium', true)
            ->with(['author', 'categories', 'featuredMedia'])
            ->orderByDesc('published_at')
            ->limit(6)
            ->get();

        return view('magazine.subscriptions.index', compact('plans', 'premiumPosts'));
    }

    /**
     * Redirect to the featured or first active subscription plan checkout.
     */
    public function redirectToFirstPlan(): RedirectResponse
    {
        $plan = SubscriptionPlan::query()
            ->where('is_active', true)
            ->orderByDesc('is_featured')
            ->orderBy('display_order')
            ->firstOrFail();

        return redirect()->route('magazine.subscriptions.subscribe.show', $plan->slug);
    }

    /**
     * Display the multi-step subscription page for a specific plan.
     */
    public function showCheckout(string $slug): View
    {
        $plan = SubscriptionPlan::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $allPlans = SubscriptionPlan::query()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();

        $savedPayments = Setting::getValue('payment_methods', []);
        $defaults = PaymentMethodAdminController::defaultConfig();
        $paymentConfig = [
            'visa' => array_merge($defaults['visa'], $savedPayments['visa'] ?? []),
            'stripe' => array_merge($defaults['stripe'], $savedPayments['stripe'] ?? []),
            'braintree' => array_merge($defaults['braintree'], $savedPayments['braintree'] ?? []),
        ];

        return view('magazine.subscriptions.checkout', compact('plan', 'allPlans', 'paymentConfig'));
    }

    /**
     * Step 3: Start account creation and send 6-digit OTP code to email.
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'uuid', 'exists:subscription_plans,id'],
            'display_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:6'],
            'company' => ['nullable', 'string', 'max:150'],
            'job_title' => ['nullable', 'string', 'max:150'],
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);
        $normalizedEmail = strtolower(trim($validated['email']));

        $user = $request->user();

        if (! $user) {
            $user = User::where('email', $normalizedEmail)->first();

            if (! $user) {
                if (empty($validated['password'])) {
                    throw ValidationException::withMessages([
                        'password' => ['Veuillez définir un mot de passe sécurisé (au moins 6 caractères).'],
                    ]);
                }

                $subscriberRole = Role::firstOrCreate(
                    ['slug' => 'subscriber'],
                    ['name' => 'Lecteur Abonné', 'permissions' => ['comments.create']]
                );

                $baseUsername = Str::slug($validated['display_name']) ?: 'abonne';
                $username = $baseUsername.'-'.Str::lower(Str::random(4));

                $user = User::create([
                    'role_id' => $subscriberRole->id,
                    'email' => $normalizedEmail,
                    'username' => $username,
                    'password_hash' => Hash::make($validated['password']),
                    'display_name' => $validated['display_name'],
                    'status' => UserStatus::Pending,
                ]);
            } else {
                // If user exists and provides updated display name
                if (! empty($validated['display_name']) && $user->display_name !== $validated['display_name']) {
                    $user->update(['display_name' => $validated['display_name']]);
                }
            }
        }

        // Generate 6-digit OTP code
        $otp = EmailOtpCode::generateFor(
            email: $normalizedEmail,
            userId: $user?->id,
            purpose: 'subscription_verification',
            ipAddress: $request->ip(),
            expiresInMinutes: 15
        );

        // Send email with OTP code
        try {
            Mail::to($normalizedEmail)->send(
                new SubscriptionOtpMail(
                    code: $otp->code,
                    displayName: $user?->display_name ?? $validated['display_name'],
                    planName: $plan->name
                )
            );
        } catch (\Throwable $e) {
            report($e);
        }

        $maskedEmail = $this->maskEmail($normalizedEmail);

        return response()->json([
            'success' => true,
            'email' => $normalizedEmail,
            'masked_email' => $maskedEmail,
            'message' => "Un code de validation à 6 chiffres a été envoyé à {$maskedEmail}.",
        ]);
    }

    /**
     * Step 4: Verify the 6-digit OTP code and authenticate user.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $normalizedEmail = strtolower(trim($validated['email']));
        $otp = EmailOtpCode::verifyCode($normalizedEmail, $validated['code'], 'subscription_verification');

        if (! $otp) {
            return response()->json([
                'success' => false,
                'message' => 'Le code saisi est incorrect ou a expiré. Veuillez vérifier votre boîte mail ou demander un nouveau code.',
            ], 422);
        }

        // Mark user email as verified and activate account
        $user = User::where('email', $normalizedEmail)->first();

        if ($user) {
            $user->update([
                'email_verified_at' => Carbon::now(),
                'status' => UserStatus::Active,
            ]);

            Auth::login($user);
        }

        session()->put('subscription_verified_email', $normalizedEmail);

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user?->id,
                'display_name' => $user?->display_name ?? 'Abonné',
                'email' => $normalizedEmail,
            ],
            'message' => 'Adresse email validée avec succès !',
        ]);
    }

    /**
     * Resend a fresh 6-digit OTP code.
     */
    public function resendOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'plan_id' => ['nullable', 'uuid', 'exists:subscription_plans,id'],
        ]);

        $normalizedEmail = strtolower(trim($validated['email']));
        $plan = ! empty($validated['plan_id']) ? SubscriptionPlan::find($validated['plan_id']) : null;
        $user = User::where('email', $normalizedEmail)->first();

        $otp = EmailOtpCode::generateFor(
            email: $normalizedEmail,
            userId: $user?->id,
            purpose: 'subscription_verification',
            ipAddress: $request->ip(),
            expiresInMinutes: 15
        );

        try {
            Mail::to($normalizedEmail)->send(
                new SubscriptionOtpMail(
                    code: $otp->code,
                    displayName: $user?->display_name ?? 'Cher lecteur',
                    planName: $plan?->name ?? 'CIOMAG Premium'
                )
            );
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'success' => true,
            'message' => 'Un nouveau code de validation à 6 chiffres vous a été envoyé.',
        ]);
    }

    /**
     * Step 5: Process subscription payment and return success or failure.
     */
    public function processPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'uuid', 'exists:subscription_plans,id'],
            'payment_method' => ['required', 'in:card,mobile_money,corporate_invoice'],
            'simulate_failure' => ['nullable', 'boolean'],
            // Card details
            'card_name' => ['nullable', 'string', 'max:150'],
            'card_number' => ['nullable', 'string', 'max:30'],
            'card_expiry' => ['nullable', 'string', 'max:10'],
            'card_cvc' => ['nullable', 'string', 'max:4'],
            // Mobile money details
            'mobile_phone' => ['nullable', 'string', 'max:60'],
            'mobile_operator' => ['nullable', 'string', 'max:50'],
            // Corporate invoice
            'company_name' => ['nullable', 'string', 'max:150'],
            'company_siret' => ['nullable', 'string', 'max:60'],
            'company_address' => ['nullable', 'string', 'max:500'],
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);

        // Determine authenticated user
        $user = $request->user();

        if (! $user && session()->has('subscription_verified_email')) {
            $user = User::where('email', session('subscription_verified_email'))->first();
            if ($user) {
                Auth::login($user);
            }
        }

        if (! $user) {
            return response()->json([
                'success' => false,
                'error_code' => 'unauthenticated',
                'message' => 'Veuillez valider votre adresse email avant de procéder au règlement.',
            ], 403);
        }

        // Test failure trigger: user requested failure simulation or card ends in 0002
        $cardNumberClean = preg_replace('/\s+/', '', (string) ($validated['card_number'] ?? ''));
        $shouldFail = $request->boolean('simulate_failure') || str_ends_with($cardNumberClean, '0002');

        if ($shouldFail) {
            return response()->json([
                'success' => false,
                'error_code' => 'payment_declined',
                'message' => 'La transaction a été refusée par la banque émettrice : carte non autorisée, solde insuffisant ou protocole 3D Secure interrompu.',
            ], 422);
        }

        $expiresAt = match ($plan->billing_interval) {
            'monthly' => Carbon::now()->addMonth(),
            'yearly' => Carbon::now()->addYear(),
            'lifetime' => null,
            default => Carbon::now()->addMonth(),
        };

        $reference = 'SUB-'.date('Y').'-'.strtoupper(Str::random(6));

        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'amount_paid' => $plan->price,
            'currency' => $plan->currency,
            'payment_method' => $validated['payment_method'],
            'payment_reference' => $reference,
            'starts_at' => Carbon::now(),
            'expires_at' => $expiresAt,
        ]);

        return response()->json([
            'success' => true,
            'subscription' => [
                'id' => $subscription->id,
                'reference' => $reference,
                'plan_name' => $plan->name,
                'amount_formatted' => $plan->formattedPrice(),
                'interval' => $plan->intervalLabel(),
                'expires_at_formatted' => $expiresAt ? $expiresAt->format('d/m/Y') : 'Accès illimité',
                'user_name' => $user->display_name,
                'user_email' => $user->email,
                'payment_method_label' => match ($validated['payment_method']) {
                    'card' => 'Carte Bancaire Visa',
                    'mobile_money' => 'Mobile Money ('.($validated['mobile_operator'] ?? 'Orange/Wave/MTN').')',
                    'corporate_invoice' => 'Facturation Entreprise',
                    default => $validated['payment_method'],
                },
            ],
            'message' => "Félicitations {$user->display_name} ! Votre abonnement « {$plan->name} » est désormais actif.",
        ]);
    }

    /**
     * Backward-compatible direct subscription handler (used in existing tests & direct post).
     */
    public function subscribe(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'uuid', 'exists:subscription_plans,id'],
            'payment_method' => ['required', 'in:card,mobile_money,corporate_invoice'],
            'display_name' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);
        $user = $request->user();

        if (! $user) {
            $request->validate([
                'display_name' => ['required', 'string', 'max:150'],
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', 'string', 'min:6'],
            ]);

            $subscriberRole = Role::firstOrCreate(
                ['slug' => 'subscriber'],
                ['name' => 'Lecteur Abonné', 'permissions' => ['comments.create']]
            );

            $user = User::where('email', $validated['email'])->first();

            if (! $user) {
                $baseUsername = Str::slug($validated['display_name']) ?: 'abonne';
                $username = $baseUsername.'-'.Str::lower(Str::random(4));

                $user = User::create([
                    'role_id' => $subscriberRole->id,
                    'email' => $validated['email'],
                    'email_verified_at' => Carbon::now(),
                    'username' => $username,
                    'password_hash' => Hash::make($validated['password']),
                    'display_name' => $validated['display_name'],
                    'status' => UserStatus::Active,
                ]);
            }

            Auth::login($user);
        }

        $expiresAt = match ($plan->billing_interval) {
            'monthly' => Carbon::now()->addMonth(),
            'yearly' => Carbon::now()->addYear(),
            'lifetime' => null,
            default => Carbon::now()->addMonth(),
        };

        UserSubscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'amount_paid' => $plan->price,
            'currency' => $plan->currency,
            'payment_method' => $validated['payment_method'],
            'payment_reference' => 'SUB-'.strtoupper(Str::random(8)),
            'starts_at' => Carbon::now(),
            'expires_at' => $expiresAt,
        ]);

        return redirect()->route('magazine.subscriptions.index')
            ->with('success', "Félicitations {$user->display_name} ! Votre abonnement « {$plan->name} » est désormais actif. Vous avez un accès illimité à tous les articles exclusifs Premium.");
    }

    /**
     * Mask an email address for privacy display (e.g. j***i@domain.com).
     */
    protected function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        $name = $parts[0];
        $domain = $parts[1] ?? '';

        $len = strlen($name);
        if ($len <= 2) {
            $maskedName = substr($name, 0, 1).'***';
        } else {
            $maskedName = substr($name, 0, 1).str_repeat('*', min(4, $len - 2)).substr($name, -1);
        }

        return $maskedName.'@'.$domain;
    }
}

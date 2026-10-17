<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use App\Models\Post;
use App\Models\Role;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
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
                $username = $baseUsername . '-' . Str::lower(Str::random(4));

                $user = User::create([
                    'role_id' => $subscriberRole->id,
                    'email' => $validated['email'],
                    'username' => $username,
                    'password_hash' => Hash::make($validated['password']),
                    'display_name' => $validated['display_name'],
                    'status' => UserStatus::Active,
                ]);
            }

            Auth::login($user);
        }

        $expiresAt = match ($plan->billing_interval) {
            'monthly' => now()->addMonth(),
            'yearly' => now()->addYear(),
            'lifetime' => null,
            default => now()->addMonth(),
        };

        UserSubscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'amount_paid' => $plan->price,
            'currency' => $plan->currency,
            'payment_method' => $validated['payment_method'],
            'payment_reference' => 'SUB-' . strtoupper(Str::random(8)),
            'starts_at' => now(),
            'expires_at' => $expiresAt,
        ]);

        return redirect()->route('magazine.subscriptions.index')
            ->with('success', "Félicitations {$user->display_name} ! Votre abonnement « {$plan->name} » est désormais actif. Vous avez un accès illimité à tous les articles exclusifs Premium.");
    }
}

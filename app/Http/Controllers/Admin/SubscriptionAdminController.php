<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SubscriptionAdminController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = min(50, max(1, (int) $request->query('per_page', 10)));
        $statusFilter = $request->query('status');
        $search = trim((string) $request->query('q', ''));

        $plans = SubscriptionPlan::query()
            ->withCount('subscriptions')
            ->orderBy('display_order')
            ->get();

        $subscriptions = UserSubscription::query()
            ->with(['user', 'plan'])
            ->when($statusFilter, fn ($q) => $q->where('status', $statusFilter))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('payment_reference', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($uq) use ($search) {
                            $uq->where('display_name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        $users = User::query()
            ->orderBy('display_name')
            ->get(['id', 'display_name', 'email']);

        return view('admin.subscriptions.index', compact(
            'plans',
            'subscriptions',
            'users',
            'statusFilter',
            'search'
        ));
    }

    public function storePlan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'badge' => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:500'],
            'billing_interval' => ['required', 'in:monthly,yearly,lifetime'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'features_text' => ['required', 'string'],
            'display_order' => ['nullable', 'integer'],
        ]);

        $features = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $validated['features_text']))));

        SubscriptionPlan::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::lower(Str::random(3)),
            'badge' => $validated['badge'] ?? null,
            'description' => $validated['description'] ?? null,
            'billing_interval' => $validated['billing_interval'],
            'price' => $validated['price'],
            'currency' => $validated['currency'] ?? 'EUR',
            'features' => $features,
            'includes_print' => $request->boolean('includes_print'),
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active', true),
            'display_order' => (int) ($validated['display_order'] ?? 0),
        ]);

        return back()->with('success', 'Formule d\'abonnement créée avec succès.');
    }

    public function updatePlan(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'badge' => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:500'],
            'billing_interval' => ['required', 'in:monthly,yearly,lifetime'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'features_text' => ['required', 'string'],
            'display_order' => ['nullable', 'integer'],
        ]);

        $features = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $validated['features_text']))));

        $plan->update([
            'name' => $validated['name'],
            'badge' => $validated['badge'] ?? null,
            'description' => $validated['description'] ?? null,
            'billing_interval' => $validated['billing_interval'],
            'price' => $validated['price'],
            'currency' => $validated['currency'] ?? $plan->currency ?? 'EUR',
            'features' => $features,
            'includes_print' => $request->boolean('includes_print'),
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active'),
            'display_order' => (int) ($validated['display_order'] ?? $plan->display_order ?? 0),
        ]);

        return back()->with('success', 'Formule « ' . $plan->name . ' » modifiée avec succès.');
    }

    public function togglePlan(SubscriptionPlan $plan): RedirectResponse
    {
        $plan->update(['is_active' => ! $plan->is_active]);

        $statusText = $plan->is_active ? 'activée' : 'désactivée';

        return back()->with('success', "Formule « {$plan->name} » {$statusText}.");
    }

    public function destroyPlan(SubscriptionPlan $plan): RedirectResponse
    {
        if ($plan->subscriptions()->exists()) {
            $plan->update(['is_active' => false]);

            return back()->with('success', 'Formule désactivée (des abonnements y sont rattachés).');
        }

        $plan->delete();

        return back()->with('success', 'Formule d\'abonnement supprimée.');
    }

    public function storeSubscription(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'plan_id' => ['required', 'uuid', 'exists:subscription_plans,id'],
            'status' => ['required', 'in:active,cancelled,expired,pending'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'max:40'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);
        $startsAt = ! empty($validated['starts_at']) ? $validated['starts_at'] : now();

        if (! empty($validated['expires_at'])) {
            $expiresAt = $validated['expires_at'];
        } else {
            $expiresAt = match ($plan->billing_interval) {
                'monthly' => now()->addMonth(),
                'yearly' => now()->addYear(),
                default => null,
            };
        }

        UserSubscription::create([
            'user_id' => $validated['user_id'],
            'plan_id' => $plan->id,
            'status' => $validated['status'],
            'amount_paid' => $validated['amount_paid'] ?? $plan->price,
            'currency' => $plan->currency ?? 'EUR',
            'payment_method' => $validated['payment_method'] ?? 'visa',
            'payment_reference' => 'ADM-' . strtoupper(Str::random(8)),
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
        ]);

        return back()->with('success', 'Abonnement attribué à l\'utilisateur avec succès.');
    }

    public function updateSubscription(Request $request, UserSubscription $subscription): RedirectResponse
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'uuid', 'exists:subscription_plans,id'],
            'status' => ['required', 'in:active,cancelled,expired,pending'],
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'max:40'],
            'starts_at' => ['required', 'date'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $subscription->update([
            'plan_id' => $validated['plan_id'],
            'status' => $validated['status'],
            'amount_paid' => $validated['amount_paid'],
            'payment_method' => $validated['payment_method'] ?? $subscription->payment_method,
            'starts_at' => $validated['starts_at'],
            'expires_at' => $validated['expires_at'] ?: null,
        ]);

        return back()->with('success', 'Abonnement de l\'utilisateur mis à jour avec succès.');
    }

    public function toggleSubscription(UserSubscription $subscription): RedirectResponse
    {
        $newStatus = $subscription->status === 'active' ? 'cancelled' : 'active';

        $updates = ['status' => $newStatus];
        if ($newStatus === 'active' && $subscription->expires_at && $subscription->expires_at->isPast()) {
            $updates['expires_at'] = now()->addYear();
        }

        $subscription->update($updates);

        $label = $newStatus === 'active' ? 'activé' : 'désactivé';

        return back()->with('success', "L'abonnement de {$subscription->user?->display_name} a été {$label}.");
    }

    public function updateSubscriptionStatus(Request $request, UserSubscription $subscription): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:active,cancelled,expired,pending'],
        ]);

        $subscription->update(['status' => $validated['status']]);

        return back()->with('success', 'Statut de l\'abonnement mis à jour.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $roleFilter = $request->query('role');
        $statusFilter = $request->query('status');
        $perPage = min(50, max(1, (int) $request->query('per_page', 10)));

        $roles = Role::orderBy('id')->get();

        $users = User::query()
            ->with('role')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('display_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->when($roleFilter, function ($q) use ($roleFilter) {
                $q->where('role_id', $roleFilter);
            })
            ->when($statusFilter, function ($q) use ($statusFilter) {
                $q->where('status', $statusFilter);
            })
            ->orderBy('display_name')
            ->paginate($perPage)
            ->withQueryString();

        $stats = [
            'total' => User::count(),
            'active' => User::where('status', UserStatus::Active)->count(),
            'suspended' => User::where('status', UserStatus::Suspended)->count(),
            'pending' => User::where('status', UserStatus::Pending)->count(),
            'admins' => User::whereHas('role', fn ($q) => $q->where('slug', 'admin'))->count(),
        ];

        return view('admin.users.index', compact(
            'users',
            'roles',
            'stats',
            'search',
            'roleFilter',
            'statusFilter'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'username' => ['nullable', 'string', 'max:100', 'unique:users,username'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['required', 'string', 'min:6'],
            'status' => ['required', 'in:active,suspended,pending'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'avatar_url' => ['nullable', 'string', 'max:1000'],
        ]);

        $username = ! empty($validated['username'])
            ? Str::slug($validated['username'])
            : Str::slug($validated['display_name']).'-'.random_int(10, 99);

        $user = User::create([
            'display_name' => $validated['display_name'],
            'email' => $validated['email'],
            'username' => $username,
            'role_id' => $validated['role_id'],
            'password_hash' => Hash::make($validated['password']),
            'status' => $validated['status'],
            'bio' => $validated['bio'] ?? null,
            'avatar_url' => $validated['avatar_url'] ?? null,
            'social_links' => [],
        ]);

        return back()->with('success', "Utilisateur « {$user->display_name} » créé avec succès.");
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'username' => ['nullable', 'string', 'max:100', Rule::unique('users', 'username')->ignore($user->id)],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['nullable', 'string', 'min:6'],
            'status' => ['required', 'in:active,suspended,pending'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'avatar_url' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($user->id === $request->user()->id && $validated['status'] !== 'active') {
            return back()->with('error', 'Vous ne pouvez pas suspendre ou désactiver votre propre compte administrateur.');
        }

        $user->display_name = $validated['display_name'];
        $user->email = $validated['email'];
        if (! empty($validated['username'])) {
            $user->username = Str::slug($validated['username']);
        }
        $user->role_id = $validated['role_id'];
        $user->status = $validated['status'];
        $user->bio = $validated['bio'] ?? null;
        $user->avatar_url = $validated['avatar_url'] ?? null;

        if (! empty($validated['password'])) {
            $user->password_hash = Hash::make($validated['password']);
        }

        $user->save();

        return back()->with('success', "Utilisateur « {$user->display_name} » mis à jour avec succès.");
    }

    public function toggle(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Vous ne pouvez pas modifier le statut de votre propre compte connecté.');
        }

        $newStatus = $user->status === UserStatus::Active ? UserStatus::Suspended : UserStatus::Active;
        $user->update(['status' => $newStatus]);

        $statusText = $newStatus === UserStatus::Active ? 'activé' : 'désactivé';

        return back()->with('success', "Le compte de « {$user->display_name} » a été {$statusText}.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte connecté.');
        }

        $name = $user->display_name;
        $user->delete();

        return back()->with('success', "L'utilisateur « {$name} » a été définitivement supprimé.");
    }
}

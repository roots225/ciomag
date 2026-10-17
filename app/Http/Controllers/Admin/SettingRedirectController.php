<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Redirect;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingRedirectController extends Controller
{
    public function index(): View
    {
        $settings = Setting::with('updater')->get()->keyBy('key');
        $redirects = Redirect::orderByDesc('created_at')->get();
        $users = User::with('role')->orderBy('display_name')->get();
        $roles = Role::orderBy('id')->get();

        return view('admin.settings.index', compact('settings', 'redirects', 'users', 'roles'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:100'],
            'tagline' => ['required', 'string', 'max:255'],
            'edition' => ['nullable', 'string', 'max:150'],
            'contact_email' => ['nullable', 'email', 'max:150'],
            'footer_about' => ['nullable', 'string', 'max:1000'],
            'default_meta_title' => ['nullable', 'string', 'max:255'],
            'default_meta_description' => ['nullable', 'string', 'max:500'],
        ]);

        Setting::updateOrCreate(
            ['key' => 'site_general'],
            [
                'value' => [
                    'site_name' => $validated['site_name'],
                    'tagline' => $validated['tagline'],
                    'edition' => $validated['edition'] ?? '',
                    'contact_email' => $validated['contact_email'] ?? '',
                    'footer_about' => $validated['footer_about'] ?? '',
                ],
                'description' => 'Paramètres généraux d\'identité du magazine',
                'updated_by' => $request->user()->id,
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'seo_defaults'],
            [
                'value' => [
                    'default_meta_title' => $validated['default_meta_title'] ?? '',
                    'default_meta_description' => $validated['default_meta_description'] ?? '',
                ],
                'description' => 'Métadonnées SEO par défaut',
                'updated_by' => $request->user()->id,
            ]
        );

        return back()->with('success', 'Paramètres généraux et SEO mis à jour.');
    }

    public function storeRedirect(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'source_path' => ['required', 'string', 'max:500'],
            'target_path' => ['required', 'string', 'max:500'],
            'status_code' => ['required', 'integer', Rule::in([301, 302, 307, 308])],
        ]);

        $sourcePath = '/' . ltrim(trim($validated['source_path']), '/');

        Redirect::updateOrCreate(
            ['source_path' => $sourcePath],
            [
                'target_path' => trim($validated['target_path']),
                'status_code' => (int) $validated['status_code'],
                'is_active' => true,
            ]
        );

        return back()->with('success', 'Redirection SEO enregistrée.');
    }

    public function toggleRedirect(Redirect $redirect): RedirectResponse
    {
        $redirect->update(['is_active' => ! $redirect->is_active]);

        return back()->with('success', 'Statut de la redirection modifié.');
    }

    public function destroyRedirect(Redirect $redirect): RedirectResponse
    {
        $redirect->delete();

        return back()->with('success', 'Redirection supprimée.');
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'username' => ['nullable', 'string', 'max:100', 'unique:users,username'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['required', 'string', 'min:6'],
            'bio' => ['nullable', 'string'],
            'avatar_url' => ['nullable', 'string', 'max:1000'],
        ]);

        User::create([
            'role_id' => $validated['role_id'],
            'display_name' => $validated['display_name'],
            'email' => $validated['email'],
            'username' => ! empty($validated['username'])
                ? Str::slug($validated['username'])
                : Str::slug($validated['display_name']) . '-' . random_int(10, 99),
            'password_hash' => Hash::make($validated['password']),
            'bio' => $validated['bio'] ?? null,
            'avatar_url' => $validated['avatar_url'] ?? null,
            'status' => 'active',
            'social_links' => [],
        ]);

        return back()->with('success', 'Membre de l\'équipe ajouté.');
    }
}

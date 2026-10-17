<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->load('role');

        return view('admin.profile.edit', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'username' => ['nullable', 'string', 'max:100', Rule::unique('users', 'username')->ignore($user->id)],
            'bio' => ['nullable', 'string', 'max:1000'],
            'avatar_url' => ['nullable', 'string', 'max:1000'],
            'current_password' => ['nullable', 'required_with:new_password', 'string'],
            'new_password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [
            'new_password.confirmed' => 'La confirmation du nouveau mot de passe ne correspond pas.',
            'new_password.min' => 'Le nouveau mot de passe doit comporter au moins 6 caractères.',
            'current_password.required_with' => 'Veuillez saisir votre mot de passe actuel pour définir un nouveau mot de passe.',
        ]);

        if (! empty($validated['new_password'])) {
            if (! Hash::check($validated['current_password'], $user->password_hash)) {
                return back()->withErrors([
                    'current_password' => 'Le mot de passe actuel saisi est incorrect.',
                ])->withInput();
            }

            $user->password_hash = Hash::make($validated['new_password']);
        }

        $user->display_name = $validated['display_name'];
        $user->email = $validated['email'];
        if (! empty($validated['username'])) {
            $user->username = Str::slug($validated['username']);
        }
        $user->bio = $validated['bio'] ?? null;
        $user->avatar_url = $validated['avatar_url'] ?? null;
        $user->save();

        return back()->with('success', 'Votre profil administrateur a été mis à jour avec succès.');
    }
}

@extends('layouts.magazine')

@section('meta_title', 'Connexion Rédaction | CIOMAG')

@section('content')
<div class="max-w-md mx-auto px-4 py-16">
    <div class="bg-white rounded-2xl border border-slate-200 p-8 shadow-sm">
        <div class="text-center mb-6">
            <span class="px-2.5 py-1 text-[11px] font-bold uppercase tracking-widest bg-red-700 text-white rounded-xs inline-flex items-center gap-1.5">
                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                <span>Studio Éditorial</span>
            </span>
            <h1 class="mt-3 text-2xl font-black text-slate-900">Connexion à CIOMAG</h1>
            <p class="mt-1 text-xs text-slate-500">Accédez au back-office de gestion du magazine</p>
        </div>

        <form action="{{ route('login.submit') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Email professionnel</label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email', 'admin@ciomag.com') }}"
                    required
                    class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-red-700"
                >
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Mot de passe</label>
                <input
                    type="password"
                    name="password"
                    value="password"
                    required
                    class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-red-700"
                >
            </div>

            <div class="flex items-center justify-between pt-1">
                <x-ui.checkbox
                    name="remember"
                    value="1"
                    :checked="false"
                    label="Rester connecté sur ce poste"
                />
            </div>

            <button type="submit" class="w-full py-2.5 bg-slate-950 hover:bg-red-700 text-white text-sm font-bold rounded-xl transition inline-flex items-center justify-center gap-2 cursor-pointer">
                <i data-lucide="log-in" class="w-4 h-4"></i>
                <span>Se connecter au Studio</span>
            </button>
        </form>

        <div class="mt-6 pt-5 border-t border-slate-100 text-xs text-slate-500 space-y-1">
            <p class="font-semibold text-slate-700">Comptes de démonstration pré-remplis :</p>
            <p>• Admin : <code>admin@ciomag.com</code> / <code>password</code></p>
            <p>• Rédactrice en chef : <code>redaction@ciomag.com</code> / <code>password</code></p>
            <p>• Journaliste : <code>cyber@ciomag.com</code> / <code>password</code></p>
        </div>
    </div>
</div>
@endsection

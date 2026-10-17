<?php

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->status !== UserStatus::Active || ! $user->isAdminOrEditor()) {
            abort(403, 'Accès réservé à la rédaction et aux administrateurs du magazine.');
        }

        return $next($request);
    }
}

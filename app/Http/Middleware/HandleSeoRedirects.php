<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class HandleSeoRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET')) {
            $path = '/'.ltrim($request->path(), '/');

            $redirects = Cache::remember('cms_active_redirects', 3600, function () {
                return Redirect::query()
                    ->where('is_active', true)
                    ->get(['id', 'source_path', 'target_path', 'status_code'])
                    ->keyBy('source_path')
                    ->toArray();
            });

            if (isset($redirects[$path])) {
                $match = $redirects[$path];
                Redirect::where('id', $match['id'])->increment('hits');

                return redirect($match['target_path'], (int) $match['status_code']);
            }
        }

        return $next($request);
    }
}

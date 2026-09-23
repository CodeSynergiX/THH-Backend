<?php

namespace App\Http\Middleware;

use App\Domains\Settings\Models\Setting;
use App\Domains\Users\RoleHome;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePublicPortalEnabled
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $enabled = (bool) Setting::get('public_portal_enabled', true);

        if (! $enabled) {
            // When root '/' is visited and public portal is disabled:
            if ($request->is('/') || $request->routeIs('home')) {
                if ($user = $request->user()) {
                    return redirect()->to(RoleHome::url($user));
                }

                return redirect()->route('login');
            }

            abort(404, 'Public portal is currently disabled.');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Ensure the authenticated user has one of the allowed roles.
     * Read requests are sent to the user's dashboard; write requests are forbidden.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user && $user->hasAnyRole($roles)) {
            return $next($request);
        }

        if ($request->isMethod('GET') && $user) {
            return redirect()->to('/app/'.$user->defaultDashboard());
        }

        abort(403);
    }
}

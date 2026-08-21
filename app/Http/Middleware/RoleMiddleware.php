<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Section 4.2: role is checked centrally at the route-file level (see
 * routes/web.php groups), not scattered inside individual controller
 * methods, so access rules stay auditable in one place. Ownership checks
 * (a publisher editing only their own books) still happen in the
 * controller/policy layer — role tells you what KIND of user this is,
 * not which records they own.
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! Auth::check() || Auth::user()->role !== $role) {
            $guardRoute = $role === 'admin' ? 'admin.login' : ($role === 'publisher' ? 'publisher.login' : 'account.login');
            return redirect()->route($guardRoute);
        }
        if (Auth::user()->status !== 'active') {
            Auth::logout();
            return redirect()->route($guardRoute)->withErrors(['email' => 'Your account has been suspended.']);
        }
        if ($role === 'publisher' && Auth::user()->publisher?->approval_status !== 'approved') {
            Auth::logout();
            return redirect()->route('publisher.login')->withErrors(['email' => 'Your publisher account is not approved.']);
        }
        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class CheckInactivity
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->last_activity) {
            if (\Carbon\Carbon::parse($user->last_activity)->addMinutes(30)->isPast()) {
                $user->currentAccessToken()->delete();
                return response()->json(['message' => 'Session expired due to 30 minutes of inactivity.'], 401);
            }
        }

        if ($user) {
            $user->update(['last_activity' => \Carbon\Carbon::now()]);
        }

        return $next($request);
    }
}

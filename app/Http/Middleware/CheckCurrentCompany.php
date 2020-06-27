<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckCurrentCompany
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (is_null(auth()->user()->currentCompany)) {
            return redirect('current_company/create')->with('status', 'Select a company first!');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use App\Account;

class CheckOutputVat
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
        if (Account::where('title', 'Output VAT')->count() < 1) {
            return redirect('accounts/create')->with('status', 'Create Output VAT account first!');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use App\Account;

class CheckAccountsPayable
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
        if (Account::where('title', 'Accounts Payable')->count() < 1) {
            return redirect('accounts/create')->with('status', 'Create Accounts Payable account first!');
        }

        return $next($request);
    }
}

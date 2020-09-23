<?php

namespace App\Http\Middleware;

use Closure;
use App\Account;

class CheckAccountsReceivable
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
        if (Account::where('title', 'Accounts Receivable')->count() < 1) {
            return redirect('accounts/create')->with('status', 'Create Accounts Receivable account first!');
        }

        return $next($request);
    }
}

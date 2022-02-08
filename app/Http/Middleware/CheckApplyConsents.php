<?php

namespace App\Http\Middleware;

use Closure;
use Cookie;

class CheckApplyConsents
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
		if(!Cookie::get('apply-consents')){
			\Auth::logout();
		}
		return $next($request);
	}
}

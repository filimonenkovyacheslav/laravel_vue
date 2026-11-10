<?php

namespace App\Http\Middleware;

use Closure;
use View;
use BaseModel;

class CheckProfile
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
		View::share('is_admin', true);
		View::share('site_logo', BaseModel::getSiteLogoName(true));
        return $next($request);
    }
}

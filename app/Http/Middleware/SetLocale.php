<?php

namespace App\Http\Middleware;

use Closure;
use App;
use CustomLaravelLocalization;

class SetLocale
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
		if(config('app')['localization_type'] == 1) {
			CustomLaravelLocalization::setLocaleLL('en');
        } else {
            $locale = CustomLaravelLocalization::getCurrentLocale($request);
			App::setLocale($locale);
	        $request->session()->put('locale', $locale);
        }
        return $next($request);
    }
}

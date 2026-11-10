<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use BaseModel;
use CustomLaravelLocalization;
use Partner;
use Cookie;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
		$applyConsents = (int) (Cookie::get('apply-consents') !== null);
        Schema::defaultStringLength(191);
		    CustomLaravelLocalization::setLocaleLL();
		    View::share('is_admin', false);
        View::share('site_name', config('app.name'));
        View::share('site_logo', BaseModel::getSiteLogoName());
        View::share('sup_locales', CustomLaravelLocalization::getLocalesForDomain());
		    View::share('google_key', config('app')['google_key']);
        View::share('captcha', config('captcha'));
        View::share('partners', Partner::getAllForDomain(CustomLaravelLocalization::getDomainLocale()));
        View::share('apply_consents', $applyConsents);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

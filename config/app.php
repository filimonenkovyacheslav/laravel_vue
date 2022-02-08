<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'Medicaleer'),
    'email' => 'medicaleer@gmail.com', // KreftCeline@protonmail.com
    'copy_to' => 'info@medicaleer.com',

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services your application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Default Language Configuration
    |--------------------------------------------------------------------------
    |
    |
    |
    |
    |
    */

    'default_lang' => 37,

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

	/*
    |--------------------------------------------------------------------------
    | Localization Type
    |--------------------------------------------------------------------------
    | 1 - use link_prefixes
	| 2 - use domain zones
    */

	'localization_type' => 2,

	/*
    |--------------------------------------------------------------------------
    | Development Mode
    |--------------------------------------------------------------------------
    | true - use dev. prefixes for domains (if localization_type = 2)
	| false - use pure domains
    */

	'dev_mode' => false,

	/*
    |--------------------------------------------------------------------------
    | Use Elastic Search
    |--------------------------------------------------------------------------
    */

	'use_elastic_search' => false,

    /*
    |--------------------------------------------------------------------------
    | Google API Key
    |--------------------------------------------------------------------------
    */

    'google_key' => 'AIzaSyBJYHYlW72Cos3G_TVtqivxu_Zn08A4-Bw', //'AIzaSyArIgPnN3sPzz5EsemlLkiqM1hPhqJDcLI',
	
    /*
    |--------------------------------------------------------------------------
    | https://free.currencyconverterapi.com API Key
    |--------------------------------------------------------------------------
    */

    'currency_converter_api' => '748fff571d4a6175791e',

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        /*
         * Package Service Providers...
         */
        Mcamara\LaravelLocalization\LaravelLocalizationServiceProvider::class,
        'Barryvdh\TranslationManager\ManagerServiceProvider',
        Intervention\Image\ImageServiceProvider::class,
		Akaunting\Money\Provider::class,

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,

	Anhskohbo\NoCaptcha\NoCaptchaServiceProvider::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [

        'App' => Illuminate\Support\Facades\App::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Blade' => Illuminate\Support\Facades\Blade::class,
        'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
        'Bus' => Illuminate\Support\Facades\Bus::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'Cookie' => Illuminate\Support\Facades\Cookie::class,
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Eloquent' => Illuminate\Database\Eloquent\Model::class,
        'Event' => Illuminate\Support\Facades\Event::class,
        'File' => Illuminate\Support\Facades\File::class,
        'Gate' => Illuminate\Support\Facades\Gate::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Mail' => Illuminate\Support\Facades\Mail::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Queue' => Illuminate\Support\Facades\Queue::class,
        'Redirect' => Illuminate\Support\Facades\Redirect::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
        'Request' => Illuminate\Support\Facades\Request::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Schema' => Illuminate\Support\Facades\Schema::class,
        'Session' => Illuminate\Support\Facades\Session::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        'URL' => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View' => Illuminate\Support\Facades\View::class,
        'LaravelLocalization' => Mcamara\LaravelLocalization\Facades\LaravelLocalization::class,
		'Image' => Intervention\Image\Facades\Image::class,
		'Currency' => Akaunting\Money\Currency::class,
		'Money' => Akaunting\Money\Money::class,
		'NoCaptcha' => Anhskohbo\NoCaptcha\Facades\NoCaptcha::class,

		/* Custom Aliases */
        'CustomLaravelLocalization' => \App\Http\Plugins\CustomLaravelLocalization::class,
		'CurrencyConverter' => \App\Http\Plugins\CurrencyConverter::class,
		'Measure' => \App\Http\Plugins\Measure::class,
		'SearchHelper' => \App\Http\Plugins\SearchHelper::class,
		'ElasticSearchHelper' => \App\Http\Plugins\ElasticSearchHelper::class,
		'DbImporter' =>  \App\Http\Plugins\DbImporter::class,
     	'RollingCurl' =>  \App\Http\Plugins\RollingCurl::class,

		/* Models Aliases */
		//Ads
        'Ads' => \App\Http\Models\Ads\Ads::class,

        //'Professional' => \App\Http\Models\Profile\Professional::class,
        
        // Arts
        'Art' => \App\Http\Models\Arts\Art::class,
        'ArtLang' => \App\Http\Models\Arts\ArtLang::class,
        'ArtFavorite' => \App\Http\Models\Arts\ArtFavorite::class,
        'Artist' => \App\Http\Models\Profile\Artist::class,
        'Gallery' => \App\Http\Models\Profile\Gallery::class,
        'ArtCategory' => \App\Http\Models\Tags\ArtCategory::class,
    
        // Products
        'Product' => \App\Http\Models\Products\Product::class,
        'ProductLang' => \App\Http\Models\Products\ProductLang::class,
        'ProductFavorite' => \App\Http\Models\Products\ProductFavorite::class,
        'Seller' => \App\Http\Models\Profile\Seller::class,
        'ProductCategory' => \App\Http\Models\Tags\ProductCategory::class,

        // Wines
        'Wine' => \App\Http\Models\Wines\Wine::class,
        'WineLang' => \App\Http\Models\Wines\WineLang::class,
        'WineFavorite' => \App\Http\Models\Wines\WineFavorite::class,
        'Wineseller' => \App\Http\Models\Profile\Wineseller::class,
        'WineCategory' => \App\Http\Models\Tags\WineCategory::class,

	// Furnitures
        'Furniture' => \App\Http\Models\Furnitures\Furniture::class,
        'FurnitureLang' => \App\Http\Models\Furnitures\FurnitureLang::class,
        'FurnitureFavorite' => \App\Http\Models\Furnitures\FurnitureFavorite::class,
        'Furnitureseller' => \App\Http\Models\Profile\Furnitureseller::class,
        'FurnitureCategory' => \App\Http\Models\Tags\FurnitureCategory::class,

        // Goods
        'Good' => \App\Http\Models\Goods\Good::class,
        'GoodLang' => \App\Http\Models\Goods\GoodLang::class,
        'GoodFavorite' => \App\Http\Models\Goods\GoodFavorite::class,
        'Brand' => \App\Http\Models\Profile\Brand::class,
        'GoodCategory' => \App\Http\Models\Tags\GoodCategory::class,
   
        // Design
        'Design' => \App\Http\Models\Designs\Design::class,
        'DesignLang' => \App\Http\Models\Designs\DesignLang::class,
        'DesignFavorite' => \App\Http\Models\Designs\DesignFavorite::class,
        'DesignCategory' => \App\Http\Models\Tags\DesignCategory::class,

		// Property
		'BaseModel' => \App\Http\Models\BaseModel::class,
		'Property' => \App\Http\Models\Properties\Property::class,
		'PropertyPrice' => \App\Http\Models\Properties\PropertyPrice::class,
		'PropertyMeasures' => \App\Http\Models\Properties\PropertyMeasures::class,
		'PropertyFavorite' => \App\Http\Models\Properties\PropertyFavorite::class,
        'PropertyLang' => \App\Http\Models\Properties\PropertyLang::class,
        'PropertiesFloors' => App\Http\Models\Properties\PropertiesFloors::class,
        'PropertiesFloorsLang' => App\Http\Models\Properties\PropertiesFloorsLang::class,
        'PropertyCategory' => \App\Http\Models\Tags\PropertyCategory::class,

		// JobEntity
		'JobEntity' => \App\Http\Models\JobEntities\JobEntity::class,
		'JobEntityPrice' => \App\Http\Models\JobEntities\JobEntityPrice::class,
		'JobEntityFavorite' => \App\Http\Models\JobEntities\JobEntityFavorite::class,
        'JobEntityLang' => \App\Http\Models\JobEntities\JobEntityLang::class,

        // QuotesRequest
        'QuotesRequest' => \App\Http\Models\QuotesRequests\QuotesRequest::class,

        // MenuCategoryItem
        'MenuCategoryItem' => \App\Http\Models\MenuCategoryItem\MenuCategoryItem::class,

        // Franchise
        'Franchise' => \App\Http\Models\Franchises\Franchise::class,

        // News
        'News' => \App\Http\Models\News\News::class,
        'NewsLang' => \App\Http\Models\News\NewsLang::class,
        'NewsFavorite' => \App\Http\Models\News\NewsFavorite::class,

		// Upload
		'Upload' => \App\Http\Models\Uploads\Upload::class,
		'UploadProperty' => \App\Http\Models\Uploads\UploadProperty::class,
        'UploadUser' => \App\Http\Models\Uploads\UploadUser::class,
        'UploadFranchise' => \App\Http\Models\Uploads\UploadFranchise::class,
		'UploadProject' => \App\Http\Models\Uploads\UploadProject::class,
		'UploadJobEntity' => \App\Http\Models\Uploads\UploadJobEntity::class,
        'UploadsAds' => \App\Http\Models\Uploads\UploadsAds::class,
        'UploadArt' => \App\Http\Models\Uploads\UploadArt::class,
        'UploadProduct' => \App\Http\Models\Uploads\UploadProduct::class,
        'UploadWine' => \App\Http\Models\Uploads\UploadWine::class,
        'UploadNews' => \App\Http\Models\Uploads\UploadNews::class,
	'UploadFurniture' => \App\Http\Models\Uploads\UploadFurniture::class,
        'UploadGood' => \App\Http\Models\Uploads\UploadGood::class,
        'UploadDesign' => \App\Http\Models\Uploads\UploadDesign::class,

		// Profile
		'App\User' => \App\Http\Models\Profile\User::class,
		'User' => \App\Http\Models\Profile\User::class,
		'Role' => \App\Http\Models\Profile\Role::class,
		'SavedSearches' => \App\Http\Models\Profile\SavedSearches::class,
        'AgencyAgents' => \App\Http\Models\Profile\AgencyAgents::class,
		'Projects' => \App\Http\Models\Profile\Projects::class,

		// Agencies
		'Agency' => \App\Http\Models\Agencies\Agency::class,
		'ArchitectFirm'  => \App\Http\Models\Agencies\ArchitectFirm::class,
		'BuildingCompany'  => \App\Http\Models\Agencies\BuildingCompany::class,
		'ProjectHomeCompany'  => \App\Http\Models\Agencies\ProjectHomeCompany::class,
		'PropertyManagement'  => \App\Http\Models\Agencies\PropertyManagement::class,
		'VacationHomeCompany'  => \App\Http\Models\Agencies\VacationHomeCompany::class,
        'DesignCompany'  => \App\Http\Models\Agencies\DesignCompany::class,
  
		// Agents
		'Agent'=> \App\Http\Models\Agents\Agent::class,
		'Architect'  => \App\Http\Models\Agents\Architect::class,
		'BuildingCompanyAgent'  => \App\Http\Models\Agents\BuildingCompanyAgent::class,
		'ProjectHomeCompanyAgent'  => \App\Http\Models\Agents\ProjectHomeCompanyAgent::class,
		'Professional'  => \App\Http\Models\Agents\Professional::class,

		// Tags
		'Feature' => \App\Http\Models\Tags\Feature::class,
		'FeatureProperty' => \App\Http\Models\Tags\FeatureProperty::class,
		'Profession' => \App\Http\Models\Tags\Profession::class,
		'ProfessionUser' => \App\Http\Models\Tags\ProfessionUser::class,
		'JobCategory' => \App\Http\Models\Tags\JobCategory::class,
        'AddressKeyword' => \App\Http\Models\Tags\AddressKeyword::class,
        'ProductAddressKeyword' => \App\Http\Models\Tags\ProductAddressKeyword::class,        
        'WineAddressKeyword' => \App\Http\Models\Tags\WineAddressKeyword::class,
	'FurnitureAddressKeyword' => \App\Http\Models\Tags\FurnitureAddressKeyword::class,
        'GoodAddressKeyword' => \App\Http\Models\Tags\GoodAddressKeyword::class,
        'ArtAddressKeyword' => \App\Http\Models\Tags\ArtAddressKeyword::class,
        'DesignAddressKeyword' => \App\Http\Models\Tags\DesignAddressKeyword::class,
        'PropertyAddressKeyword' => \App\Http\Models\Tags\PropertyAddressKeyword::class,
        'UserAddressKeyword' => \App\Http\Models\Tags\UserAddressKeyword::class,
        'SimpleKeyword' => \App\Http\Models\Tags\SimpleKeyword::class,
        'NewsSimpleKeyword' => \App\Http\Models\Tags\NewsSimpleKeyword::class,

        //Emails
        'Email' => \App\Http\Models\Emails\Email::class,
        'EmailLog' => \App\Http\Models\Emails\EmailLog::class,
        'EmailTemplate' => \App\Http\Models\Emails\EmailTemplate::class,

        //Advertising
        'Partner' => \App\Http\Models\Advertising\Partner::class,
        'PartnerDomain' => \App\Http\Models\Advertising\PartnerDomain::class,
        'AdUser' => \App\Http\Models\Advertising\AdUser::class,
        'AdUserProfession' => \App\Http\Models\Advertising\AdUserProfession::class,

        //Settings
        'Setting' => \App\Http\Models\Settings\Setting::class,
        'Country' => \App\Http\Models\Settings\Country::class,
        'Rate' => \App\Http\Models\Settings\Rate::class,
        'Page' => \App\Http\Models\Settings\Page::class,
        'Quote' => \App\Http\Models\Settings\Quote::class,

        //Parsers
        'Parser' => \App\Http\Models\Parsers\Parser::class,
        'ParserLog' => \App\Http\Models\Parsers\ParserLog::class,
        'ParserResult' => \App\Http\Models\Parsers\ParserResult::class,
        'BaseParser' => \App\Http\Models\Parsers\BaseParser::class,
        'YelpParser' => \App\Http\Models\Parsers\YelpParser::class,
        'ZezoomParser' => \App\Http\Models\Parsers\ZezoomParser::class,
        'EngelvolkersParser' => \App\Http\Models\Parsers\EngelvolkersParser::class,
        'RaywhiteParser' => \App\Http\Models\Parsers\RaywhiteParser::class,
        'RaywhitecommParser' => \App\Http\Models\Parsers\RaywhitecommParser::class,
        'RaywhiteruralParser' => \App\Http\Models\Parsers\RaywhiteruralParser::class,
        'RealogyParser' => \App\Http\Models\Parsers\RealogyParser::class,
        'RealogyCorParser' => \App\Http\Models\Parsers\RealogyCorParser::class,
        'RealogyC21Parser' => \App\Http\Models\Parsers\RealogyC21Parser::class,
        'RealogyCBParser' => \App\Http\Models\Parsers\RealogyCBParser::class,

        //Import
        'ImportLink' => \App\Http\Models\Import\ImportLink::class,
        'ImportRun' => \App\Http\Models\Import\ImportRun::class,
        'ImportId' => \App\Http\Models\Import\ImportId::class,
        'ImportLog' => \App\Http\Models\Import\ImportLog::class,
    ],

];

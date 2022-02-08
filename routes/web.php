<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use Illuminate\Http\Request;

$groupParams = ['middleware' => ['setLocale', 'checkApplyConsents']];

if(config('app')['localization_type'] == 1) {
	$groupParams['prefix'] = CustomLaravelLocalization::setLocaleLL();
	$groupParams['middleware'] = ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath'] + $groupParams['middleware'];
}

// Vue Localization
Route::get('/js/lang.js', 'Controller@createLangJsFile')->name('assets.lang');

// Localized Routes
Route::group(
	$groupParams,
	function() {
		/** ADD ALL LOCALIZED ROUTES INSIDE THIS GROUP **/
		// Share global JS variables
		Route::get('/js/jsvars.js', 'Controller@createJsVarsFile')->name('assets.jsvars');
		// Authentication Routes
		Route::get('login', 'Auth\LoginController@showLoginForm')->name('login')->middleware('checkProfile');
		Route::post('login', 'Auth\LoginController@login');
		Route::post('logout', 'Auth\LoginController@logout')->name('logout');
		// Password Reset Routes
		Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
		Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
		Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
		Route::post('password/reset', 'Auth\ResetPasswordController@reset');
		// Registration Routes
		Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register')->middleware('checkProfile');
		Route::post('register', 'Auth\RegisterController@register');
		// Site routes
		Route::get('/', 'Controller@index')->name('home');
		Route::get('lang/{code}', 'Controller@setLang')->name('lang');
		// Pages
		Route::get('terms-and-privacy', 'Pages\PageController@getPageContent')->name('page.terms');
		Route::get('contact', 'Pages\PageController@getPageContent')->name('page.contact');
		Route::get('imprint', 'Pages\PageController@getPageContent')->name('page.imprint');
		Route::get('recommendations', 'Pages\PageController@getQuotesPage')->name('page.quotes');

		// Frontend Ads
		Route::get('/api/getAdsTypes/{id}', 'Ads\AdsController@getAdsTypes')->name('api.getAdsTypes.frontend');
		// Frontend News
        Route::get('news/{params?}', 'News\NewsController@getAllNews')->name('news.list.frontend');
        Route::get('one-news/{slug}', 'News\NewsController@getNewsBySlug')->name('news.view.frontend');
		// Frontend Properties
		Route::get('properties/{params?}', 'Properties\PropertyController@getAllProperties')->name('property.list.frontend');
		Route::get('property/{slug}', 'Properties\PropertyController@getPropertyBySlug')->name('property.view.frontend');
		// Frontend JobEntities
		Route::get('jobs/{params?}', 'JobEntities\JobEntityController@getAllJobEntities')->name('jobEntity.list.frontend');
		Route::get('job/{slug}', 'JobEntities\JobEntityController@getJobEntityBySlug')->name('jobEntity.view.frontend');
		// Frontend Arts
        //Route::get('professionals/{params?}', 'Arts\ArtController@getAllArts')->name('art.list.frontend');
        //Route::get('professionalwork/{slug}', 'Arts\ArtController@getArtBySlug')->name('art.view.frontend');
        // Frontend Luxury
        /*Route::get('marketplace/{params}', function($params){
            return Redirect::route('product.list.frontend', [$params]);
        })->name('marketplace.view.frontend');*/
        Route::get('products/{params?}', 'Products\ProductController@getAllProducts')->name('product.list.frontend');
        Route::get('product/{slug}', 'Products\ProductController@getProductBySlug')->name('product.view.frontend');
        // Frontend Wines
        Route::get('wines/{params?}', 'Wines\WineController@getAllWines')->name('wine.list.frontend');
        Route::get('wine/{slug}', 'Wines\WineController@getWineBySlug')->name('wine.view.frontend');
        // Frontend Furnitures
        Route::get('furnitures/{params?}', 'Furnitures\FurnitureController@getAllFurnitures')->name('furniture.list.frontend');
        Route::get('furniture/{slug}', 'Furnitures\FurnitureController@getFurnitureBySlug')->name('furniture.view.frontend');
        // Frontend Products
        Route::get('marketplace/{params?}', 'Goods\GoodController@getAllGoods')->name('good.list.frontend');
        Route::get('good/{slug}', 'Goods\GoodController@getGoodBySlug')->name('good.view.frontend');
		// Frontend Impressions
		Route::get('impressions/{params?}', 'Uploads\UploadController@getAllPropertyUploads')->name('impression.list.frontend');
		Route::get('impression/{id}', 'Uploads\UploadController@getPropertyUploadById')->name('impression.view.frontend');
		// Frontend Agencies
		Route::get('agencies/{params?}', 'Agencies\AgencyController@getAllUsers')->name('agency.list.frontend');
        Route::get('agency/{slug}', function($slug){
            return Redirect::route('user.view.frontend', [$slug]);
        })->name('agency.view.frontend');
        Route::get('projects/{params?}', 'Designs\DesignController@getAllDesigns')->name('design.list.frontend');
        Route::get('project/{slug}', 'Designs\DesignController@getDesignBySlug')->name('design.view.frontend');
		Route::get('architect-firms/{params?}', 'Agencies\ArchitectFirmController@getAllUsers')->name('architect_firm.list.frontend');
        Route::get('architect-firm/{slug}', function($slug){
            return Redirect::route('user.view.frontend', [$slug]);
        })->name('architect_firm.view.frontend');
        Route::get('design-company/{slug}', 'Agencies\DesignCompanyController@getUserBySlug')->name('design_company.view.frontend');
		Route::get('building-companies/{params?}', 'Agencies\BuildingCompanyController@getAllUsers')->name('building_company.list.frontend');
		Route::get('building-company/{slug}', 'Agencies\BuildingCompanyController@getUserBySlug')->name('building_company.view.frontend');
		Route::get('project-home-companies/{params?}', 'Agencies\ProjectHomeCompanyController@getAllUsers')->name('project_home_company.list.frontend');
		Route::get('project-home-company/{slug}', 'Agencies\ProjectHomeCompanyController@getUserBySlug')->name('project_home_company.view.frontend');
		Route::get('property-managements/{params?}', 'Agencies\PropertyManagementController@getAllUsers')->name('property_management.list.frontend');
		Route::get('property-management/{slug}', 'Agencies\PropertyManagementController@getUserBySlug')->name('property_management.view.frontend');
		Route::get('vacation-home-companies/{params?}', 'Agencies\VacationHomeCompanyController@getAllUsers')->name('vacation_home_company.list.frontend');
		Route::get('vacation-home-company/{slug}', 'Agencies\VacationHomeCompanyController@getUserBySlug')->name('vacation_home_company.view.frontend');
		// Frontend Agents
		Route::get('administrator/{slug}', 'Agents\AgentController@getUserBySlug')->name('administrator.view.frontend');
		Route::get('agents/{params?}', 'Agents\AgentController@getAllUsers')->name('agent.list.frontend');
        Route::get('agent/{slug}', function($slug){
            return Redirect::route('user.view.frontend', [$slug]);
        })->name('agent.view.frontend');
		Route::get('architects/{params?}', 'Agents\ArchitectController@getAllUsers')->name('architect.list.frontend');
        Route::get('architect/{slug}', function($slug){
            return Redirect::route('user.view.frontend', [$slug]);
        })->name('architect.view.frontend');
        Route::get('professional/{slug}', function($slug){
            return Redirect::route('user.view.frontend', [$slug]);
        })->name('professional.view.frontend');
        Route::get('gallery/{slug}', function($slug){
            return Redirect::route('user.view.frontend', [$slug]);
        })->name('gallery.view.frontend');
        Route::get('seller/{slug}', function($slug){
            return Redirect::route('user.view.frontend', [$slug]);
        })->name('seller.view.frontend');
        Route::get('wineseller/{slug}', function($slug){
            return Redirect::route('user.view.frontend', [$slug]);
        })->name('wineseller.view.frontend');
        Route::get('furnitureseller/{slug}', function($slug){
            return Redirect::route('user.view.frontend', [$slug]);
        })->name('furnitureseller.view.frontend');
        Route::get('brand/{slug}', function($slug){
            return Redirect::route('user.view.frontend', [$slug]);
        })->name('brand.view.frontend');
		Route::get('building-company-agents/{params?}', 'Agents\BuildingCompanyAgentController@getAllUsers')->name('building_company_agent.list.frontend');
		Route::get('building-company-agent/{slug}', 'Agents\BuildingCompanyAgentController@getUserBySlug')->name('building_company_agent.view.frontend');
		Route::get('professionals/{params?}', 'Agents\ProfessionalController@getAllUsers')->name('professional.list.frontend');
		Route::get('artists/{params?}', 'Agents\AgentController@getAllUsers')->name('artist.list.frontend');
		Route::get('brands/{params?}', 'Agents\BrandController@getAllUsers')->name('brand.list.frontend');
		//Route::get('professional/{slug}', 'Agents\ProfessionalController@getUserBySlug')->name('professional.view.frontend');
		Route::get('professional/{slug}', function($slug){
            return Redirect::route('user.view.frontend', [$slug]);
        })->name('professional.view.frontend');
		Route::get('project-home-company-agents/{params?}', 'Agents\ProjectHomeCompanyAgentController@getAllUsers')->name('project_home_company_agent.list.frontend');
		Route::get('project-home-company-agent/{slug}', 'Agents\ProjectHomeCompanyAgentController@getUserBySlug')->name('project_home_company_agent.view.frontend');
		// Additional
		Route::get('prices', 'Properties\PropertyController@updatePropertyPrices');
		Route::get('update-searches', 'Profile\UserController@updateAllSearches');
        
        Route::get('{slug}', 'Profile\UserController@getUserBySlug')->name('user.view.frontend');
        
		Route::group(['middleware' => 'checkProfile'], function(){
			// User Profile
			Route::get('admin/profile', 'Profile\UserController@getUserProfile')->name('user.profile.profile');
			Route::get('admin/profile/agents', 'Profile\UserController@getUserProfile')->name('user.profile.agents');
			Route::get('admin/profile/properties/{params?}', 'Profile\UserController@getUserProfile')->name('user.profile.properties');
            //Route::get('admin/profile/professional/{params?}', 'Profile\UserController@getUserProfile')->name('user.profile.arts');
			Route::get('admin/profile/jobEntities/{params?}', 'Profile\UserController@getUserProfile')->name('user.profile.jobEntities');
			Route::get('admin/profile/saved-searches', 'Profile\UserController@getUserProfile')->name('user.profile.saved_searches');
			Route::get('admin/profile/quotesRequests/{params?}', 'Profile\UserController@getUserProfile')->name('user.profile.quotesRequests');

			/*Route::get('admin/profile/db_importer', 'Profile\UserController@dbImporterView')->name('user.profile.db_importer');
			Route::get('admin/import/links', 'Import\ImportController@getImportLinks')->name('user.import.links');
			Route::get('admin/import/runs/{id?}', 'Import\ImportController@getImportRuns')->name('user.import.runs');
			Route::get('admin/import/log', 'Import\ImportController@getImportLog')->name('user.import.log');
			Route::get('admin/import/status/{id}/{status?}', 'Import\ImportController@setImportLinkStatus')->name('user.import.status');*/
			// Profile Pages
			Route::get('admin/agent/status/{id}/{status?}', 'Profile\UserController@setAgentStatus')->name('agent.status.admin');
			Route::get('admin/property/edit/{id?}', 'Properties\PropertyController@editProperty')->name('property.edit.admin');
			Route::get('admin/property/delete/{id}', 'Properties\PropertyController@deleteProperty')->name('property.delete.admin');
			Route::get('admin/property/unpublish/{id}', 'Properties\PropertyController@unpublishProperty')->name('property.unpublish.admin');
            Route::get('admin/art/edit/{id?}', 'Arts\ArtController@editArt')->name('art.edit.admin');
            Route::get('admin/art/delete/{id}', 'Arts\ArtController@deleteArt')->name('art.delete.admin');
            Route::get('admin/art/unpublish/{id}', 'Arts\ArtController@unpublishArt')->name('art.unpublish.admin');
			Route::get('admin/jobEntity/edit/{id?}', 'JobEntities\JobEntityController@editJobEntity')->name('jobEntity.edit.admin');
			Route::get('admin/jobEntity/delete/{id}', 'JobEntities\JobEntityController@deleteJobEntity')->name('jobEntity.delete.admin');
			Route::get('admin/saved-searches/delete/{id}', 'Profile\UserController@deleteUserSearch')->name('saved_searches.delete.admin');
			Route::get('admin/quotesRequest/edit/{id}', 'QuotesRequests\QuotesRequestController@editQuotesRequest')->name('quotesRequest.edit.admin');
			Route::get('admin/quotesRequest/delete/{id}', 'QuotesRequests\QuotesRequestController@deleteQuotesRequest')->name('quotesRequest.delete.admin');
            
            Route::get('admin/profile/products/{params?}', 'Profile\UserController@getUserProfile')->name('user.profile.products');
            Route::get('admin/product/edit/{id?}', 'Products\ProductController@editProduct')->name('product.edit.admin');
            Route::get('admin/product/delete/{id}', 'Products\ProductController@deleteProduct')->name('product.delete.admin');
            Route::get('admin/product/clone/{id}', 'Products\ProductController@cloneProduct')->name('product.clone.admin');
            Route::get('admin/product/unpublish/{id}', 'Products\ProductController@unpublishProduct')->name('product.unpublish.admin');

            Route::get('admin/profile/wines/{params?}', 'Profile\UserController@getUserProfile')->name('user.profile.wines');
            Route::get('admin/wine/edit/{id?}', 'Wines\WineController@editWine')->name('wine.edit.admin');
            Route::get('admin/wine/delete/{id}', 'Wines\WineController@deleteWine')->name('wine.delete.admin');
            Route::get('admin/wine/clone/{id}', 'Wines\WineController@cloneWine')->name('wine.clone.admin');
            Route::get('admin/wine/unpublish/{id}', 'Wines\WineController@unpublishWine')->name('wine.unpublish.admin');

            Route::get('admin/profile/news/{params?}', 'Profile\UserController@getUserProfile')->name('user.profile.news');
            Route::get('admin/news/edit/{id?}', 'News\NewsController@editNews')->name('news.edit.admin');
            Route::get('admin/news/delete/{id}', 'News\NewsController@deleteNews')->name('news.delete.admin');
            Route::get('admin/news/unpublish/{id}', 'News\NewsController@unpublishNews')->name('news.unpublish.admin');

            Route::get('admin/profile/furnitures/{params?}', 'Profile\UserController@getUserProfile')->name('user.profile.furnitures');
            Route::get('admin/furniture/edit/{id?}', 'Furnitures\FurnitureController@editFurniture')->name('furniture.edit.admin');
            Route::get('admin/furniture/delete/{id}', 'Furnitures\FurnitureController@deleteFurniture')->name('furniture.delete.admin');
            Route::get('admin/furniture/clone/{id}', 'Furnitures\FurnitureController@cloneFurniture')->name('furniture.clone.admin');
            Route::get('admin/furniture/unpublish/{id}', 'Furnitures\FurnitureController@unpublishFurniture')->name('furniture.unpublish.admin');

            Route::get('admin/profile/goods/{params?}', 'Profile\UserController@getUserProfile')->name('user.profile.goods');
            Route::get('admin/good/edit/{id?}', 'Goods\GoodController@editGood')->name('good.edit.admin');
            Route::get('admin/good/delete/{id}', 'Goods\GoodController@deleteGood')->name('good.delete.admin');
            Route::get('admin/good/clone/{id}', 'Goods\GoodController@cloneGood')->name('good.clone.admin');
            Route::get('admin/good/unpublish/{id}', 'Goods\GoodController@unpublishGood')->name('good.unpublish.admin');
            
            Route::get('admin/profile/designs/{params?}', 'Profile\UserController@getUserProfile')->name('user.profile.designs');
            Route::get('admin/design/edit/{id?}', 'Designs\DesignController@editDesign')->name('design.edit.admin');
            Route::get('admin/design/delete/{id}', 'Designs\DesignController@deleteDesign')->name('design.delete.admin');
            Route::get('admin/design/unpublish/{id}', 'Designs\DesignController@unpublishDesign')->name('design.unpublish.admin');
		});
		Route::group(['middleware' => ['admin', 'checkProfile']], function(){
			// Admin Pages
			Route::get('admin/profile/features', 'Profile\UserController@getUserProfile')->name('user.profile.features');
			Route::get('admin/profile/professions', 'Profile\UserController@getUserProfile')->name('user.profile.professions');
			Route::get('admin/profile/jobCategories', 'Profile\UserController@getUserProfile')->name('user.profile.jobCategories');
			Route::get('admin/profile/users/{params?}', 'Profile\UserController@getUserProfile')->name('user.profile.users');
			Route::get('admin/profile/parsers', 'Parsers\ParserController@getAllParsers')->name('admin.profile.parsers');
			Route::get('admin/emails/settings', 'Emails\EmailController@getSettings')->name('admin.emails.settings');
			Route::get('admin/emails/log', 'Emails\EmailController@getLog')->name('admin.emails.log');
			Route::get('admin/emails/template/{params?}', 'Emails\EmailController@getTemplate')->name('admin.emails.template');
			Route::get('admin/feature/edit/{id?}', 'Tags\FeatureController@featureEditAdmin')->name('feature.edit.admin');
			Route::get('admin/feature/delete/{id}', 'Tags\FeatureController@featureDeleteAdmin')->name('feature.delete.admin');
			Route::get('admin/profession/edit/{id?}', 'Tags\ProfessionController@professionEditAdmin')->name('profession.edit.admin');
			Route::get('admin/profession/delete/{id}', 'Tags\ProfessionController@professionDeleteAdmin')->name('profession.delete.admin');
			Route::get('admin/user/status/{id}/{status?}', 'Profile\UserController@setUserStatus')->name('user.status.admin');
			Route::get('admin/user/edit/{id}', 'Profile\UserController@getUserProfile')->name('user.edit.admin');
			Route::get('admin/user/label/{id}/{label?}', 'Profile\UserController@setUserLabel')->name('user.label.admin');
			Route::get('admin/jobCategory/edit/{id?}', 'Tags\JobCategoryController@jobCategoryEditAdmin')->name('jobCategory.edit.admin');
			Route::get('admin/jobCategory/delete/{id}', 'Tags\JobCategoryController@jobCategoryDeleteAdmin')->name('jobCategory.delete.admin');
			Route::get('admin/property/status/{id}/{status?}', 'Properties\PropertyController@setPropertyStatus')->name('property.status.admin');
			Route::get('admin/property/label/{id}/{label?}', 'Properties\PropertyController@setPropertyLabel')->name('property.label.admin');
			Route::get('admin/jobEntity/status/{id}/{status?}', 'JobEntities\JobEntityController@setJobEntityStatus')->name('jobEntity.status.admin');
			Route::get('admin/jobEntity/label/{id}/{label?}', 'JobEntities\JobEntityController@setJobEntityLabel')->name('jobEntity.label.admin');
			Route::get('admin/profile/pages', 'Pages\PageController@getAllPages')->name('admin.profile.pages');
			Route::get('admin/profile/quotes', 'Pages\PageController@getAllQuotes')->name('admin.profile.quotes');

			Route::get('admin/page/edit/{name}/{lang?}', 'Pages\PageController@editPage')->name('admin.edit.page');
			Route::get('admin/home/edit', 'Pages\PageController@editHome')->name('admin.edit.home');
			Route::get('admin/footer/edit', 'Pages\PageController@editFooter')->name('admin.edit.footer');
			Route::get('admin/ad/partners', 'Advertising\PartnerController@getAllPartners')->name('admin.ad.partners');
			Route::get('admin/partner/edit/{id}', 'Advertising\PartnerController@editPartner')->name('admin.edit.partner');
			Route::get('admin/ad/users', 'Advertising\AdUserController@getAdUsers')->name('admin.ad.users');
			Route::get('admin/ad_user/edit/{id}', 'Advertising\AdUserController@editAdUser')->name('admin.edit.ad_user');

			Route::get('admin/profile/propertyCategories', 'Profile\UserController@getUserProfile')->name('user.profile.propertyCategories');
            Route::get('admin/propertyCategory/edit/{id?}', 'Tags\PropertyCategoryController@categoryEditAdmin')->name('propertyCategory.edit.admin');
            Route::get('admin/propertyCategory/delete/{id}', 'Tags\PropertyCategoryController@categoryDeleteAdmin')->name('propertyCategory.delete.admin');
            
            Route::get('admin/profile/ads/{params?}', 'Profile\UserController@getUserProfile')->name('user.profile.ads');
            Route::get('admin/ads/edit/{id?}', 'Ads\AdsController@editAds')->name('ads.edit.admin');
            Route::get('admin/ads/delete/{id}', 'Ads\AdsController@deleteAds')->name('ads.delete.admin');
            Route::get('admin/ads/{id}/{status}', 'Ads\AdsController@setAdsStatus')->name('ads.status.admin');
            Route::get('admin/ads/{id}/6', 'Ads\AdsController@setAdsStatus')->name('ads.unpublish.admin');
            
            Route::get('admin/profile/productCategories', 'Profile\UserController@getUserProfile')->name('user.profile.productCategories');
            Route::get('admin/productCategory/edit/{id?}', 'Tags\ProductCategoryController@categoryEditAdmin')->name('productCategory.edit.admin');
            Route::get('admin/productCategory/delete/{id}', 'Tags\ProductCategoryController@categoryDeleteAdmin')->name('productCategory.delete.admin');
            Route::get('admin/product/status/{id}/{status?}', 'Products\ProductController@setProductStatus')->name('product.status.admin');
            Route::get('admin/product/label/{id}/{label?}', 'Products\ProductController@setProductLabel')->name('product.label.admin');
            Route::get('admin/productCategory/status/{id}/{status}', 'Tags\ProductCategoryController@setCategoryStatus')->name('productCategory.status.admin');

            Route::get('admin/profile/wineCategories', 'Profile\UserController@getUserProfile')->name('user.profile.wineCategories');
            Route::get('admin/wineCategory/edit/{id?}', 'Tags\WineCategoryController@categoryEditAdmin')->name('wineCategory.edit.admin');
            Route::get('admin/wineCategory/delete/{id}', 'Tags\WineCategoryController@categoryDeleteAdmin')->name('wineCategory.delete.admin');
            Route::get('admin/wine/status/{id}/{status?}', 'Wines\WineController@setWineStatus')->name('wine.status.admin');
            Route::get('admin/wine/label/{id}/{label?}', 'Wines\WineController@setWineLabel')->name('wine.label.admin');
            Route::get('admin/wineCategory/status/{id}/{status}', 'Tags\WineCategoryController@setCategoryStatus')->name('wineCategory.status.admin');

            Route::get('admin/news/status/{id}/{status?}', 'News\NewsController@setNewsStatus')->name('news.status.admin');
            Route::get('admin/news/label/{id}/{label?}', 'News\NewsController@setNewsLabel')->name('news.label.admin');

            Route::get('admin/profile/furnitureCategories', 'Profile\UserController@getUserProfile')->name('user.profile.furnitureCategories');
            Route::get('admin/furnitureCategory/edit/{id?}', 'Tags\FurnitureCategoryController@categoryEditAdmin')->name('furnitureCategory.edit.admin');
            Route::get('admin/furnitureCategory/delete/{id}', 'Tags\FurnitureCategoryController@categoryDeleteAdmin')->name('furnitureCategory.delete.admin');
            Route::get('admin/furniture/status/{id}/{status?}', 'Furnitures\FurnitureController@setFurnitureStatus')->name('furniture.status.admin');
            Route::get('admin/furniture/label/{id}/{label?}', 'Furnitures\FurnitureController@setFurnitureLabel')->name('furniture.label.admin');
            Route::get('admin/furnitureCategory/status/{id}/{status}', 'Tags\FurnitureCategoryController@setCategoryStatus')->name('furnitureCategory.status.admin');

            Route::get('admin/profile/goodCategories', 'Profile\UserController@getUserProfile')->name('user.profile.goodCategories');
            Route::get('admin/goodCategory/edit/{id?}', 'Tags\GoodCategoryController@categoryEditAdmin')->name('goodCategory.edit.admin');
            Route::get('admin/goodCategory/delete/{id}', 'Tags\GoodCategoryController@categoryDeleteAdmin')->name('goodCategory.delete.admin');
            Route::get('admin/good/status/{id}/{status?}', 'Goods\GoodController@setGoodStatus')->name('good.status.admin');
            Route::get('admin/good/label/{id}/{label?}', 'Goods\GoodController@setGoodLabel')->name('good.label.admin');
            Route::get('admin/goodCategory/status/{id}/{status}', 'Tags\GoodCategoryController@setCategoryStatus')->name('goodCategory.status.admin');
            
            //Route::get('admin/profile/professionalCategories', 'Profile\UserController@getUserProfile')->name('user.profile.artCategories');
            Route::get('admin/artCategory/edit/{id?}', 'Tags\ArtCategoryController@categoryEditAdmin')->name('artCategory.edit.admin');
            Route::get('admin/artCategory/delete/{id}', 'Tags\ArtCategoryController@categoryDeleteAdmin')->name('artCategory.delete.admin');
            Route::get('admin/art/status/{id}/{status?}', 'Arts\ArtController@setArtStatus')->name('art.status.admin');
            Route::get('admin/art/label/{id}/{label?}', 'Arts\ArtController@setArtLabel')->name('art.label.admin');
            
            Route::get('admin/profile/designCategories', 'Profile\UserController@getUserProfile')->name('user.profile.designCategories');
            Route::get('admin/designCategory/edit/{id?}', 'Tags\DesignCategoryController@categoryEditAdmin')->name('designCategory.edit.admin');
            Route::get('admin/designCategory/delete/{id}', 'Tags\DesignCategoryController@categoryDeleteAdmin')->name('designCategory.delete.admin');
            Route::get('admin/design/status/{id}/{status?}', 'Designs\DesignController@setDesignStatus')->name('design.status.admin');
            Route::get('admin/design/label/{id}/{label?}', 'Designs\DesignController@setDesignLabel')->name('design.label.admin');
		});
	}
);

Route::group(['middleware' => 'setLocale'], function(){
	// Click Methods
	Route::post('/apply-consents', 'Profile\UserController@applyConsents');
	Route::post('/save-jobEntity', 'JobEntities\JobEntityController@saveJobEntity')->name('jobEntity.save.admin');
	Route::post('/toggle-favorite-jobEntity', 'JobEntities\JobEntityController@toggleFavoriteJobEntity');
	Route::post('/api/jobEntity/{id?}', 'JobEntities\JobEntityController@_getJobEntity')->name('api.jobEntity.edit.admin');
	Route::post('/save-quotesRequest', 'QuotesRequests\QuotesRequestController@saveQuotesRequest')->name('quotesRequest.save.admin');
	Route::post('/api/quotesRequest/{id?}', 'QuotesRequests\QuotesRequestController@_getQuotesRequest')->name('api.quotesRequest.edit.admin');
	Route::post('/search-quote', 'Pages\PageController@searchQuotes');
	// Form Methods
	Route::post('/uploads-save/{type?}', 'Uploads\UploadController@store');
	Route::post('/uploads-delete', 'Uploads\UploadController@destroy');
	Route::post('/save-property', 'Properties\PropertyController@saveProperty')->name('property.save.admin');
    Route::post('/save-art', 'Arts\ArtController@saveArt')->name('art.save.admin');
	Route::post('/toggle-favorite-property', 'Properties\PropertyController@toggleFavoriteProperty');
    Route::post('/toggle-favorite-art', 'Arts\ArtController@toggleFavoriteArt');
	Route::post('/save-profile', 'Profile\UserController@saveUserProfile')->name('user.profile.save');
	Route::post('/delete-profile', 'Profile\UserController@deleteUserProfile');
	Route::post('/reset-password', 'Profile\UserController@resetUserPassword');
	Route::post('/save-search-result', 'Profile\UserController@saveUserSearch');
	Route::post('/agent-send-message','Emails\EmailController@sendEmailToAgent');
	Route::post('/elasticsearch-results', 'Controller@getElasticSearchResults');
	Route::post('/search-user', 'Profile\UserController@searchUsers');
    Route::post('/search-country', 'Properties\PropertyController@searchCountries');
    Route::post('/search-location', 'Properties\PropertyController@searchLocations');
	Route::post('/api/property/{id?}', 'Properties\PropertyController@_getProperty')->name('api.property.edit.admin');
    Route::post('/api/art/{id?}', 'Arts\ArtController@_getArt')->name('api.art.edit.admin');
    Route::post('/api/ads/{id?}', 'Ads\AdsController@_getAds')->name('api.ads.edit.admin');
	Route::post('/contact-send-message','Emails\EmailController@sendContactEmail');
	//Route::post('/add-import-link', 'Import\ImportController@addImportLink');
	Route::post('/send-quote','Emails\EmailController@sendQuoteEmail');

	Route::post('/api/propertyCategories', 'Tags\PropertyCategoryController@_getPropertyCategoryFields')->name('api.propertyCategories.edit.admin');
    
    Route::post('/save-product', 'Products\ProductController@saveProduct')->name('product.save.admin');
    Route::post('/toggle-favorite-product', 'Products\ProductController@toggleFavorite');
    Route::post('/api/product/{id?}', 'Products\ProductController@_getProduct')->name('api.product.edit.admin');

    Route::post('/save-wine', 'Wines\WineController@saveWine')->name('wine.save.admin');
    Route::post('/toggle-favorite-wine', 'Wines\WineController@toggleFavorite');
    Route::post('/api/wine/{id?}', 'Wines\WineController@_getWine')->name('api.wine.edit.admin');

    Route::post('/save-news', 'News\NewsController@saveNews')->name('news.save.admin');
    Route::post('/save-news-upload-item', 'News\NewsController@saveNewsUploadItem')->name('news.upload.item.save.admin');
    Route::post('/toggle-favorite-news', 'News\NewsController@toggleFavorite');
    Route::post('/api/news/{id?}', 'News\NewsController@_getNews')->name('api.news.edit.admin');

    Route::post('/save-furniture', 'Furnitures\FurnitureController@saveFurniture')->name('furniture.save.admin');
    Route::post('/toggle-favorite-furniture', 'Furnitures\FurnitureController@toggleFavorite');
    Route::post('/api/furniture/{id?}', 'Furnitures\FurnitureController@_getFurniture')->name('api.furniture.edit.admin');

    Route::post('/save-good', 'Goods\GoodController@saveGood')->name('good.save.admin');
    Route::post('/toggle-favorite-good', 'Goods\GoodController@toggleFavorite');
    Route::post('/api/good/{id?}', 'Goods\GoodController@_getGood')->name('api.good.edit.admin');
    
    Route::post('/api/productCategories', 'Tags\ProductCategoryController@_getProductCategoryFields')->name('api.productCategories.edit.admin');
    Route::post('/api/wineCategories', 'Tags\WineCategoryController@_getWineCategoryFields')->name('api.wineCategories.edit.admin');
    Route::post('/api/furnitureCategories', 'Tags\FurnitureCategoryController@_getFurnitureCategoryFields')->name('api.furnitureCategories.edit.admin');
    Route::post('/api/goodCategories', 'Tags\GoodCategoryController@_getGoodCategoryFields')->name('api.goodCategories.edit.admin');
    Route::post('/api/artCategories', 'Tags\ArtCategoryController@_getArtCategoryFields')->name('api.artCategories.edit.admin');
    Route::post('/api/adsCategories', 'Ads\AdsController@_getAdsCategoryFields')->name('api.adsCategories.edit.admin');
    
    Route::post('/save-design', 'Designs\DesignController@saveDesign')->name('design.save.admin');
    Route::post('/toggle-favorite-design', 'Designs\DesignController@toggleFavorite');
    Route::post('/api/design/{id?}', 'Designs\DesignController@_getDesign')->name('api.design.edit.admin');
    Route::post('/api/designCategories', 'Tags\DesignCategoryController@_getDesignCategoryFields')->name('api.designCategories.edit.admin');

    Route::post('/search-address-keyword', 'Tags\AddressKeywordController@searchKeywords');
    Route::post('/search-simple-keyword', 'Tags\SimpleKeywordController@searchKeywords');
});
Route::group(['middleware' => ['setLocale', 'admin']], function(){
	// Admin Pages
	Route::post('/save-jobCategory', 'Tags\JobCategoryController@jobCategorySave')->name('jobCategory.save.admin');
	Route::post('/save-user-projects', 'Profile\UserController@saveUserProjects');
	Route::post('/save-jobEntity-settings', 'JobEntities\JobEntityController@saveJobSettings')->name('jobEntity.save.admin');
	Route::post('/save-profession', 'Tags\ProfessionController@professionSave')->name('profession.save.admin');
	Route::post('/save-feature', 'Tags\FeatureController@featureSave')->name('feature.save.admin');
	Route::post('/save-email-settings', 'Emails\EmailController@saveEmailSettings');
	Route::post('/save-email-template', 'Emails\EmailController@saveEmailTemplate');
	Route::post('/bulk-delete-properties', 'Properties\PropertyController@bulkDeleteProperties');
	Route::post('/bulk-edit-properties', 'Properties\PropertyController@bulkEditProperties');
	Route::post('/bulk-label-properties', 'Properties\PropertyController@bulkLabelProperties');
    Route::post('/bulk-delete-arts', 'Arts\ArtController@bulkDeleteArts');
    Route::post('/bulk-edit-arts', 'Arts\ArtController@bulkEditArts');
    Route::post('/bulk-label-arts', 'Arts\ArtController@bulkLabelArts');
	Route::post('/bulk-delete-users', 'Profile\UserController@bulkDeleteUsers');
	Route::post('/bulk-edit-users', 'Profile\UserController@bulkEditUsers');
	Route::post('/bulk-label-users', 'Profile\UserController@bulkLabelUsers');
	Route::get('/clear-cache', 'Controller@clearCache');
	Route::post('/add-quote', 'Pages\PageController@addQuote');
	Route::get('/delete-quote/{id}', 'Pages\PageController@deleteQuote')->name('delete.quote.admin');
	//Route::post('/db-import', 'Profile\UserController@dbImporterImport');
	Route::post('/add-watermarks', 'Profile\UserController@addWatermarksToImages');
	Route::get('/start-parser/{id}', 'Parsers\ParserController@startParser')->name('start.parser.admin');
	Route::get('/stop-parser/{id}', 'Parsers\ParserController@stopParser')->name('stop.parser.admin');
	Route::post('/save-proxy-list', 'Parsers\ParserController@savePoxies')->name('save.proxies.admin');
	Route::post('/save-page-content', 'Pages\PageController@savePage')->name('save.page.admin');
	Route::post('/save-home', 'Pages\PageController@saveHome')->name('save.home.admin');
	Route::post('/save-footer', 'Pages\PageController@saveFooter')->name('save.footer.admin');
	Route::post('/save-partner', 'Advertising\PartnerController@savePartner')->name('save.partner.admin');
	Route::get('/delete-partner/{id}', 'Advertising\PartnerController@deletePartner')->name('delete.partner.admin');
	Route::post('/save-ad-user', 'Advertising\AdUserController@saveAdUser')->name('save.ad_user.admin');
	Route::get('/delete-ad-user/{id}', 'Advertising\AdUserController@deleteAdUser')->name('delete.ad_user.admin');
	//Route::get('/run-import/{id?}', 'Import\ImportController@runImport')->name('run.import.admin');
    Route::post('/save-ads', 'Ads\AdsController@saveAds')->name('ads.save.admin');

    Route::post('/save-propertyCategory/{back?}', 'Tags\PropertyCategoryController@categorySave')->name('propertyCategory.save.admin');
    
    Route::post('/save-productCategory/{back?}', 'Tags\ProductCategoryController@categorySave')->name('productCategory.save.admin');
    Route::post('/bulk-edit-prod-cats', 'Tags\ProductCategoryController@bulkEditCategories');
    Route::post('/bulk-delete-products', 'Products\ProductController@bulkDeleteProducts');
    Route::post('/bulk-edit-products', 'Products\ProductController@bulkEditProducts');
    Route::post('/bulk-label-products', 'Products\ProductController@bulkLabelProducts');

    Route::post('/save-wineCategory/{back?}', 'Tags\WineCategoryController@categorySave')->name('wineCategory.save.admin');
    Route::post('/bulk-edit-wine-cats', 'Tags\WineCategoryController@bulkEditCategories');
    Route::post('/bulk-delete-wines', 'Wines\WineController@bulkDeleteWines');
    Route::post('/bulk-edit-wines', 'Wines\WineController@bulkEditWines');
    Route::post('/bulk-label-wines', 'Wines\WineController@bulkLabelWines');

    Route::post('/bulk-delete-news', 'News\NewsController@bulkDeleteNews');
    Route::post('/bulk-edit-news', 'News\NewsController@bulkEditNews');
    Route::post('/bulk-label-news', 'News\NewsController@bulkLabelNews');

    Route::post('/save-furnitureCategory/{back?}', 'Tags\FurnitureCategoryController@categorySave')->name('furnitureCategory.save.admin');
    Route::post('/bulk-edit-furniture-cats', 'Tags\FurnitureCategoryController@bulkEditCategories');
    Route::post('/bulk-delete-furnitures', 'Furnitures\FurnitureController@bulkDeleteFurnitures');
    Route::post('/bulk-edit-furnitures', 'Furnitures\FurnitureController@bulkEditFurnitures');
    Route::post('/bulk-label-furnitures', 'Furnitures\FurnitureController@bulkLabelFurnitures');

    Route::post('/save-goodCategory/{back?}', 'Tags\GoodCategoryController@categorySave')->name('goodCategory.save.admin');
    Route::post('/bulk-edit-good-cats', 'Tags\GoodCategoryController@bulkEditCategories');
    Route::post('/bulk-delete-goods', 'Goods\GoodController@bulkDeleteGoods');
    Route::post('/bulk-edit-goods', 'Goods\GoodController@bulkEditGoods');
    Route::post('/bulk-label-goods', 'Goods\GoodController@bulkLabelGoods');
    
    Route::post('/save-artCategory/{back?}', 'Tags\ArtCategoryController@categorySave')->name('artCategory.save.admin');
    
    Route::post('/save-designCategory/{back?}', 'Tags\DesignCategoryController@categorySave')->name('designCategory.save.admin');
    Route::post('/bulk-delete-designs', 'Designs\DesignController@bulkDeleteDesigns');
    Route::post('/bulk-edit-designs', 'Designs\DesignController@bulkEditDesigns');
    Route::post('/bulk-label-designs', 'Designs\DesignController@bulkLabelDesigns');

    Route::post('/save-address-keyword', 'Tags\AddressKeywordController@keywordSave')->name('akeyword.save.admin');
    Route::post('/save-simple-keyword', 'Tags\SimpleKeywordController@keywordSave')->name('skeyword.save.admin');
    Route::post('/override-keywords', 'Profile\UserController@overrideKeywords')->name('user.override.keywords');
});

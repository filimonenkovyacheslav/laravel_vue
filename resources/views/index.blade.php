@extends('layouts.app')

@section('meta_og')
    @if (isset($seo_tags['title']))
        <meta property="og:title" content="{{ $seo_tags['title'] }}"/>
    @endif
    @if (isset($seo_tags['description']))
        <meta property="og:description" content="{{ $seo_tags['description'] }}"/>
    @endif
    @if (isset($seo_tags['image']))
        <meta property="og:image" content="{{ $seo_tags['image'] }}"/>
        <meta property="og:image:width" content="968">
        <meta property="og:image:height" content="504">
    @endif
@endsection

@section('title')
	{{ isset($seo_tags['title']) ? $seo_tags['title'] : '' }}
@endsection

@section('css')
	@switch($route_name)
		@case('property.edit.admin')
		@case('jobEntity.edit.admin')
        @case('product.edit.admin')
        @case('wine.edit.admin')
        @case('news.edit.admin')
        @case('furniture.edit.admin')
        @case('good.edit.admin')
		@case('franchise.edit.admin')
		@case('user.profile.profile')
		@case('user.edit.admin')
        @case('design.edit.admin')
			<link rel="stylesheet" href="{{ url('/css/dropzone.css') }}">
			@break
		@default
			@break
	@endswitch

@endsection

@section('js_foot')
	@switch($route_name)
		@case('property.edit.admin')
		@case('jobEntity.edit.admin')
        @case('product.edit.admin')
        @case('wine.edit.admin')
        @case('news.edit.admin')
        @case('furniture.edit.admin')
        @case('good.edit.admin')
		@case('franchise.edit.admin')
		@case('user.profile.profile')
		@case('user.edit.admin')
        @case('design.edit.admin')
			<script src="{{ url('/js/dropzone.js') }}"></script>
			@break
		@default
			@break
	@endswitch
@endsection

@section('body_class')
    @if($route_name == 'home' || $route_name == 'page.quotes')home-page
    @endif
@endsection

@section('content')
	{{--@include('partials.top-bar')--}}

    @if(app('request')->input('es'))
        @include('partials.headeres')
    @else
	    @include('partials.header')
    @endif

  @if(!$is_admin)
		@if($route_name == 'home' || $route_name == 'page.quotes')
            <header-media :params="{{ $params }}"></header-media>
    	@else
            @switch($route_name)
                @case('artist.view.frontend')
                @case('gallery.view.frontend')               
                <search-bar :params="{{ $params }}" :is_artist="1"></search-bar>
                @break
                @case('seller.view.frontend')
                <search-bar :params="{{ $params }}" :is_seller="1"></search-bar>
                @break
                @case('wineseller.view.frontend')
                <search-bar :params="{{ $params }}" :is_wineseller="1"></search-bar>
                @break
                @case('furnitureseller.view.frontend')
                <search-bar :params="{{ $params }}" :is_furnitureseller="1"></search-bar>
                @break
                @case('brand.view.frontend')
                <search-bar :params="{{ $params }}" :is_brand="1"></search-bar>
                @break
                @case('news.view.frontend')
                <search-bar :params="{{ $params }}"></search-bar>
                @break
                @default
                <search-bar :params="{{ $params }}"></search-bar>
                @break
            @endswitch
    	@endif
	@endif


	@if (session('message'))
		<message-bar :message="{{ session('message') }}"></message-bar>
	@endif

	@if (session('errors'))
		<message-bar :errors="{{ session('errors') }}"></message-bar>
	@endif

	@switch($route_name)
		@case('home')
        @case('page.quotes')
          {{--<index-component :params="{{ $params }}"></index-component>--}}
          @break

		@case('user.profile.profile')
		@case('user.profile.agents')
		@case('user.profile.properties')
        @case('user.profile.propertyCategories')
		@case('user.profile.franchises')
		@case('user.profile.features')
        @case('user.profile.quotesRequests')
		@case('user.profile.jobEntities')
		@case('user.profile.professions')
		@case('user.profile.jobCategories')
		@case('user.profile.users')
		@case('user.profile.saved_searches')
        @case('user.profile.ads')
		@case('admin.emails.settings')
		@case('admin.emails.template')
		@case('admin.emails.log')
		@case('admin.ad.partners')
		@case('admin.edit.partner')
		@case('admin.ad.users')
        @case('admin.edit.ad_user')
		@case('admin.profile.pages')
		@case('admin.edit.page')
		@case('admin.edit.home')
        @case('admin.edit.footer')
		@case('admin.edit.jobEntity')
		@case('admin.profile.parsers')
		@case('user.profile.db_importer')
		@case('user.import.links')
		@case('user.import.runs')
		@case('user.import.log')
        @case('user.profile.products')
        @case('user.profile.productCategories')
        @case('user.profile.wines')
        @case('user.profile.wineCategories')
        @case('user.profile.news')
        @case('user.profile.furnitures')
        @case('user.profile.furnitureCategories')
        @case('user.profile.goods')
        @case('user.profile.goodCategories')
        @case('user.profile.designs')
        @case('user.profile.designCategories')
        @case('admin.profile.quotes')
			<user-profile :params="{{ $params }}"></user-profile>
			@break

		@case('agency.list.frontend')
		@case('architect_firm.list.frontend')
		@case('building_company.list.frontend')
        @case('design_company.list.frontend')
		@case('project_home_company.list.frontend')
		@case('property_management.list.frontend')
		@case('vacation_home_company.list.frontend')
		@case('professional.list.frontend')
        @case('brand.list.frontend')
            <agency-list-frontend :params="{{ $params }}"></agency-list-frontend>
            @break

        @case('agent.list.frontend')
        @case('architect.list.frontend')
        @case('building_company_agent.list.frontend')
        @case('project_home_company_agent.list.frontend')
        	<agency-list-frontend :params="{{ $params }}"></agency-list-frontend>
            @break

        @case('agency.view.frontend')
		@case('architect_firm.view.frontend')
		@case('building_company.view.frontend')
		@case('project_home_company.view.frontend')
		@case('property_management.view.frontend')
		@case('vacation_home_company.view.frontend')
        @case('design_company.view.frontend')
            <agency-view-frontend :params="{{ $params }}" :captcha="{{ json_encode($captcha) }}"></agency-view-frontend>
            @break

        @case('administrator.view.frontend')
        @case('agent.view.frontend')
        @case('architect.view.frontend')
        @case('building_company_agent.view.frontend')
        @case('professional.view.frontend')
        @case('project_home_company_agent.view.frontend')
        	<agency-view-frontend :params="{{ $params }}" :captcha="{{ json_encode($captcha) }}"></agency-view-frontend>
            @break
        @case('artist.view.frontend')
        @case('gallery.view.frontend')
            <artist-view-frontend :params="{{ $params }}" :captcha="{{ json_encode($captcha) }}"></artist-view-frontend>
            @break
		@case('property.list.frontend')
			<property-list-frontend :params="{{ $params }}"></property-list-frontend>
			@break
		@case('property.view.frontend')
			<property-view-frontend :params="{{ $params }}" :captcha="{{ json_encode($captcha) }}"></property-view-frontend>
			@break
        @case('ads.edit.admin')
            <ads-edit-admin :params="{{ $params }}"></ads-edit-admin>
            @break
		@case('impression.list.frontend')
			<impression-list-frontend :params="{{ $params }}"></impression-list-frontend>
			@break
		@case('property.edit.admin')
			<property-edit-admin :params="{{ $params }}"></property-edit-admin>
			@break
		@case('franchise.edit.admin')
			<franchise-edit-admin :params="{{ $params }}"></franchise-edit-admin>
			@break

		@case('feature.edit.admin')
			<feature-edit-admin :params="{{ $params }}"></feature-edit-admin>
			@break
		@case('jobCategory.edit.admin')
			<jobcategory-edit-admin :params="{{ $params }}"></jobcategory-edit-admin>
			@break
		@case('profession.edit.admin')
            <profession-edit-admin :params="{{ $params }}"></profession-edit-admin>
            @break
        
        @case('seller.view.frontend')
            <seller-view-frontend :params="{{ $params }}" :captcha="{{ json_encode($captcha) }}"></seller-view-frontend>
            @break
        @case('wineseller.view.frontend')
            <wineseller-view-frontend :params="{{ $params }}" :captcha="{{ json_encode($captcha) }}"></wineseller-view-frontend>
            @break
        @case('furnitureseller.view.frontend')
            <furnitureseller-view-frontend :params="{{ $params }}" :captcha="{{ json_encode($captcha) }}"></furnitureseller-view-frontend>
            @break
        @case('product.list.frontend')
            <product-list-frontend :params="{{ $params }}"></product-list-frontend>
            @break
        @case('product.view.frontend')
            <product-view-frontend :params="{{ $params }}" :captcha="{{ json_encode($captcha) }}"></product-view-frontend>
            @break
        @case('product.edit.admin')
            <product-edit-admin :params="{{ $params }}"></product-edit-admin>
            @break
        @case('productCategory.edit.admin')
            <productcategory-edit-admin :params="{{ $params }}"></productcategory-edit-admin>
            @break
        @case('wine.list.frontend')
            <wine-list-frontend :params="{{ $params }}"></wine-list-frontend>
            @break
        @case('wine.view.frontend')
            <wine-view-frontend :params="{{ $params }}" :captcha="{{ json_encode($captcha) }}"></wine-view-frontend>
            @break
        @case('wine.edit.admin')
            <wine-edit-admin :params="{{ $params }}"></wine-edit-admin>
            @break
        @case('wineCategory.edit.admin')
            <winecategory-edit-admin :params="{{ $params }}"></winecategory-edit-admin>
            @break
        @case('news.list.frontend')
            <news-list-frontend :params="{{ $params }}"></news-list-frontend>
            @break
        @case('news.view.frontend')
            <news-view-frontend :params="{{ $params }}"></news-view-frontend>
            @break
        @case('news.edit.admin')
            <news-edit-admin :params="{{ $params }}"></news-edit-admin>
            @break
        @case('furniture.list.frontend')
            <furniture-list-frontend :params="{{ $params }}"></furniture-list-frontend>
            @break
        @case('furniture.view.frontend')
            <furniture-view-frontend :params="{{ $params }}" :captcha="{{ json_encode($captcha) }}"></furniture-view-frontend>
            @break
        @case('furniture.edit.admin')
            <furniture-edit-admin :params="{{ $params }}"></furniture-edit-admin>
            @break
        @case('furnitureCategory.edit.admin')
            <furniturecategory-edit-admin :params="{{ $params }}"></furniturecategory-edit-admin>
            @break
        @case('brand.view.frontend')
            <brand-view-frontend :params="{{ $params }}" :captcha="{{ json_encode($captcha) }}"></brand-view-frontend>
            @break
        @case('good.list.frontend')
            <good-list-frontend :params="{{ $params }}"></good-list-frontend>
            @break
        @case('good.view.frontend')
            <good-view-frontend :params="{{ $params }}" :captcha="{{ json_encode($captcha) }}"></good-view-frontend>
            @break
        @case('good.edit.admin')
            <good-edit-admin :params="{{ $params }}"></good-edit-admin>
            @break      
        @case('goodCategory.edit.admin')
            <goodcategory-edit-admin :params="{{ $params }}"></goodcategory-edit-admin>
            @break
        @case('propertyCategory.edit.admin')
            <property-category-edit-admin :params="{{ $params }}"></property-category-edit-admin>
        @break
        
        @case('design.list.frontend')
            <design-list-frontend :params="{{ $params }}"></design-list-frontend>
        @break
        @case('design.view.frontend')
            <design-view-frontend :params="{{ $params }}" :captcha="{{ json_encode($captcha) }}"></design-view-frontend>
        @break
        @case('design.edit.admin')
            <design-edit-admin :params="{{ $params }}"></design-edit-admin>
        @break
        @case('designCategory.edit.admin')
            <design-category-edit-admin :params="{{ $params }}"></design-category-edit-admin>
        @break
        
        @case('user.edit.admin')
            <user-profile :params="{{ $params }}"></user-profile>
            @break
        @case('quotesRequest.edit.admin')
            <quotesRequest-edit-admin :params="{{ $params }}"></quotesRequest-edit-admin>
            @break

        @case('page.terms')
        @case('page.imprint')
        	@include('partials.content')
            @break
        @case('page.contact')
        	@include('partials.contact')
            @break

		@default
			<div class="container no-content">
				<div class="row">
					<div class="col-md-12 col-xs-12">
						<span>{{ __('missing_page') }}...</span>
					</div>
				</div>
			</div>
			@break
	@endswitch

    @if(!$is_admin)
        @if($route_name != 'home' && $route_name != 'page.quotes')
            <footer-media :params="{{ $params }}"></footer-media>
        @endif
    @endif

	{{--Form to logout action--}}
	<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    	@csrf
    </form>

	@switch($route_name)
		@case('page.terms')
			@break
		@default
            <consents-dialog :site_name="{{ json_encode($site_name) }}" :apply_consents="{{ $apply_consents }}"></consents-dialog>
            @break
	@endswitch

@endsection

@section('footer')
    @if(!$is_admin)
        @if(app('request')->input('es'))
            @include('partials.footeres')
        @else
            @include('partials.footer')
        @endif
    @endif
@endsection

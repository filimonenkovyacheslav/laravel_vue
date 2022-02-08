@php ($cur_locale = App::getLocale())
<header id="header-section" class="header-section {{ !empty($route_name) && $route_name == 'home' ? 'home-header' : '' }}">
	<nav class="navbar navbar-light {{ $cur_locale != 'en' ? 'navbar-expand-xxl' : 'navbar-expand-lg' }}">
		<a class="navbar-brand" href="{{ route('home') }}">
		<img src="{{ $site_logo }}" class="d-inline-block align-top" alt="logo" />
		</a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarHeaderContent" aria-controls="navbarHeaderContent" aria-expanded="false" aria-label="Toggle navigation">
		<i class="fa fa-bars" aria-hidden="true"></i>
		</button>
		<div class="collapse navbar-collapse" id="navbarHeaderContent">
			<ul class="navbar-nav ml-auto">
				<li class="nav-item">
					<a href="{{ route('home') }}" class="nav-link">{{ __('Home') }}</a>
				</li>
				<!-- <li class="nav-item">
					<a href="{{ route('property.list.frontend') }}" class="nav-link">{{ __('Properties') }}</a>
				</li> -->
                <!-- <li class="nav-item">
                    <a href="{{ route('design.list.frontend') }}" class="nav-link">{{ __('Architecture & Design') }}</a>
                </li> -->
                <li class="nav-item">
                    <a href="{{ route('professional.list.frontend') }}" class="nav-link">{{ __('Professionals') }}</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('brand.list.frontend') }}" class="nav-link">{{ __('Brands') }}</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('product.list.frontend') }}" class="nav-link">{{ __('Products') }}</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('good.list.frontend') }}" class="nav-link">{{ __('Marketplace') }}</a>
                </li>                
                <!-- <li class="nav-item">
                    <a href="{{ route('furniture.list.frontend') }}" class="nav-link">{{ __('Furniture') }}</a>
                </li> -->               
                <li class="nav-item">
                    <a href="{{ route('news.list.frontend') }}" class="nav-link">{{ __('News') }}</a>
                </li>
                <!-- <li class="nav-item">
                    <a href="{{ route('wine.list.frontend') }}" class="nav-link">{{ __('Wines') }}</a>
                </li> -->
			</ul>
            @guest
            @else
            <ul class="navbar-nav user-menu">
                <li class="nav-item dropdown">
                    <a href="#" class="user nav-link dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {{--<span class="user-alert" style="display: none;"></span>--}}
                        <i class="fa fa-user user-image" aria-hidden="true"></i>
                        <span class="user-name">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</span>
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="{{ route('user.profile.profile') }}"><i class="fa fa-user"></i><span>{{ __('My Profile') }}</span></a>
                        @php ($user_role = Auth::user()->role['name'])
                        @if (isset($agency_agents[$user_role]))
                        <a class="dropdown-item" href="{{ route('user.profile.agents') }}"><i class="fa fa-users"></i><span>{{ __('My') }} {{ $agency_agents[$user_role]['title'] }}</span></a>
                        @endif
                        
                        @if ($user_role == 'architect_firm' || $user_role == 'building_company')
                            <a class="dropdown-item" href="{{ route('user.profile.designs') }}"><i class="fa fa-file-image-o"></i><span>{{ __('My Designs') }}</span></a>
                            <a class="dropdown-item" href="{{ route('design.edit.admin') }}"><i class="fa fa-plus-circle"></i><span>{{ __('Add new A&D') }}</span></a>
                            <a class="dropdown-item" href="{{ route('user.profile.designs', ['favorite']) }}"><i class="fa fa-heart"></i><span>{{ __('Favourite A&D') }}</span></a>
                        @endif
                        @if ($user_role == 'brand')
                            <a class="dropdown-item" href="{{ route('user.profile.goods') }}"><i class="fa fa-building"></i><span>{{ __('My Products') }}</span></a>
                            <a class="dropdown-item" href="{{ route('good.edit.admin') }}"><i class="fa fa-building"></i><span>{{ __('Add new product') }}</span></a>
                            <a class="dropdown-item" href="{{ route('user.profile.goods', ['favorite']) }}"><i class="fa fa-heart"></i><span>{{ __('Favourite products') }}</span></a>
                        @endif
                        @if ($user_role == 'furnitureseller')
                            <a class="dropdown-item" href="{{ route('user.profile.furnitures') }}"><i class="fa fa-building"></i><span>{{ __('My Furniture') }}</span></a>
                            <a class="dropdown-item" href="{{ route('furniture.edit.admin') }}"><i class="fa fa-plus-circle"></i><span>{{ __('Add new furniture') }}</span></a>
                            <a class="dropdown-item" href="{{ route('user.profile.furnitures', ['favorite']) }}"><i class="fa fa-heart"></i><span>{{ __('Favourite furniture') }}</span></a>
                        @endif
                        @if ($user_role == 'seller')
                            <a class="dropdown-item" href="{{ route('user.profile.products') }}"><i class="fa fa-building"></i><span>{{ __('My Products') }}</span></a>
                            <a class="dropdown-item" href="{{ route('product.edit.admin') }}"><i class="fa fa-plus-circle"></i><span>{{ __('Add new product') }}</span></a>
                            <a class="dropdown-item" href="{{ route('user.profile.products', ['favorite']) }}"><i class="fa fa-heart"></i><span>{{ __('Favourite products') }}</span></a>
                        @endif
                        @if ($user_role == 'wineseller')
                            <a class="dropdown-item" href="{{ route('user.profile.wines') }}"><i class="fa fa-building"></i><span>{{ __('My Wines') }}</span></a>
                            <a class="dropdown-item" href="{{ route('wine.edit.admin') }}"><i class="fa fa-plus-circle"></i><span>{{ __('Add new wine') }}</span></a>
                            <a class="dropdown-item" href="{{ route('user.profile.wines', ['favorite']) }}"><i class="fa fa-heart"></i><span>{{ __('Favourite wines') }}</span></a>
                        @endif

                        <!-- <a class="dropdown-item" href="{{ route('user.profile.properties') }}"><i class="fa fa-building"></i><span>@if ($user_role == 'administrator'){{ __('Properties List') }} @else {{ __('My Properties') }} @endif</span></a>
                        <a class="dropdown-item" href="{{ route('property.edit.admin') }}"><i class="fa fa-plus-circle"></i><span>{{ __('Add new property') }}</span></a>
                        <a class="dropdown-item" href="{{ route('user.profile.properties', ['favorite']) }}"><i class="fa fa-heart"></i><span>{{ __('Favourite properties') }}</span></a>                       
                        <a class="dropdown-item" href="{{ route('user.profile.saved_searches') }}"><i class="fa fa-search-plus"></i><span>{{ __('Saved Searches') }}</span></a> -->
                        
                        @if ($user_role == 'administrator')
                            <a class="dropdown-item" href="{{ route('user.profile.goods') }}"><i class="fa fa-file-image-o"></i><span>{{ __('Goods List') }}</span></a>
                            <!-- <a class="dropdown-item" href="{{ route('user.profile.furnitures') }}"><i class="fa fa-file-image-o"></i><span>{{ __('Furniture List') }}</span></a> -->
                            <a class="dropdown-item" href="{{ route('user.profile.products') }}"><i class="fa fa-file-image-o"></i><span>{{ __('Products List') }}</span></a>
                            <!-- <a class="dropdown-item" href="{{ route('user.profile.wines') }}"><i class="fa fa-file-image-o"></i><span>{{ __('Wines List') }}</span></a>
                            <a class="dropdown-item" href="{{ route('user.profile.designs') }}"><i class="fa fa-file-image-o"></i><span>{{ __('A&D List') }}</span></a> -->
                            <!-- <a class="dropdown-item" href="{{ route('user.profile.features') }}"><i class="fa fa-tags"></i><span>{{ __('Features List') }}</span></a> -->
                            <a class="dropdown-item" href="{{ route('user.profile.professions') }}"><i class="fa fa-tags"></i><span>{{ __('Professions List') }}</span></a>
                            <a class="dropdown-item" href="{{ route('user.profile.users') }}"><i class="fa fa-users"></i><span>{{ __('Users List') }}</span></a>
                            <a class="dropdown-item" href="{{ route('admin.ad.partners') }}"><i class="fa fa-handshake-o"></i><span>{{ __('Partners') }}</span></a>
                            <!-- <a class="dropdown-item" href="{{ route('admin.ad.users') }}"><i class="fa fa-buysellads"></i><span>{{ __('Users Ad') }}</span></a> -->
                            <a class="dropdown-item" href="{{ route('admin.profile.pages') }}"><i class="fa fa-language"></i><span>{{ __('Pages') }}</span></a>
                            <a class="dropdown-item" href="{{ route('admin.emails.settings') }}"><i class="fa fa-envelope"></i><span>{{ __('Emails') }}</span></a>
                            <a class="dropdown-item" href="/translations" target="_blank"><i class="fa fa-language"></i><span>{{ __('Languages') }}</span></a>
                        @endif

                        <a class="dropdown-item" href="{{ route('user.profile.news') }}"><i class="fa fa-file-image-o"></i><span>{{ __('News List') }}</span></a>
                        <a class="dropdown-item" href="{{ route('news.edit.admin') }}"><i class="fa fa-plus-circle"></i><span>{{ __('Add new News') }}</span></a>
                        <a class="dropdown-item" href="{{ route('user.profile.news', ['favorite']) }}"><i class="fa fa-heart"></i><span>{{ __('Favourite News') }}</span></a>
                        
                        <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fa fa-unlock"></i>{{ __('Logout') }}
                        </a>
                    </div>
                </li>
            </ul>
            @endguest
			<ul class="navbar-nav">
				@if (sizeof($sup_locales) > 1)
					<li class="nav-item dropdown">
						<a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ $sup_locales[$cur_locale] }}</a>
						<div class="dropdown-menu">
							@foreach($sup_locales as $locale => $name)
								<a class="dropdown-item" href="{{ route('lang', $locale) }}">{{ $name }} ({{ $locale }})
									@if ($locale == $cur_locale)
										<i class="fa fa-check"></i>
									@endif
								</a>
							@endforeach
						</div>
					</li>
				@endif
                @guest
                    <li class="nav-item">
                        <a href="{{ route('login') }}" class="nav-link">
                        <span class="hidden-sm hidden-xs">{{ __('Sign In') }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('register') }}" class="nav-link">
                        <span class="hidden-sm hidden-xs">{{ __('Register') }}</span>
                        </a>
                    </li>
                @endguest
                    <!-- <li class="nav-item d-xs-none d-sm-none d-lg-block">
                        <a href="{{ route('property.edit.admin') }}" class="btn btn-default nav-btn">{{ __('Create Listing') }}</a>
                    </li> -->
			</ul>
		</div>
	</nav>
</header>

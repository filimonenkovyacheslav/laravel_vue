<header id="header-section" class="header-section {{ !empty($route_name) && $route_name == 'home' ? 'home-header' : '' }}">
	<nav class="navbar navbar-light navbar-expand-lg">
		<a class="navbar-brand" href="{{ app('request')->input('es') }}">
		<img src="//everisearch.com/images/logo-small-white.png" class="d-inline-block align-top" alt="logo" />
		</a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarHeaderContent" aria-controls="navbarHeaderContent" aria-expanded="false" aria-label="Toggle navigation">
		<i class="fa fa-bars" aria-hidden="true"></i>
		</button>
		<div class="collapse navbar-collapse" id="navbarHeaderContent">
			<ul class="navbar-nav ml-auto">
				<li class="nav-item">
					<a href="{{ app('request')->input('es') }}" class="nav-link">{{ __('Home') }}</a>
				</li>
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
                        <a class="dropdown-item" href="{{ app('request')->input('es') }}/admin/profile"><i class="fa fa-user"></i><span>{{ __('My Profile') }}</span></a>
                        @php ($user_role = Auth::user()->role['name'])
                        <!-- <a class="dropdown-item" href="{{ route('user.profile.saved_searches') }}"><i class="fa fa-search-plus"></i><span>{{ __('Saved Searches') }}</span></a> -->

                        @if ($user_role == 'administrator')
                        <a class="dropdown-item" href="{{ app('request')->input('es') }}/admin/profile/users"><i class="fa fa-users"></i><span>{{ __('Users List') }}</span></a>
                        <a class="dropdown-item" href="{{ app('request')->input('es') }}/translations" target="_blank"><i class="fa fa-language"></i><span>{{ __('Languages') }}</span></a>
                        @endif

                        <a class="dropdown-item" href="{{ app('request')->input('es') }}/logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fa fa-unlock"></i>{{ __('Logout') }}
                        </a>
                    </div>
                </li>
            </ul>
            @endguest
			<ul class="navbar-nav">
				@php ($cur_locale = App::getLocale())
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
                        <a href="{{ app('request')->input('es') }}/login" class="nav-link">
                        <span class="hidden-sm hidden-xs">{{ __('Sign In') }}</span>
                        </a>
                    </li>
                    <!-- <li class="nav-item">
                        <a href="{{ route('register') }}" class="nav-link">
                        <span class="hidden-sm hidden-xs">{{ __('Register') }}</span>
                        </a>
                    </li> -->
                @endguest

			</ul>
		</div>
	</nav>
</header>

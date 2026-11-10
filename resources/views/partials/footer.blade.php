@php ($cnt = sizeof($partners))
@if ($cnt > 0)
    <div></div>
    <div class="partners-container container">
        <div class="row justify-content-center">
            @for ($i = 0; $i < $cnt; $i++)
                <div>
                    <div class="partners">
                        <span class="partner-title">{{ $partners[$i]['title'] }}</span>
                            <div class='partner-image'>
                                <a href="{{ $partners[$i]['url'] }}" target="_blank">
                                    @if (isset($partners[$i]['image']['name']))
                                        <img class="partner-logo" src="/uploads/{{ $partners[$i]['image']['name'] }}">
                                    @endif
                                </a>
                            </div>
                        <span class="partner-name">{{ $partners[$i]['name'] }}</span>
                    </div>
                </div>
            @endfor
        </div>
    </div>
@endif
<footer id="footer-section">
	<div class="footer-top">
		<div class="container">
			<div class="row">
            	<div class="col-md-12 col-sm-12">
					<div class="footer-col">
						<div class="navi text-center">
							<p class="text-uppercase">{{ $site_name }} International</p>
							<ul class="account-action">
                            	<li class="dropdown">
                            		@php ($domain = BaseModel::getCurrentDomain())
                            			<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            				<span class="flag flag-{{ $domain['code'] }}"></span>{{ $domain['name'] }}
                            			</a>
                            			<div class="country-dropdown dropdown-menu">
                            				<ul>
                            					@php ($domains = BaseModel::getDomainsList(false))
                            					@php ($cnt = sizeof($domains))
                            					@php ($fourth = ceil($cnt/4))
                                      
                            					@for ($i = 0; $i < $fourth; $i++)
                            						<li>
                            							<a href="{{  $domains[$i]['link'] }}" tabindex="0">
                            								<span class="flag flag-{{ $domains[$i]['locale'] }}"></span>{{ $domains[$i]['country_name'] }}
                            							</a>
                                                        @for ($s = 1; $s <= 3; $s++)
                                                            @php ($j = $fourth*$s + $i)
                            									@if ($j < $cnt)
                            										<a href="{{  $domains[$j]['link'] }}" tabindex="0">
                            											<span class="flag flag-{{ $domains[$j]['locale'] }}"></span>{{ $domains[$j]['country_name'] }}
                            										</a>
                                                                @else
                                                                    <a href="#" tabindex="0">
                                                                        <span class="flag-leer"></span>
                                                                    </a>
                            									@endif
                                                            @endfor
                            						</li>
                            					@endfor
                            				</ul>
                            			</div>
                            	</li>
                            </ul>
						</div>
					</div>
                </div>
			</div>
		</div>
	</div>
    <div class="footer-bottom">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-sm-12">
                    <div class="footer-col">
                        <p>{{ $site_name }} {{ __('All rights reserved') }}</p>
                    </div>
                </div>
                <div class="col-md-6 col-sm-12">
                    <div class="footer-col navi">
                        <a href="/terms-and-privacy">{{ __('Terms & Privacy') }}</a>
                        <a href="/contact">{{ __('Contact Us') }}</a>
                        @if (in_array($domain['code'], ['de', 'at', 'ch', 'uk']))
                        	<a href="/imprint">{{ __('Imprint') }}</a>
                        @endif
                    </div>
                </div>
                <div class="col-md-3 col-sm-12">
                    <div class="footer-col foot-social">
                        <p>{{ __('Follow us') }} <a target="_blank" href="https://www.facebook.com/medicaleer"><i class="fa fa-facebook-square"></i></a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
<!-- Above the footer. -->
<!--<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-3316346585884811"
     data-ad-slot="1733755434"
     data-ad-format="auto"
     data-full-width-responsive="true"></ins>
<script>
     (adsbygoogle = window.adsbygoogle || []).push({});
</script>-->

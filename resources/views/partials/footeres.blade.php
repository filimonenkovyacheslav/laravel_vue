<footer id="footer-section">
    <div class="footer-bottom">
        <div class="container">
            <div class="row">
                <div class="col-md-4 col-sm-12">
                    <div class="footer-col">
                        <p>{{ $site_name }} {{ __('All rights reserved') }}</p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-12">
                    <div class="footer-col navi">
                        <a href="{{ app('request')->input('es') }}/terms-and-privacy">{{ __('Terms & Privacy') }}</a>
                        <a href="{{ app('request')->input('es') }}/contact">{{ __('Contact Us') }}</a>
						@php ($domain = BaseModel::getCurrentDomain())
                        @if (in_array($domain['code'], ['de', 'at', 'ch', 'uk']))
                        	<a href="{{ app('request')->input('es') }}/imprint">{{ __('Imprint') }}</a>
                        @endif
                    </div>
                </div>
                <div class="col-md-4 col-sm-12">
                    <div class="footer-col foot-social">
                        <!--<p>{{ __('Follow us') }} <a target="_blank" href="https://www.facebook.com/GlobalHomes"><i class="fa fa-facebook-square"></i></a></p>-->
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

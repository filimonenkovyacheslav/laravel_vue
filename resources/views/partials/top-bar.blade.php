<div class="top-bar">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="top-bar-left  houzez-top-bar-full">
                    <div class="top-contact">
                        <ul class="top-drop-downs">
                            <li class="btn-price-lang btn-price">
                                <form id="houzez-currency-switcher-form" method="post" action="#">
                                    <button id="houzez-selected-currency" class="btn dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span>USD</span> <i class="fa fa-sort"></i></button>
                                    <ul id="houzez-currency-switcher-list" class="dropdown-menu" aria-labelledby="dropdown">
                                        <li data-currency-code="EUR">EUR</li>
                                    </ul>
                                    <input type="hidden" id="houzez-switch-to-currency" name="houzez_switch_to_currency" value="USD">
                                    <input type="hidden" id="currency_switch_security" name="nonce" value="0e1fd74230">
                                </form>
                            </li>
                            <li class="btn-price-lang btn-area">
                                <form id="houzez-area-switcher-form" method="post" action="#">
                                    <button id="houzez-selected-area" class="btn dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span>Square Meters</span> <i class="fa fa-sort"></i></button>
                                    <ul id="houzez-area-switcher-list" class="dropdown-menu" aria-labelledby="dropdown">
                                        <li data-area-code="sqft">Square feet</li>
                                        <li data-area-code="sq_meter">Square Meters</li>
                                    </ul>
                                    <input type="hidden" id="houzez-switch-to-area" name="houzez_switch_to_area" value="sq_meter"><input type="hidden" id="houzez_switch_area_text" value="Square Meters">
                                    <input type="hidden" id="area_switch_security" name="nonce" value="5bb6264041">
                                </form>
                            </li>
                            <li class="btn-price-lang btn-language">
                                <form id="houzez-language-switcher-form" method="post" action="#" class="show">
                                    <button id="houzez-selected-language" class="btn dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span>Select Language</span> <i class="fa fa-sort"></i></button>
                                    <ul id="houzez-language-switcher-list" class="dropdown-menu" aria-labelledby="dropdown">
                                        @foreach(CustomLaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                            <li>
                                                <a rel="alternate" hreflang="{{ $localeCode }}" href="{{ CustomLaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}">
                                                    {{ $properties['native'] }}
                                                </a>
                                            </li>
                                        @endforeach       
                                    </ul>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>                
        </div>
    </div>
</div>
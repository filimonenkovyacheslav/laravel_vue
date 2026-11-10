<template>
    <div :class="['advanced-search', 'advance-search-header', isHome ? ' advanced-search-home' : '']">
        <div class="container">
            <div class="row">
                <div class="col-sm-12 search-container">
                    <div v-if="isHome" class="advanced-search-label">{{ trans('All about Health – Locally and Globally') }}</div>
                    <form :action="route('property.list.frontend')" method="get" autocomplete="off" id="search-entity-form" class="search-entity-form">
                        <div class="row">
                            <div class="col-lg-2 col-md-4 col-xs-12">
                                <div class="form-group no-margin">
                                    <select id="searchType" name="search_type" :value="searchType" class="form-control form-control">
                                        <option :value="index" v-for="item, index in params.entity_types">{{ item }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 col-xs-12 advanced-product-fields" style="display:none;">
                                <div class="form-group no-margin">
                                    <select id="category" name="category" :value="urlGetParam('category', 'fcategory')" class="form-control form-control">
                                        <option value="">{{ trans('Category') }}</option>
                                        <option :value="index" v-for="item, index in params.product_categories">{{ item }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 col-xs-12 advanced-wine-fields" style="display:none;">
                                <div class="form-group no-margin">
                                    <select id="category" name="category" :value="urlGetParam('category', 'fcategory')" class="form-control form-control">
                                        <option value="">{{ trans('Category') }}</option>
                                        <option :value="index" v-for="item, index in params.wine_categories">{{ item }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 col-xs-12 advanced-furniture-fields" style="display:none;">
                                <div class="form-group no-margin">
                                    <select id="category" name="category" :value="urlGetParam('category', 'fcategory')" class="form-control form-control">
                                        <option value="">{{ trans('Category') }}</option>
                                        <option :value="index" v-for="item, index in params.furniture_categories">{{ item }}</option>
                                    </select>
                                </div>
                            </div>
                            <div :class="['col-xs-12', 'location-field', searchType != 'product' && searchType != 'wine' && searchType != 'furniture' ? 'col-lg-6 col-md-8' : 'col-lg-4 col-md-4']">
                                <div class="form-group no-margin">
                                    <div class="search-location">
                                        <input type="text" name="search_location" id="search_location" :value="urlGetParam('search_location')" class="form-control" :placeholder="trans('Location')">
                                        <input type="hidden" name="lat" :value="urlGetParam('lat')">
                                        <input type="hidden" name="lng" :value="urlGetParam('lng')">
                                        <input type="hidden" name="ao" :value="urlGetParam('ao')">
                                        <input type="hidden" name="ar" :value="urlGetParam('ar')">
                                        <input type="hidden" name="ac" :value="urlGetParam('ac')">
                                        <input type="hidden" name="as" :value="urlGetParam('as')">
                                        <input type="hidden" name="ai" :value="urlGetParam('ai')">
                                        <input type="hidden" name="key" :value="urlGetParam('key')">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-12 col-xs-7 advanced-all-search">
                                <div class="row">
                                    <div class="col-md-4 col-xs-12 radius-field-wrapper">
                                        <div class="form-group no-margin">
                                            <input type="text" name="radius" :value="urlGetParam('radius')" class="form-control" :placeholder="trans('No Radius')">
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-xs-6 text-center button-wrapper">
                                        <div class="form-group no-margin">
                                            <button type="button" data-toggle="collapse" data-target="#globalSearchAdvancedShell" aria-expanded="false" class="advance-btn" >
                                                <i class="fa fa-gear"></i>
                                                <span>{{ trans('Advanced') }}</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-xs-6 button-wrapper">
                                        <div class="form-group no-margin">
                                            <button type="submit" class="btn btn-black btn-block">{{ trans('Search') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-8 col-xs-12 advanced-news-search">
                                <div class="keyword-field-wrapper">
                                    <div class="form-group">
                                        <input type="text" name="news_search_word" id="keyword-autocomplete" :value="(urlGetParam('news_search_word'))?urlGetParam('news_search_word'):''" class="form-control" @change="getKeywordAutocomplete" :placeholder="trans('Enter keyword...')">
                                        <input type="hidden" name="news_keyword">
                                    </div>
                                </div>                               
                            </div>

                            <div class="col-lg-4 col-md-12 col-xs-7 advanced-news-search">
                                <div class="form-group no-margin">
                                    <button type="button" v-on:click="newsSearch" class="btn btn-black btn-block">{{ trans('Search') }}</button>
                                </div>
                            </div>

                        </div>
                        <div id="globalSearchAdvancedShell" class="advance-fields collapse">
                            <div class="row">
                                <div class="col-md-12 col-xs-12 keyword-field-wrapper">
                                    <div class="form-group">
                                        <input type="text" name="keyword" id="keyword" :value="urlGetParam('keyword')" class="form-control" @change="getKeywordAutocomplete" :placeholder="trans('Enter keyword...')">
                                        <input type="text" name="artist" id="artist" :value="urlGetParam('artist')" class="form-control" :placeholder="trans('Enter Artist')">
                                    </div>
                                </div>
                                <!--<div class="col-md-2 col-xs-6">
                                    <div class="form-group advanced-property-fields">
                                        <select name="property_status" :value="urlGetParam('property_status')" class="form-control">
                                            <option value="">{{ trans('All Statuses') }}</option>
                                            <option :value="item.id" v-for="item in params.property_statuses">{{ item.label }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 col-xs-6">
                                    <div class="form-group advanced-property-fields">
                                        <select name="property_type" :value="urlGetParam('property_type')" class="form-control" >
                                            <option value="">{{ trans('All Types') }}</option>
                                            <option :value="item.id" v-for="item in params.property_types">{{ item.label }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 col-xs-6">
                                    <div class="form-group">
                                        <select name="property_subtype" :value="urlGetParam('property_subtype')" class="form-control" >
                                            <option value="">{{ trans('All Subtypes') }}</option>
                                            <option :value="item.id" v-for="item in params.property_subtypes">{{ item.label }}</option>
                                        </select>
                                    </div>
                                </div>-->
                            </div>
                            <div class="advanced-product-fields row">
                                <div class="col-md-6 col-xs-12">
                                    <div class="form-group">
                                        <select name="seller_type" :value="urlGetParam('seller_type')" class="form-control" >
                                            <option value="">{{ trans('Seller') }}</option>
                                            <option :value="index" v-for="label, index in params.seller_types">{{ label }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 col-xs-6">
                                    <div class="form-group">
                                        <input type="text" name="mprice[min]" :value="urlGetParam('mprice[min]')" class="form-control" :placeholder="trans('Min Price')">
                                    </div>
                                </div>
                                <div class="col-md-3 col-xs-6">
                                    <div class="form-group">
                                        <input type="text" name="mprice[max]" :value="urlGetParam('mprice[max]')" class="form-control" :placeholder="trans('Max Price')">
                                    </div>
                                </div>
                            </div>
                            <div class="advanced-wine-fields row">
                                <div class="col-md-6 col-xs-12">
                                    <div class="form-group">
                                        <select name="wineseller_type" :value="urlGetParam('wineseller_type')" class="form-control" >
                                            <option value="">{{ trans('Wine Seller') }}</option>
                                            <option :value="index" v-for="label, index in params.wineseller_types">{{ label }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 col-xs-6">
                                    <div class="form-group">
                                        <input type="text" name="mprice[min]" :value="urlGetParam('mprice[min]')" class="form-control" :placeholder="trans('Min Price')">
                                    </div>
                                </div>
                                <div class="col-md-3 col-xs-6">
                                    <div class="form-group">
                                        <input type="text" name="mprice[max]" :value="urlGetParam('mprice[max]')" class="form-control" :placeholder="trans('Max Price')">
                                    </div>
                                </div>
                            </div>
                            <div class="advanced-furniture-fields row">
                                <div class="col-md-6 col-xs-12">
                                    <div class="form-group">
                                        <select name="furnitureseller_type" :value="urlGetParam('furnitureseller_type')" class="form-control" >
                                            <option value="">{{ trans('Furniture Seller') }}</option>
                                            <option :value="index" v-for="label, index in params.furnitureseller_types">{{ label }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 col-xs-6">
                                    <div class="form-group">
                                        <input type="text" name="mprice[min]" :value="urlGetParam('mprice[min]')" class="form-control" :placeholder="trans('Min Price')">
                                    </div>
                                </div>
                                <div class="col-md-3 col-xs-6">
                                    <div class="form-group">
                                        <input type="text" name="mprice[max]" :value="urlGetParam('mprice[max]')" class="form-control" :placeholder="trans('Max Price')">
                                    </div>
                                </div>
                            </div>
                            <div class="advanced-property-fields row">
                                <div class="col-md-2 col-xs-6">
                                    <div class="form-group">
                                        <input type="text" name="bedrooms" :value="urlGetParam('bedrooms')" class="form-control" :placeholder="trans('Beds')">
                                    </div>
                                </div>
                                <div class="col-md-2 col-xs-6">
                                    <div class="form-group">
                                        <input type="text" name="bathrooms" :value="urlGetParam('bathrooms')" class="form-control" :placeholder="trans('Baths')">
                                    </div>
                                </div>
                                <div class="col-md-2 col-xs-6">
                                    <div class="form-group">
                                        <input type="text" name="property_area[min]" :value="urlGetParam('property_area[min]')" class="form-control" :placeholder="trans('Min Area')+' ('+params.measure_default.abbreviation+')'">
                                    </div>
                                </div>
                                <div class="col-md-2 col-xs-6">
                                    <div class="form-group">
                                        <input type="text" name="property_area[max]" :value="urlGetParam('property_area[max]')" class="form-control" :placeholder="trans('Max Area')+' ('+params.measure_default.abbreviation+')'">
                                    </div>
                                </div>
                                <div class="col-md-2 col-xs-6">
                                    <div class="form-group">
                                        <input type="text" name="price[min]" :value="urlGetParam('price[min]')" class="form-control" :placeholder="trans('Min Price')">
                                    </div>
                                </div>
                                <div class="col-md-2 col-xs-6">
                                    <div class="form-group">
                                        <input type="text" name="price[max]" :value="urlGetParam('price[max]')" class="form-control" :placeholder="trans('Max Price')">
                                    </div>
                                </div>
                                <div class="col-md-3 col-xs-6">
                                    <div class="form-group">
                                        <select name="currency_code" :value="currencyCode" class="form-control" :placeholder="trans('Currency')">
                                            <option v-for="item, index in params.currencies" :value="index">{{ item }}</option>
                                        </select>
                                    </div>
                                </div>
                                <!--<div class="col-md-2 col-xs-6">
                                    <div class="form-group">
                                        <select name="property_rent_schedule" :value="propertyRentSchedule" class="form-control">
                                            <option value="">{{ trans('Rent Schedule') }}</option>
                                            <option v-for="item in params.property_rent_schedule" :value="item.id">{{ item.label }}</option>
                                        </select>
                                    </div>
                                </div>-->
                                <div class="col-md-12 col-xs-12 features-list">
                                    <div class="row">
                                        <div class="col-md-12 col-xs-12">
                                        <div class="panel-title">
                                            <a href="#globalSearchFeaturesShell" role="button" data-toggle="collapse" aria-expanded="false" aria-controls="globalSearchFeaturesShell" class="advance-trigger text-uppercase title collapsed">
                                                <span>{{ trans('Other Features') }}</span>
                                            </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="globalSearchFeaturesShell" class="row collapse">
                                        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6" v-for="item, index in params.features">
                                            <label class="checkbox-inline">
                                                <input type="checkbox" name="features[]" :value="item.feature_id" :checked="inArray(item.feature_id, features)" />
                                                <span>{{ item.name }}</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="fcategory" :value="urlGetParam('category', 'fcategory')">
                        <input type="hidden" name="measure_code" :value="params.measure_default.code">
                        <input type="hidden" name="_token" v-model="csrf">
                    </form>
                    <input type="hidden" id="location_address" :value="urlGetParam('search_location')">
                </div>
            </div>
        </div>
        <message-bar :message="messageData" :errors="errorsList" :isError="errorsExist"></message-bar>
    </div>
</template>

<script>
    export default {
        data: function() {
            var paramsArr = this.getUrlParams(),
                features = [],
                i = 0,
                param;

            do {
                param = 'features[' + i + ']';
                if(typeof paramsArr[param] != 'undefined') {
                    features.push(paramsArr[param]);
                } else break;
                i++;
            } while(true);
            return {
                isHome: false,
                isArtist: false,
                isSeller: false,
                isWineseller: false,
                isFurnitureseller: false,
                isBrand: false,
                isNews: false,
                searchType: this.getSearchType(),
                currencyCode: this.urlGetParam('currency_code') || 840,
                propertyRentSchedule: this.urlGetParam('property_rent_schedule') || '',
                features: features
            };
        },
        props: ['params', 'is_home', 'is_artist', 'is_seller', 'is_wineseller', 'is_furnitureseller', 'is_brand', 'is_news'],
        mounted: function() {
            this.isHome = typeof this.is_home != 'undefined' ? this.is_home : this.isHome;
            this.isArtist = typeof this.is_artist != 'undefined' ? this.is_artist : this.isArtist;
            this.isSeller = typeof this.is_seller != 'undefined' ? this.is_seller : this.isSeller;
            this.isWineseller = typeof this.is_wineseller != 'undefined' ? this.is_wineseller : this.isWineseller;
            this.isFurnitureseller = typeof this.is_furnitureseller != 'undefined' ? this.is_furnitureseller : this.isFurnitureseller;
            this.isBrand = typeof this.is_brand != 'undefined' ? this.is_brand : this.isBrand;
            this.isNews = typeof this.is_news != 'undefined' ? this.is_news : this.isNews;
            
            if (this.searchType !== 'news') {
                $('.advanced-news-search').hide()
                $('.advanced-news-search input').prop("disabled", true)
                
                $('.location-field').show()
                $('.advanced-all-search').show()
                $('.location-field input').prop("disabled", false)
                $('.advanced-all-search input').prop("disabled", false)
                $('#globalSearchAdvancedShell [name="keyword"]').prop("disabled", false)
            }
            else{
                $('.advanced-news-search').show()
                $('.advanced-news-search input').prop("disabled", false)
                
                $('.location-field').hide()
                $('.advanced-all-search').hide()
                $('.location-field input').prop("disabled", true)
                $('.advanced-all-search input').prop("disabled", true)
                $('#globalSearchAdvancedShell [name="keyword"]').prop("disabled", true)
            }
            
            var self = this,
                propertyFields = $('.advanced-property-fields'),
                locationField = $('.location-field'),
                locationShell = $('.search-location'),
                locationAddress = $('#location_address'),
                locationInput = locationShell.find('input[name="search_location"]'),
                searchForm = $('form.search-entity-form'),
                searchType = $('#searchType'),
                radiusShell = $('.radius-field-wrapper'),
                buttonShell = $('.button-wrapper'),
                inputKeywordWrapper = $('.keyword-field-wrapper'),
                inputKeyword = $("#keyword"),
                inputArtist = $("#artist"),
                productFields = $('.advanced-product-fields'),
                wineFields = $('.advanced-wine-fields'),
                furnitureFields = $('.advanced-furniture-fields'),
                defaultRadius = 100,
                first = true,
                defaultType = searchType.val();

            this.getSimpleKeywordsAutocomplete($('#keyword-autocomplete'), {type: ''},
                function(item, event, ui) {
                    console.log({key_id: item.id, keyword: item.value})
                    $('[name="news_keyword"]').val(item.id)
                }
            );
            
            searchType.on('change', function() {
                var $this = $(this),
                    value = $this.val(),
                    form = $this.parents('form:first'),
                    keyLocation = (value == 'product' || value == 'wine' || value == 'furniture' || value == 'good' || value == 'art' || value == 'design' || value == 'brand' || value == 'news');

                form.attr('action', self.route(value + '.list.frontend'));
                form.find('input[name="keyword"]').data('search-type', value);
                form.find('input[name="keyword"]').attr('search-type', value);
                if (!first) {
                    locationShell.find('input[name="key"]').val('');
                    locationAddress.val('');
                    locationInput.val('');
                }

                if (value !== 'news') {
                    $('.advanced-news-search').hide()
                    $('.advanced-news-search input').prop("disabled", true)

                    $('.location-field').show()
                    $('.advanced-all-search').show()
                    $('.location-field input').prop("disabled", false)
                    $('.advanced-all-search input').prop("disabled", false)
                    $('#globalSearchAdvancedShell [name="keyword"]').prop("disabled", false)
                }
                else{
                    $('.advanced-news-search').show()
                    $('.advanced-news-search input').prop("disabled", false)

                    $('.location-field').hide()
                    $('.advanced-all-search').hide()
                    $('.location-field input').prop("disabled", true)
                    $('.advanced-all-search input').prop("disabled", true)
                    $('#globalSearchAdvancedShell [name="keyword"]').prop("disabled", true)
                }
    
                locationField.removeClass('col-lg-3 col-md-4').addClass('col-lg-6 col-md-8');
                inputKeyword.fadeIn(0);
                inputArtist.fadeOut(0);
                inputKeywordWrapper.fadeIn(0);
                inputKeyword.prop("disabled",false);
                productFields.fadeOut(0);
                wineFields.fadeOut(0);
                furnitureFields.fadeOut(0);
                radiusShell.fadeIn(0);
                buttonShell.addClass('col-md-4').removeClass('col-md-6');
                $('#globalSearchAdvancedShell').addClass('collapse');

                if(value == 'property') {
                    propertyFields.fadeIn(300);
                    //inputKeyword.parents('.keyword-field-wrapper:first').removeClass('col-md-12').addClass('col-md-6');
                    self.getLocationAutocomplete(locationInput,
                        {url: '/search-location', _token: self.csrf},
                        function(item, event) {
                            if (item.id) {
                                locationShell.find('input[name="key"]').val(item.id);
                            } else {
                                locationShell.find('input[name="lat"]').val(item.lat);
                                locationShell.find('input[name="lng"]').val(item.lng);
                                locationShell.find('input[name="ao"]').val(item.other);
                                locationShell.find('input[name="ar"]').val(item.street);
                                locationShell.find('input[name="ac"]').val(item.city);
                                locationShell.find('input[name="as"]').val(item.state);
                                locationShell.find('input[name="ai"]').val(item.iso3);
                                locationShell.find('input[name="key"]').val('');
                            }

                            locationAddress.val(item.value);
                        }
                    );
                } else {
                    if (value == 'art') {
                        propertyFields.fadeOut(300);
                        //inputKeyword.parents('.keyword-field-wrapper:first').removeClass('col-md-6').addClass('col-md-12');
                        inputKeyword.fadeOut(0);
                        inputArtist.fadeIn(0);
                    } else if(value == 'product' || value == 'wine' || value == 'furniture') {
                        propertyFields.fadeOut(0);
                        inputKeyword.fadeOut(0);
                        if(value == 'product') productFields.fadeIn(0);
                        else if(value == 'wine') wineFields.fadeIn(0);
                        else furnitureFields.fadeIn(0);
                        defaultRadius = 10;
                        locationField.removeClass('col-lg-6 col-md-8').addClass('col-lg-4 col-md-4');
                        $('#globalSearchAdvancedShell').removeClass('collapse');
                    } else {
                        propertyFields.fadeOut(300);
                        //$('input[name="keyword"]').parents('.keyword-field-wrapper:first').removeClass('col-md-6').addClass('col-md-12');
                    }
                    if (keyLocation) {
                        self.getAddressKeywordsAutocomplete(locationInput, {type: value},
                            function(item, event) {
                                locationShell.find('input[name="key"]').val(item.id);
                                locationAddress.val(item.value);
                            }
                        );
                        radiusShell.fadeOut(0);
                        buttonShell.removeClass('col-md-4').addClass('col-md-6');
                    }
                }
                first = false;
            }).trigger('change');
            
            if (this.isArtist) {
                searchType.find('[value="art"]').prop('selected', true);
                searchType.trigger('change');
            }
            if (this.isSeller) {
                searchType.find('[value="product"]').prop('selected', true);
                searchType.trigger('change');
            }
            if (this.isWineseller) {
                searchType.find('[value="wine"]').prop('selected', true);
                searchType.trigger('change');
            }
            if (this.isFurnitureseller) {
                searchType.find('[value="furniture"]').prop('selected', true);
                searchType.trigger('change');
            }
            if (this.isBrand) {
                searchType.find('[value="good"]').prop('selected', true);
                searchType.trigger('change');
            }
            if (this.isNews) {
                searchType.find('[value="news"]').prop('selected', true);
                searchType.trigger('change');
            }
    
            /*if (this.getSearchType() != 'property') {
                this.geoSearchAutocompleate(document.getElementById('search_location'),
                    {
                        provider: 'bing',
                        onSelect:  function(item, event) {
                            locationShell.find('input[name="lat"]').val(item.lat);
                            locationShell.find('input[name="lng"]').val(item.lng);
                            locationShell.find('input[name="ao"]').val(item.other);
                            locationShell.find('input[name="ar"]').val(item.street);
                            locationShell.find('input[name="ac"]').val(item.city);
                            locationShell.find('input[name="as"]').val(item.state);
                            locationShell.find('input[name="ai"]').val(item.iso3);
                            locationAddress.val(item.value);
                        }
                    }
                );
            } else {
                this.getLocationAutocomplete(document.getElementById('search_location'),
                    {url: '/search-location', _token: self.csrf},
                    function(item, event) {
                        locationShell.find('input[name="lat"]').val(item.lat);
                        locationShell.find('input[name="lng"]').val(item.lng);
                        locationShell.find('input[name="ao"]').val(item.other);
                        locationShell.find('input[name="ar"]').val(item.street);
                        locationShell.find('input[name="ac"]').val(item.city);
                        locationShell.find('input[name="as"]').val(item.state);
                        locationShell.find('input[name="ai"]').val(item.iso3);
                        locationAddress.val(item.value);
                    }
                );
            }*/
            

            searchForm.on('submit', function(event) {
                var type = searchType.val(),
                    keyLocation = (type == 'product' || type == 'wine' || type == 'furniture' || type == 'good' || type == 'art' || type == 'design' || type == 'news');
                self.messageData = '';
                if(locationAddress.val() != locationInput.val()) {
                    locationShell.find('input[name="lat"]').val('');
                    locationShell.find('input[name="lng"]').val('');
                    locationShell.find('input[name="ao"]').val('');
                    locationShell.find('input[name="ar"]').val('');
                    locationShell.find('input[name="ac"]').val('');
                    locationShell.find('input[name="as"]').val('');
                    locationShell.find('input[name="ai"]').val('');
                    locationShell.find('input[name="key"]').val('');
                }
                if(searchForm.find('input[name="radius"]').val() == '') {
                    if (self.searchType == 'product' || self.searchType == 'wine' || self.searchType == 'furniture' || self.searchType == 'good') {
                        defaultRadius = 10;
                    }
                    searchForm.find('input[name="radius"]').val(defaultRadius);
                }
                /*if (keyLocation) {
                    if (locationInput.val() == '') {
                        locationShell.find('input[name="key"]').val('');
                    } else if (locationShell.find('input[name="key"]').val() == '') {
                        self.messageData = 'Be sure to choose a location hint';
                        self.errorsExist = true;
                        searchType.val(defaultType).trigger('change');
                        return false;
                    }
                }*/
                if (locationInput.val() == '') {
                    locationShell.find('input[name="key"]').val('');
                }
                if (keyLocation) {
                    if (locationInput.val() != '' && locationShell.find('input[name="key"]').val() == '') {
                        self.messageData = 'Be sure to choose a location hint';
                        self.errorsExist = true;
                        searchType.val(defaultType).trigger('change');
                        return false;
                    }
                }
                return true;
            });

            if(this.params.use_elastic_search) {
                this.getKeywordAutocomplete($('.advanced-search input[name="keyword"]'),{
                    onSelect: function(item, event, ui) {
                        window.location.href = self.route(item.type + '.view.frontend', {'slug': item.slug})
                    }
                });
            }

            searchForm.find('select[name="property_status"]').on('change', function() {
                var statusVal = $(this).val();
                if(statusVal == 1) {
                    searchForm.find('select[name="property_rent_schedule"]').show();
                } else {
                    searchForm.find('select[name="property_rent_schedule"]').val('').hide();
                }
            }).trigger('change');

            searchForm.find('select[name="property_type"]').on('change', function() {
                var statusVal = $(this).val();
                if(statusVal == 6) {
                    searchForm.find('select[name="property_subtype"]').show();
                } else {
                    searchForm.find('select[name="property_subtype"]').val('').hide();
                }
            }).trigger('change');
        },
        methods: {
            getSearchType: function() {
                var searchType = this.urlGetParam('search_type');

                if(!searchType && this.params && this.params.route_name) {
                    var pageType = this.params.route_name.split('.')[0];

                    searchType = this.params.entity_types && this.params.entity_types[pageType] ? pageType : 'property';
                }
                return searchType;
            },
            getValueForMultiselect: function(value, options, sort) {
                var values = typeof(value) == 'undefined' || value == null ? [] : value.split(','),
                    res = [],
                    i;
                for(var j in sort) {
                    i = sort[j];
                    if(this.inArray(i, values)) {
                        res.push({label: options[i], value: i});
                    }
                }
                return res;
            },
            newsSearch: function(){
                const keyword = $('[name="news_keyword"]').val()
                const newsSearchWord = $('[name="news_search_word"]').val()
                if (keyword) {                   
                    location.href = '/news?news_keyword='+keyword
                }
                else if (newsSearchWord){
                    location.href = '/news?news_search_word='+newsSearchWord
                }
            }
        }
    }
</script>

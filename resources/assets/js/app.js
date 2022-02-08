
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

/**
 * Init Vue: links will be like: http://medicaleer/properties
 */
require('./bootstrap');

import Vue from 'vue';
import _ from 'lodash';
import tinymce from 'tinymce';
import 'tinymce/themes/modern/theme.js';
import VueTinymce from 'vue-tinymce';
import vSelect from 'vue-select';
import VModal from 'vue-js-modal';
import { GeoSearchControl, OpenStreetMapProvider, BingProvider, EsriProvider } from 'leaflet-geosearch';
import 'jquery-ui/ui/widgets/autocomplete.js';
import 'jquery-ui/ui/widgets/sortable.js';
import 'lightgallery/dist/css/lightgallery.css';
import 'lightgallery/dist/js/lightgallery.js';

import 'owl.carousel2/dist/assets/owl.carousel.css';
import 'owl.carousel2/dist/assets/owl.theme.default.css';
import 'owl.carousel2';
import 'owl.carousel2.thumbs';

import axios from 'axios';

Vue.use(VueTinymce);
Vue.component('v-select', vSelect);
Vue.use(VModal);
$.fn.reverse = [].reverse;

Vue.mixin({
    data: function () {
        return {
            window: window,
            entityPath: ['entity'],
            geocoder: null,
            entity: {},
            entities_list: {
                total: 0,
                per_page: 2,
                from: 1,
                to: 0,
                current_page: 1
            },
            offset: 4,
            currentSortOrder: 'title',
            messageData: '',
            errorsExist: false,
            errorsList: [],
            csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            hereApiKey: 'fScPU9upJpNd5aurobYRgHn-bjo0rmfJhRO-e3oNUv4',
            mapBoxApiKey: 'pk.eyJ1Ijoic3RyaWxlemtpanNsYXdhIiwiYSI6ImNrNnYxdTRrMjAxaGUzZm1peHhuYnQyZncifQ.NMrr6HaCm7r7la4oCX2agg',
            markers: [],
            routeControl: null,
            provider: null
        }
    },
    methods: {
        prepareIndex: function(index) {
            return index.replace('[', '-').replace(']', '');
        },
        getFeaturedImageName: function(list) {
            if(list && list.length && list[0] && list[0].name && list[0].type == 1) {
                return list[0].name;
            }
            return '';
        },
        getImageUrl: function(name, type) {
            var defaultImage = type == 'avatar' ? '/images/profile-avatar.png' : '/images/logo-profilepic.jpg';
            if (name) {
                name = name.indexOf('/uploads/') > -1 ? name : '/uploads/' + name;
            }
            return name ? name : defaultImage;
        },
        getClearFileName: function(name) {
            var begin = name.indexOf('/');
            return begin === -1 ? name : name.substring(begin + 1);
        },
        getBgImageStyle: function(name, type) {
            return 'background-image: url(\'' + this.getImageUrl(name, type) + '\');';
        },
        getCompanyName: function(user) {
            return user.company_name ? user.company_name : (user.first_name + ' ' + user.last_name);
        },
        getCompanySlug: function(user) {
            return user.company_slug ? user.company_slug : user.slug;
        },
        switchTabs: function(e) {
            e.preventDefault();
            $(this).tab('show');
        },
        switchCheckboxIcon: function(e) {
            var $elem = $(e.target).parents('.form-group:first'),
                input = $elem.find('input[type="checkbox"]'),
                icon = $elem.find('.checkbox-icon');
            
            if(input.is(':checked')) {
                icon.addClass('fa-check');
            } else {
                icon.removeClass('fa-check');
            }
        },
        switchMultiCheckboxIcon: function(e) {
            var $elem = $(e.target).parents('.checkbox-item:first'),
                input = $elem.find('input[type="checkbox"]'),
                icon = $elem.find('.checkbox-icon');
        
            if(input.is(':checked')) {
                icon.addClass('fa-check');
            } else {
                icon.removeClass('fa-check');
            }
        },
        toggleFavoriteJobEntity: function(jobId) {
            var btn = $(this.$el).find('.favorite-btn .fa'),
                makeFavorite = btn.hasClass('fa-heart-o') ? 1 : 0;
            
            $.post({
                url: '/toggle-favorite-jobEntity',
                data: {job_entity_id: jobId, make_favorite: makeFavorite, _token: $('[name="_token"]').val()},
                dataType: 'json',
                success: function (res) {
                    if(res.favorite) {
                        btn.removeClass('fa-heart-o');
                        btn.addClass('fa-heart');
                    } else {
                        btn.removeClass('fa-heart');
                        btn.addClass('fa-heart-o');
                    }
                }
            });
        },
        toggleFavoriteProperty: function(propertyId) {
            var btn = $(this.$el).find('.favorite-btn .fa'),
                makeFavorite = btn.hasClass('fa-heart-o') ? 1 : 0;
            
            $.post({
                url: '/toggle-favorite-property',
                data: {property_id: propertyId, make_favorite: makeFavorite, _token: $('[name="_token"]').val()},
                dataType: 'json',
                success: function (res) {
                    if(res.favorite) {
                        btn.removeClass('fa-heart-o');
                        btn.addClass('fa-heart');
                    } else {
                        btn.removeClass('fa-heart');
                        btn.addClass('fa-heart-o');
                    }
                }
            });
        },
        toggleFavoriteArt: function(artId) {
            var btn = $(this.$el).find('.favorite-btn .fa'),
                makeFavorite = btn.hasClass('fa-heart-o') ? 1 : 0;
        
            $.post({
                url: '/toggle-favorite-art',
                data: {art_id: artId, make_favorite: makeFavorite, _token: $('[name="_token"]').val()},
                dataType: 'json',
                success: function (res) {
                    if(res.favorite) {
                        btn.removeClass('fa-heart-o');
                        btn.addClass('fa-heart');
                    } else {
                        btn.removeClass('fa-heart');
                        btn.addClass('fa-heart-o');
                    }
                }
            });
        },

        toggleFavorite: function(id, typ) {
            var btn = $(this.$el).find('.favorite-btn .fa'),
                makeFavorite = btn.hasClass('fa-heart-o') ? 1 : 0;
        
            $.post({
                url: '/toggle-favorite-' + typ,
                data: {id: id, make_favorite: makeFavorite, _token: $('[name="_token"]').val()},
                dataType: 'json',
                success: function (res) {
                    if(res.favorite) {
                        btn.removeClass('fa-heart-o');
                        btn.addClass('fa-heart');
                    } else {
                        btn.removeClass('fa-heart');
                        btn.addClass('fa-heart-o');
                    }
                }
            });
        },
        showDialog: function(name, params) {
            params = params || {};
            this.$modal.show(name, params)
        },
        getGeocoder: function() {
            if(!this.geocoder) {
                this.geocoder = new GeoSearchControl();
            }
            return this.geocoder;
        },
        initMap: function(elemId, coords, options) {
            var self = this;
            var element = document.getElementById(elemId),
                options = typeof options !== 'undefined' ? options : {},
                draggable = 'draggable' in options ? options.draggable : true,
                //entity = typeof options !== 'undefined' ? options.entity : true,
                lat = coords.lat || 0,
                lng = coords.lng || 0;
            if (element) {
                var leafletMap = L.map(element).setView([lat, lng], 16);
                
                /*L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw', {
                    maxZoom: 18,
                    attribution: '',
                    id: 'mapbox.streets'
                }).addTo(leafletMap);*/
                L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw', {
                    attribution: '',
                    tileSize: 512,
                    maxZoom: 18,
                    zoomOffset: -1,
                    id: 'mapbox/streets-v11'
                }).addTo(leafletMap);
                
                setInterval(function() {
                    leafletMap.invalidateSize();
                }, 100);
                
                var marker =  L.marker([lat, lng], {
                    draggable: draggable,
                    riseOnHover:true
                }).addTo(leafletMap);
                
                marker.on("dragend",function(e){
                    var chagedPos = e.target.getLatLng();
                    options.draggable = true;

                    self.updateMapAddressData(element, chagedPos.lat, chagedPos.lng, options);
                    self.updateMarkerPosition(chagedPos.lat, chagedPos.lng);
                    self.toggleResetButton();
                });
                
                this.map = leafletMap;
                this.marker = marker;
            }
        },
        
        updateMapAddressData: function(elem, lat, lng, options) {
            var $elem = elem instanceof $ ? elem : $(elem),
                self = this;
            
            if(this.map && this.marker && !isNaN(lat) && !isNaN(lng)) {
                var parent = $elem.parents('form:first'),
                    countries = self.$parent.params.countries_codes,
                    entity = self.$parent && self.$parent.entity,
                    draggable = false;
                
                $.get('https://nominatim.openstreetmap.org/reverse?format=jsonv2&accept-language=en&lat='+lat+'&lon='+lng, function(data){
                    if (typeof options !== 'undefined') {
                        entity = options.entity ? options.entity : entity;
                        countries = options.countries ? options.countries : countries;
                        draggable = options.draggable;
                    }
                    countries = typeof countries === 'undefined' ? [] : countries;
                    if (entity && 'address' in entity){
                        var city = data.address.city ? data.address.city : (data.address.town ? data.address.town : data.address.village);
                        entity.address = data.display_name;
                        entity.map_address = data.display_name;
                        entity.city = city ? city : '';
                        entity.state = data.address.state ? data.address.state : '';
                        entity.postal_code = data.address.postcode ? data.address.postcode : '';
                        entity.country = (data.address.country_code.toUpperCase() in countries ? countries[data.address.country_code.toUpperCase()] : '');
                        entity.lng = data.lon;
                        entity.lat = data.lat;

                        /*if (parent.find('input[name="address"]').val() == '') {
                            entity.address = data.display_name;
                            entity.map_address = data.display_name;
                        }
                        if (!entity.city.length) {
                            entity.city = data.address.city;
                        }
                        if (!entity.state.length) {
                            entity.state = data.address.state;
                        }
                        if (!entity.postal_code.length) {
                            entity.postal_code = data.address.postcode;
                        }
                        if (!entity.country.length) {
                            entity.country = (data.address.country_code.toUpperCase() in countries ? countries[data.address.country_code.toUpperCase()] : '');
                        }*/
                    } else if (draggable) {
                        parent.find('input[name="address"]').val(data.display_name);
                        parent.find('input[name="map_address"]').val(data.display_name);
                        parent.find('input[name="city"]').val(data.address.city);
                        parent.find('input[name="state"]').val(data.address.state);
                        parent.find('input[name="postal_code"]').val(data.address.postcode);
                        parent.find('input[name="lat"]').val(data.lat);
                        parent.find('input[name="lng"]').val(data.lon);
                    }
                });
                
                this.map.flyTo([lat, lng],16);
            }
        },
        updateMarkerPosition: function(lat, lng) {
            if(this.map && this.marker && !isNaN(lat) && !isNaN(lng)) {
                this.marker.setLatLng(new L.LatLng(lat, lng));
            }
        },
        resetMarkerPosition: function(elemId) {
            var elem = $('#' + elemId),
                address = elem.data('map'),
                lat = elem.data('lat'),
                lng = elem.data('lng');
            
            if(address && lat && lng && !isNaN(lat) && !isNaN(lng)) {
                var parent = elem.parents('form:first');
                
                parent.find('input[name="map_address"]').val(address);
                parent.find('input[name="lat"]').val(lat);
                parent.find('input[name="lng"]').val(lng);
                this.toggleResetButton(true);
                this.updateMarkerPosition(lat, lng);
            }
        },
        toggleResetButton: function(hide) {
            var reset = $('#reset-marker-position');
            
            if(!hide && reset.length && reset.data('map') && reset.data('lat') && reset.data('lng')) {
                reset.show();
            } else {
                reset.hide();
            }
        },
        getKeywordAutocomplete: function(input, params) {
            var self = this;
            
            input = input instanceof $ ? input : $(input);
            params = params || {};
            
            input.keyup(function(event){
                // Ignore tab, enter, caps, end, home, arrows
                if(self.inArray(event.keyCode, [9, 13, 20, 35, 36, 37, 38, 39, 40])) return;
                
                var searchData = $.trim($(this).val());
                
                if(searchData) {
                    input.autocomplete({
                        source: function(request, response) {
                            var autocomleate = typeof(params.advanced) != 'undefined' ? self.getKeywordAutocomplete(params.advanced, request.term) : [],
                                searchType = input.data('search-type');
                            
                            $.post({
                                url: '/elasticsearch-results',
                                data: {keyword: input.val(), search_type: searchType, _token: $('[name="_token"]').val()},
                                dataType: 'json',
                                success: function (res) {
                                    var arr = res.results.hits.hits;
                                    
                                    for(var i = 0; i < arr.length; i++) {
                                        var data = {
                                            id: arr[i]._id,
                                            type: searchType,
                                            label: arr[i]._source.title,
                                            slug: arr[i]._source.slug
                                        };
                                        autocomleate.push(data);
                                    }
                                    response(autocomleate);
                                }
                            });
                        },
                        select: function(event, ui) {
                            if(params.onSelect) {
                                params.onSelect(ui.item, event, ui);
                            }
                        }
                    });
                    // Force imidiate search right after creation
                    input.autocomplete('search');
                }
            });
        },
        getLocationAutocomplete: function(input, params, onSelect) {
            var self = this,
                first = true;
        
            input = input instanceof $ ? input : $(input);
            params = params || {};
        
            input.keyup(function(event){
                // Ignore tab, enter
                if(self.inArray(event.keyCode, [9, 13])) return;
            
                var searchData = $.trim($(this).val());
            
                if(searchData) {
                    input.autocomplete({
                        source: function(request, response) {
                            var autocomleate = [];
                        
                            params['keyword'] = searchData;
                            if(searchData.length >= 2 || !isNaN(parseInt(searchData))) {
                                $.post({
                                    url: params.url,
                                    data: params,
                                    dataType: 'json',
                                    success: function (res) {
                                        var arr = res.data;
                                        for(var i = 0; i < arr.length; i++) {
                                            var result = arr[i].key_id ? {
                                                    label: arr[i].keyword,
                                                    value: arr[i].keyword,
                                                    id: arr[i].key_id
                                                } : {
                                                    label: arr[i].name,
                                                    lat: arr[i].lat,
                                                    lng: arr[i].lng,
                                                    postal_code: '',
                                                    country: arr[i].country,
                                                    iso2: arr[i].iso2,
                                                    state: arr[i].state,
                                                    city: arr[i].city,
                                                    street: '',
                                                    house: '',
                                                    other: arr[i].lat && arr[i].lng ? 1 : 0,
                                                    value: arr[i].city ? arr[i].city : arr[i].name
                                                };
                                            autocomleate.push(result);
                                        }
                                        response(autocomleate);
                                    }
                                });
                            }
                        },
                        select: function(event, ui) {
                            if(typeof(onSelect) != 'undefined') {
                                onSelect(ui.item, event, ui);
                            } else if(params.inputId) {
                                params.inputId.val(ui.item.id);
                            }
                        },
                        search: function(){$(this).addClass('ui-autocomplete-loading');},
                        response: function(){$(this).removeClass('ui-autocomplete-loading');}
                    });
                    // Force imidiate search right after creation
                    if(first) {
                        input.autocomplete('search');
                        first = false;
                    }
                }
            });
        },
        getGeocodeByLocation: function(location) {
            var self = this;
            if (location) {
                self.setGeoProvider();
            
                self.provider.search({ query: location }).then(function(results) {
                    if(results.length) {
                        //console.log(results);
                    }
                });
            }
        },
        setGeoProvider: function(params) {
            var provider_name = typeof params.provider !== 'undefined' ? params.provider : 'bing',
                provider;
            switch(provider_name) {
                case 'bing':
                    provider = new BingProvider({
                        params: {
                            key: 'AszP4vn1HkPA6-jS2TMskuanH_f6kJEnk6d6rWiku6Mg1J2KcFm6C0Wmxz35Uasz'
                        },
                    });
                    break;
                case 'esri':
                    provider = new EsriProvider();
                    break;
                case 'google':
                case 'openstreetmap':
                default:
                    provider = new OpenStreetMapProvider();
                    break;
            }
        
            this.provider = provider;
        },
        geoSearchAutocompleate: function(input, params) {
            var self = this,
                provider_name = typeof params.provider !== 'undefined' ? params.provider : 'esri',
                inputJq = input instanceof $ ? input : $(input),
                first = true;
        
            self.setGeoProvider(params);

            inputJq.on('input keydown change focus', function(event) {
                // Ignore tab, enter, caps, end, home, arrows
                if(self.inArray(event.keyCode, [9, 13, 20, 35, 36, 37, 38, 39, 40])) return;
                
                var searchData = inputJq.val();
                
                if(searchData) {
                    inputJq.autocomplete({
                        source: function(request, response) {
                            self.provider.search({ query: searchData }).then(function(results) {
                                var autocomplete = [];
                                if(results.length) {
                                    autocomplete = self.getSearchDataByProvider(results, autocomplete, provider_name);
                                }
                                response(autocomplete);
                            });
                        },                    
                        select: function(event, ui) {
                            if(params.onSelect) {
                                params.onSelect(ui.item, event, ui);
                            } else if(params.inputId) {
                                params.inputId.val(ui.item.id);
                            }
                        },
                        search: function(){$(this).addClass('ui-autocomplete-loading');},
                        response: function(){$(this).removeClass('ui-autocomplete-loading');}
                    });
                    // Force imidiate search right after creation
                    if(first) {
                        inputJq.autocomplete('search');
                        first = false;
                    }
                }
            });
        },
        getSearchDataByProvider: function(results, autocomplete, provider_name) {
            switch (provider_name) {
                case 'esri':
                    for(var i = 0; i < results.length; i++) {
                        const data = {
                            label: results[i].label,
                            lat: results[i].y,
                            lng: results[i].x,
                            postal_code: typeof results[i].address !== 'undefined' && typeof results[i].address.postalCode !== 'undefined' ? results[i].address.postalCode : '',
                            country: typeof results[i].address !== 'undefined' && typeof results[i].address.countryRegion !== 'undefined' ? results[i].address.countryRegion : '',
                            iso2: '',
                            state: typeof results[i].address !== 'undefined' && typeof results[i].address.adminDistrict !== 'undefined' ? results[i].address.adminDistrict : '',
                            city: typeof results[i].address !== 'undefined' && typeof results[i].address.locality !== 'undefined' ? results[i].address.locality : '',
                            street: '',
                            house: '',
                            other: results[i].y && results[i].x ? 1 : 0
                        };
                        autocomplete.push(data);
                    }
                    break;
                case 'bing':
                    for(var i = 0; i < results.length; i++) {
                        var raw = results[i].raw;
                        const data = {
                            label: results[i].label,
                            lat: results[i].y,
                            lng: results[i].x,
                            postal_code: typeof raw.address !== 'undefined' && typeof raw.address.postalCode !== 'undefined' ? raw.address.postalCode : '',
                            country: typeof raw.address !== 'undefined' && typeof raw.address.countryRegion !== 'undefined' ? raw.address.countryRegion : '',
                            iso2: '',
                            state: typeof raw !== 'undefined' && typeof raw.address.adminDistrict !== 'undefined' ? raw.address.adminDistrict : '',
                            city: typeof raw !== 'undefined' && typeof raw.address.locality !== 'undefined' ? raw.address.locality : '',
                            street: '',
                            house: '',
                            other: results[i].y && results[i].x ? 1 : 0
                        };
                        autocomplete.push(data);
                    }
                    break;
                case 'bing_new':
                    for(var i = 0; i < results.length; i++) {
                        var raw = results[i];
                        const data = {
                            label: raw.name,
                            lat: raw.point.coordinates[0],
                            lng: raw.point.coordinates[1],
                            postal_code: typeof raw.address !== 'undefined' && typeof raw.address.postalCode !== 'undefined' ? raw.address.postalCode : '',
                            country: typeof raw.address !== 'undefined' && typeof raw.address.countryRegion !== 'undefined' ? raw.address.countryRegion : '',
                            iso2: '',
                            state: typeof raw !== 'undefined' && typeof raw.address.adminDistrict !== 'undefined' ? raw.address.adminDistrict : '',
                            city: typeof raw !== 'undefined' && typeof raw.address.locality !== 'undefined' ? raw.address.locality : '',
                            street: '',
                            house: '',
                            other: raw.point.coordinates.length ? 1 : 0
                        };
                        autocomplete.push(data);
                    }
                    break;
                case 'google':
                case 'openstreetmap':
                default:
                    for(var i = 0; i < results.length; i++) {
                        const data = {
                            label: results[i].display_name,
                            lat: results[i].lat,
                            lng: results[i].lon,
                            postal_code: typeof results[i].address !== 'undefined' && typeof results[i].address.postcode !== 'undefined' ? results[i].address.postcode : '',
                            country: typeof results[i].address !== 'undefined' && typeof results[i].address.country !== 'undefined' ? results[i].address.country : '',
                            iso2: typeof results[i].address !== 'undefined' && typeof results[i].address.country_code !== 'undefined' ? results[i].address.country_code.toUpperCase() : '',
                            state: typeof results[i].address !== 'undefined' && typeof results[i].address.state !== 'undefined' ? results[i].address.state : '',
                            city: typeof results[i].address !== 'undefined' && typeof results[i].address.city !== 'undefined' ? results[i].address.city : '',
                            street: '',
                            house: '',
                            other: 0
                        };
                        autocomplete.push(data);
                    }
                    break;
            }
        
            return autocomplete;
        },
        geoSearchOldAutocompleate: function(input, params) {
            var self = this,
                inputJq = input instanceof $ ? input : $(input);
        
            inputJq.on('input keydown change focus', function(event) {
                if (event.keyCode === 13) {
                    event.preventDefault();
                }
            
                // $.get('https://nominatim.openstreetmap.org/search/'+inputJq.val()+'?format=jsonv2&addressdetails=1&extratags=1&namedetails=1&accept-language=en&limit=10', function(results){
                $.get('https://dev.virtualearth.net/REST/v1/Locations/?key=AszP4vn1HkPA6-jS2TMskuanH_f6kJEnk6d6rWiku6Mg1J2KcFm6C0Wmxz35Uasz&q='+inputJq.val(), function(results){
                    inputJq.autocomplete({
                        source: function(request, response) {
                            var autocomplete = [];
                        
                            if(Object.keys(results).length) {
                                var items = results.resourceSets[0].resources;
                                autocomplete = self.getSearchDataByProvider(items, autocomplete, 'bing_new');
                                response(autocomplete);
                            }
                        },
                        select: function(event, ui) {
                            if(params.onSelect) {
                                params.onSelect(ui.item, event, ui);
                            } else if(params.inputId) {
                                params.inputId.val(ui.item.id);
                            }
                        },
                        search: function(){$(this).addClass('ui-autocomplete-loading');},
                        response: function(){$(this).removeClass('ui-autocomplete-loading');}
                    });
                });
            });
        },
        geoLocationiqAutocompleate: function(input, params) {
            var self = this,
                inputJq = input instanceof $ ? input : $(input);
        
            inputJq.on('input keydown change focus', function(event) {
                if (event.keyCode === 13) {
                    event.preventDefault();
                }
            
                $.get('https://us1.locationiq.com/v1/search.php?key=b79996d1227a97&q='+inputJq.val()+'&format=json&addressdetails=1&accept-language=en&limit=10', function(results){
                    inputJq.autocomplete({
                        source: function(request, response) {
                            var autocomplete = [];
                        
                            if(results.length) {
                                for(var i = 0; i < results.length; i++) {
                                    (function(){
                                        var address = typeof results[i].address !== 'undefined' ? results[i].address : {},
                                            data = {
                                            label: results[i].display_name,
                                            lat: results[i].lat,
                                            lng: results[i].lon,
                                            postal_code: typeof address.postcode !== 'undefined' ? address.postcode : '',
                                            country: typeof address.country !== 'undefined' ? address.country : '',
                                            iso2: typeof address.country_code !== 'undefined' ? address.country_code.toUpperCase() : '',
                                            state: typeof address.state !== 'undefined' ? address.state : '',
                                            city: typeof address.city !== 'undefined' ? address.city : (typeof address.village !== 'undefined' ? address.village : ''),
                                            street: typeof address.street !== 'undefined' ? address.street : (typeof address.road !== 'undefined' ? address.road : ''),
                                            house: typeof address.house_number !== 'undefined' ? address.house_number : '',
                                            other: 0
                                        };
                                        autocomplete.push(data);
                                    })();
                                }
                                response(autocomplete);
                            }
                        },
                        select: function(event, ui) {
                            if(params.onSelect) {
                                params.onSelect(ui.item, event, ui);
                            } else if(params.inputId) {
                                params.inputId.val(ui.item.id);
                            }
                        },
                        search: function(){$(this).addClass('ui-autocomplete-loading');},
                        response: function(){$(this).removeClass('ui-autocomplete-loading');}
                    });
                });
            });
        },
        geoMapBoxAutocompleate: function(input, params) {
            var self = this,
                inputJq = input instanceof $ ? input : $(input);
        
            inputJq.on('input keydown change focus', function(event) {
                if (event.keyCode === 13) {
                    event.preventDefault();
                }
                var location = inputJq.val();
                $.get('https://api.mapbox.com/geocoding/v5/mapbox.places/'+location+'.json?language=en,es,de,fr,it&limit=10&types=place,locality&access_token=' + self.mapBoxApiKey, function(results){
                    inputJq.autocomplete({
                        source: function(request, response) {
                            var autocomplete = [];
                            if(results.features.length) {
                                results = results.features;
                                for(var i = 0; i < results.length; i++) {
                                    (function(){
                                        var data = {
                                            label: results[i].place_name,
                                            lat: results[i].center[1],
                                            lng: results[i].center[0],
                                            postal_code: typeof results[i].address !== 'undefined' && typeof results[i].address.postcode !== 'undefined' ? results[i].address.postcode : '',
                                            country: typeof results[i].address !== 'undefined' && typeof results[i].address.country !== 'undefined' ? results[i].address.country : '',
                                            iso2: typeof results[i].address !== 'undefined' && typeof results[i].address.country_code !== 'undefined' ? results[i].address.country_code.toUpperCase() : '',
                                            state: typeof results[i].address !== 'undefined' && typeof results[i].address.state !== 'undefined' ? results[i].address.state : '',
                                            city: typeof results[i].address !== 'undefined' && typeof results[i].address.city !== 'undefined' ? results[i].address.city : '',
                                            street: '',
                                            house: '',
                                            other: 0
                                        };
                                        autocomplete.push(data);
                                    })();
                                }
                                response(autocomplete);
                            }
                        },
                        select: function(event, ui) {
                            if(params.onSelect) {
                                params.onSelect(ui.item, event, ui);
                            } else if(params.inputId) {
                                params.inputId.val(ui.item.id);
                            }
                        },
                        search: function(){$(this).addClass('ui-autocomplete-loading');},
                        response: function(){$(this).removeClass('ui-autocomplete-loading');}
                    });
                });
            });
        },
        geoHereAutocompleate: function(input, params) {
            var self = this,
                inputJq = input instanceof $ ? input : $(input);
        
            inputJq.on('input keydown change focus', function(event) {
                if (event.keyCode === 13) {
                    event.preventDefault();
                }
            
                $.get('https://autocomplete.geocoder.ls.hereapi.com/6.2/suggest.json?apiKey='+self.hereApiKey
                    +'&query='+inputJq.val()+'&resultType=areas&language=en&maxresults=10', function(results){
                    inputJq.autocomplete({
                        source: function(request, response) {
                            var autocomplete = [];
                            
                            if(results.suggestions.length) {
                                for(var i = 0; i < results.suggestions.length; i++) {
                                    if (results.suggestions[i].matchLevel === 'county') {
                                        continue;
                                    }
                                    (function(){
                                        var data = {
                                            label: results.suggestions[i].label,
                                            postal_code: typeof results.suggestions[i].address.postalCode !== 'undefined' ? results.suggestions[i].address.postalCode : '',
                                            country: typeof results.suggestions[i].address.country !== 'undefined' ? results.suggestions[i].address.country : '',
                                            iso3: typeof results.suggestions[i].countryCode !== 'undefined' ? results.suggestions[i].countryCode.toUpperCase() : '',
                                            state: typeof results.suggestions[i].address.state !== 'undefined' ? results.suggestions[i].address.state : '',
                                            city: typeof results.suggestions[i].address.city !== 'undefined' ? results.suggestions[i].address.city : '',
                                            street: '',
                                            house: '',
                                            other: 0,
                                            location: results.suggestions[i].locationId
                                        };
                                    
                                        var front = [];
                                        if (data.city.length) {
                                            front.push(data.city);
                                        }
                                        if (data.state.length) {
                                            front.push(data.state);
                                        }
                                        if (data.country.length) {
                                            front.push(data.country);
                                        }
                                        data.label = front.join(', ');
                                        autocomplete.push(data);
                                    })();
                                }
                                response(autocomplete);
                            }
                        },
                        select: function(event, ui) {
                            if(params.onSelect) {
                                params.onSelect(ui.item, event, ui);
                            } else if(params.inputId) {
                                params.inputId.val(ui.item.id);
                            }
                        },
                        search: function(){$(this).addClass('ui-autocomplete-loading');},
                        response: function(){$(this).removeClass('ui-autocomplete-loading');}
                    });
                });
            });
        },
        getHereGeocodeByLocation: function(location, latInput, lngInput) {
            var self = this;
            if (location) {
                $.get('https://geocoder.ls.hereapi.com/6.2/geocode.json?apiKey='+self.hereApiKey
                    +'&locationid='+location+'&jsonattributes=1&gen=9', function(results){
                    if (results.response) {
                        var view = results.response.view;
                        if (view) {
                            latInput.val( view[0].result[0].location.displayPosition.latitude );
                            lngInput.val( view[0].result[0].location.displayPosition.longitude );
                        }
                    }
                });
            }
        },
        getUserAutocomplete: function(input, params) {
            var self = this,
                first = true;
            
            input = input instanceof $ ? input : $(input);
            params = params || {};
            
            input.keyup(function(event){
                // Ignore tab, enter, caps, end, home, arrows
                if(self.inArray(event.keyCode, [9, 13, 20, 35, 36, 37, 38, 39, 40])) return;
                
                var searchData = $.trim($(this).val());
                
                if(searchData) {
                    input.autocomplete({
                        source: function(request, response) {
                            var autocomleate = [], //typeof(params.advanced) != 'undefined' ? self.getUserAutocomplete(params.advanced, request.term) : [],
                                keyword = input.val();
                            
                            if(searchData.length >= (params.minlen ? params.minlen : 3) || !isNaN(parseInt(searchData))) {
                                $.post({
                                    url: params.url ? params.url : '/search-user',
                                    data: {keyword: searchData, role: params.role, user: params.user, _token: $('[name="_token"]').val()},
                                    dataType: 'json',
                                    success: function (res) {
                                        var arr = res.results;
                                        for(var i = 0; i < arr.length; i++) {
                                            var data = {
                                                label: arr[i].name,
                                                value: arr[i].name,
                                                id: arr[i].id
                                            };
                                            autocomleate.push(data);
                                        }
                                        response(autocomleate);
                                    }
                                });
                            }
                        },
                        select: function(event, ui) {
                            if(params.onSelect) {
                                params.onSelect(ui.item, event, ui);
                            } else if(params.inputId) {
                                params.inputId.val(ui.item.id);
                            }
                        },
                        search: function(){$(this).addClass('ui-autocomplete-loading');},
                        response: function(){$(this).removeClass('ui-autocomplete-loading');}
                    });
                    // Force imidiate search right after creation
                    if(first) {
                        input.autocomplete('search');
                        first = false;
                    }
                }
            });
        },
        getAddressKeywordsAutocomplete: function(input, params, onSelect) {
            var self = this,
                first = true;
        
            input = input instanceof $ ? input : $(input);
            params = params || {};
        
            input.keyup(function(event){
                // Ignore tab, enter, caps, end, home, arrows
                if(self.inArray(event.keyCode, [9, 13, 20, 35, 36, 37, 38, 39, 40])) return;
            
                var searchData = $.trim($(this).val());
            
                if(searchData) {
                    input.autocomplete({
                        source: function(request, response) {
                            var autocomleate = [];
                        
                            params['keyword'] = searchData;
                            params['_token'] = $('[name="_token"]').val();
                            if(searchData.length >= 3) {
                                $.post({
                                    url: '/search-address-keyword',
                                    data: params,
                                    dataType: 'json',
                                    success: function (res) {
                                        var arr = res.results ? res.results : [];
                                        for(var i = 0; i < arr.length; i++) {
                                            var result = {
                                                label: arr[i].keyword,
                                                value: arr[i].keyword,
                                                id: arr[i].key_id
                                            };
                                            autocomleate.push(result);
                                        }
                                        response(autocomleate);
                                    }
                                });
                            }
                        },
                        select: function(event, ui) {
                            if(typeof(onSelect) != 'undefined') {
                                onSelect(ui.item, event, ui);
                            } else if(params.inputId) {
                                params.inputId.val(ui.item.id);
                            }
                        },
                        search: function(){$(this).addClass('ui-autocomplete-loading');},
                        response: function(){$(this).removeClass('ui-autocomplete-loading');}
                    });
                    // Force imidiate search right after creation
                    if(first) {
                        input.autocomplete('search');
                        first = false;
                    }
                }
            });
        },
        getSimpleKeywordsAutocomplete: function(input, params, onSelect) {
            var self = this,
                first = true;
        
            input = input instanceof $ ? input : $(input);
            params = params || {};
        
            input.keyup(function(event){
                // Ignore tab, enter, caps, end, home, arrows
                if(self.inArray(event.keyCode, [9, 13, 20, 35, 36, 37, 38, 39, 40])) return;
            
                var searchData = $.trim($(this).val());
            
                if(searchData) {
                    input.autocomplete({
                        source: function(request, response) {
                            var autocomleate = [];
                        
                            params['keyword'] = searchData;
                            params['_token'] = $('[name="_token"]').val();
                            if(searchData.length >= 3) {
                                $.post({
                                    url: '/search-simple-keyword',
                                    data: params,
                                    dataType: 'json',
                                    success: function (res) {
                                        var arr = res.results ? res.results : [];
                                        for(var i = 0; i < arr.length; i++) {
                                            var result = {
                                                label: arr[i].keyword,
                                                value: arr[i].keyword,
                                                id: arr[i].key_id
                                            };
                                            autocomleate.push(result);
                                        }
                                        response(autocomleate);
                                    }
                                });
                            }
                        },
                        select: function(event, ui) {
                            if(typeof(onSelect) != 'undefined') {
                                onSelect(ui.item, event, ui);
                            } else if(params.inputId) {
                                params.inputId.val(ui.item.id);
                            }
                        },
                        search: function(){$(this).addClass('ui-autocomplete-loading');},
                        response: function(){$(this).removeClass('ui-autocomplete-loading');}
                    });
                    // Force imidiate search right after creation
                    if(first) {
                        input.autocomplete('search');
                        first = false;
                    }
                }
            });
        },
        getVideoType: function(video_link){
            if (typeof video_link === 'undefined') {
                return 'video/mp4';
            }
    
            if (video_link.length == 0) {
                return 'video/mp4';
            }
        
            var video = video_link.split('.'),
                type = video.slice(-1)[0],
                output = 'video/';
        
            switch (type) {
                case 'avi':
                    output += 'x-msvideo';
                    break;
                case 'wmv':
                    output += 'x-ms-wmv';
                    break;
                case '3gp':
                    output += '3gpp';
                    break;
                default:
                    output += type;
                    break;
            }
        
            return output;
        },
        getValue: function(value, path) {
            path = path ? path : [];

            if(typeof value == 'object' && value) {
                var p = this.params,
                    i;
                
                for(i = 0; i < path.length; i++) {
                    p = p[path[i]] ? p[path[i]] : p;
                }
                for(i = 0; i < value.length; i++) {
                    p = p[value[i]] ? p[value[i]] : '';
                }
                value = p;
            }
            return value;
        },
        getValueForSelect: function(value, options, valReady, optLabel, optId) {
            var val = valReady ? value : this.getValue(value),
                opts = this.getOptions(options, optLabel, optId),
                res = [];
            
            val = typeof val == 'object' ? val : [val];

            for(var i = 0; i < opts.length; i++) {
                if(this.inArray(opts[i].value, val)) {
                    res.push(opts[i]);
                }
            }
            return res;
        },
        getOptions: function(options, optLabel, optId) {
            var optionsList = [],
                optLabel = typeof optLabel == 'undefined' ? 'label' : optLabel,
                optId = typeof optId == 'undefined' ? 'id' : optId;
            
            for(var i in options) {
                if(typeof(options[i]) == 'object') optionsList.push({label: this.htmlDecode(options[i][optLabel]), value: options[i][optId]});
                else optionsList.push({label: this.htmlDecode(options[i]), value: i});
            }
            return optionsList;
        },
        getOptionsSort: function(options, sort) {
            var optionsList = [],
                i;
            
            for(var j in sort) {
                i = sort[j];
                optionsList.push({label: options[i], value: i});
            }
            return optionsList;
        },
        htmlDecode: function(value) {
            return $("<div/>").html(value).text();
        },
        updateMultiselect: function(val) {
            var newValue = [];
            
            for(var i = 0; i < val.length; i++) {
                newValue.push(val[i].value);
            }
            $(this.$el).find('.v-select').parent().find('input[type="hidden"]').val(newValue);
        },
        updateCurrentMultiselect: function(id) {
            var select = $('#' + id),
                val = select.length ? select[0].__vue__.valueAsArray : [];

            var newValue = [];
            
            for(var i = 0; i < val.length; i++) {
                newValue.push(val[i].value);
            }
            select.parent().find('input[type="hidden"]').val(newValue);
        },        
        inArray: function(needle, haystack) {
            var length = haystack.length;
            
            for(var i = 0; i < length; i++) {
                if(haystack[i] == needle) return true;
            }
            return false;
        },
        getUrlParams: function() {
            var queryStr = window.location.search,
                queryParams = [];
            
            if(queryStr) {
                var queryArr = queryStr.replace('?', '').split('&');
                
                for(var i = 0; i < queryArr.length; i++) {
                    var paramArr = queryArr[i].split('=');
                    
                    queryParams[decodeURIComponent(paramArr[0])] = decodeURIComponent(paramArr[1].replace(/\+/g, ' '));
                }
            }
            return queryParams;
        },
        setUrlParams: function(url, paramsArr){
            var pairsArr = [],
                iter = 0,
                params;
            
            for(var i in paramsArr) {
                pairsArr[iter] = i + '=' + paramsArr[i];
                iter++;
            }
            params = pairsArr.join('&');
            params = params ? '?' + params : params;
            return url + params;
        },
        modifyUrl: function(param, value, addParams) {
            var paramsArr = this.getUrlParams();
            if (param == 'category') delete paramsArr['fcategory'];
            
            if(value) {
                paramsArr[param] = value;
            } else {
                delete paramsArr[param];
            }
            if (typeof addParams !== undefined) {
                if (addParams) {
                    var addParamsKeys = Object.keys(addParams);
                    for (var iParam = 0; iParam < addParamsKeys.length; iParam++) {
                        if (addParams[addParamsKeys[iParam]]) {
                            paramsArr[addParamsKeys[iParam]] = addParams[addParamsKeys[iParam]];
                        } else {
                            delete paramsArr[addParamsKeys[iParam]];
                        }
                    }
                }
            }
            return this.setUrlParams(window.location.origin + window.location.pathname, paramsArr);
        },
        urlGetParam: function(param, defParam) {
            var paramsArr = this.getUrlParams();
            
            return typeof paramsArr[param] != 'undefined' ? paramsArr[param] : (typeof defParam != 'undefined' && paramsArr[defParam] != 'undefined' ? paramsArr[defParam] : null);
        },
        urlHasParam: function(param, value) {
            var paramsArr = this.getUrlParams(),
                result = typeof paramsArr[param] != 'undefined';
            
            if(value) {
                result = result && paramsArr[param] == value;
            }
            return result;
        },
        resizeSlider: function(width, height) {
            var w  = (width - 32) + 'px',
                t = $('.owl-thumbs').height(),
                h  = (height - 75 - t) + 'px';
            
            $('.owl-theme').css({'max-width': w, 'max-height': h});
            $('.owl-theme .item').css({'width': w, 'height': h});
            //$('.owl-theme .owl-item').css({'width': (width - 15) + 'px'});
            $('.owl-theme .item video').css({'max-width': w, 'max-height': h});
        },
        setSearchLocation: function(add) {
            var location = this.urlGetParam('search_location');
            return location == null || location == '' ? '' : ' ' + add + ' ' + location;
        },
        ucFirst: function(string) {
            return string ? string.charAt(0).toUpperCase() + string.slice(1) : string;
        },
        route: function(name, params) {
            var urls = jsvars && jsvars.routesList ? jsvars.routesList : [],
                url = urls[name] ? urls[name] : urls['home'],
                clearRegexp = new RegExp('(/\{.*\})', 'g');
            
            for(var i in params) {
                var regexp = new RegExp('(\{' + i + '[\?]?\})', 'g');
                
                url = url.replace(regexp, params[i]);
            }
            url = url.replace(clearRegexp, '');
            
            return url;
        },
        toggleBulkEditEntity: function(event) {
            var bulk = $(event.target);
            var items = $('.bulkEditEntity');
            if (bulk.prop('checked')) {
                items.prop('checked', true);
            } else {
                items.prop('checked', false);
            }
        },
        toggleBulkEditItems: function(event) {
            var bulk = $(event.target);
            var items = $('.bulkEditItems');
            if (bulk.prop('checked')) {
                items.prop('checked', true);
            } else {
                items.prop('checked', false);
            }
        },
        showFilterChildrenCats: function(cat_id, event) {
            $('[data-parent="'+cat_id+'"]').toggleClass('open');
            $(event.target).toggleClass('active');
        },
        //getNewPage: function(path) {
        //	var self = this;
        //
        //	axios.get(self.route(self.params.route_name + '.api', { 'params': '?page=' + self.entities_list.current_page + '&order_by=' + self.currentSortOrder }))
        //		.then(function(response) {
        //			var result = response.data;
        //
        //			for(var i = 0; i < path.length; i++) {
        //				result = result[path[i]] ? result[path[i]] : result;
        //			}
        //			self.entities_list = result;
        //		})
        //		.catch(function() {
        //			console.log('Handle server error from here');
        //		});
        //},
    }
});

Vue.prototype.trans = function(string, args) {
    var value = _.get(window.i18n[jsvars.curLang], string);
    
    _.eachRight(args, function(paramVal, paramKey) {
        value = _.replace(value, `:${paramKey}`, paramVal);
    });
    return typeof(value) == 'undefined' ? string : value;
};

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
Vue.component('html-element', require('./components/partials/HtmlElementsComponent.vue').default);
Vue.component('file-uploader', require('./components/partials/FileUploaderComponent.vue').default);
Vue.component('pagination', require('./components/partials/PaginationComponent.vue').default);
Vue.component('sort-order-selectbox', require('./components/partials/SortOrderSelectboxComponent.vue').default);
Vue.component('message-bar', require('./components/partials/MessageBarComponent.vue').default);
Vue.component('consents-dialog', require('./components/partials/ConsentsDialogComponent.vue').default);
Vue.component('address-keywords', require('./components/partials/AddressKeywordsComponent.vue').default);
Vue.component('simple-keywords', require('./components/partials/SimpleKeywordsComponent.vue').default);
Vue.component('add-address-keyword', require('./components/partials/AddAddressKeywordFormComponent.vue').default);
Vue.component('add-simple-keyword', require('./components/partials/AddSimpleKeywordFormComponent.vue').default);

Vue.component('index-component', require('./components/frontend/IndexComponent.vue').default);
Vue.component('login', require('./components/frontend/auth/LoginComponent.vue').default);
Vue.component('register', require('./components/frontend/auth/RegisterComponent.vue').default);
Vue.component('password-forgot', require('./components/frontend/auth/PasswordForgotComponent.vue').default);

Vue.component('user-profile', require('./components/backend/profile/UserProfileComponent.vue').default);
Vue.component('user-profile-profile', require('./components/backend/profile/partials/ProfileComponent.vue').default);
Vue.component('user-profile-agents', require('./components/backend/profile/partials/AgentListComponent.vue').default);
Vue.component('user-profile-properties', require('./components/backend/profile/partials/PropertyListComponent.vue').default);
Vue.component('user-profile-arts', require('./components/backend/profile/partials/ArtListComponent.vue').default);
Vue.component('user-profile-designs', require('./components/backend/profile/partials/DesignListComponent.vue').default);
Vue.component('user-profile-franchises', require('./components/backend/profile/partials/FranchiseListComponent.vue').default);
Vue.component('user-profile-features', require('./components/backend/profile/partials/FeatureListComponent.vue').default);
Vue.component('feature-edit-admin', require('./components/backend/profile/partials/FeatureEditComponent.vue').default);
Vue.component('admin-profile-parsers', require('./components/backend/profile/partials/ParserListComponent.vue').default);
Vue.component('admin-profile-pages', require('./components/backend/profile/partials/PageListComponent.vue').default);
Vue.component('admin-page-edit', require('./components/backend/profile/partials/PageEditComponent.vue').default);
Vue.component('admin-home-edit', require('./components/backend/profile/partials/HomeEditComponent.vue').default);
Vue.component('admin-footer-edit', require('./components/backend/profile/partials/FooterEditComponent.vue').default);
Vue.component('admin-ad-partners', require('./components/backend/profile/partials/PartnerListComponent.vue').default);
Vue.component('admin-partner-edit', require('./components/backend/profile/partials/PartnerEditComponent.vue').default);
Vue.component('admin-ad-users', require('./components/backend/profile/partials/AdUserListComponent.vue').default);
Vue.component('admin-ad-user-edit', require('./components/backend/profile/partials/AdUserEditComponent.vue').default);
Vue.component('user-profile-professions', require('./components/backend/profile/partials/ProfessionListComponent.vue').default);
Vue.component('user-profile-users', require('./components/backend/profile/partials/UserListComponent.vue').default);
Vue.component('user-profile-jobCategories', require('./components/backend/profile/partials/JobCategoryListComponent.vue').default);
Vue.component('jobcategory-edit-admin', require('./components/backend/profile/partials/JobCategoryEditComponent.vue').default);
Vue.component('user-profile-jobEntities', require('./components/backend/profile/partials/JobEntityListComponent.vue').default);
Vue.component('profession-edit-admin', require('./components/backend/profile/partials/ProfessionEditComponent.vue').default);
Vue.component('user-profile-saved-searches', require('./components/backend/profile/partials/SavedSearchesComponent.vue').default);
Vue.component('admin-emails-settings', require('./components/backend/profile/partials/EmailSettingsComponent.vue').default);
Vue.component('admin-emails-template', require('./components/backend/profile/partials/EmailTemplateComponent.vue').default);
Vue.component('admin-emails-log', require('./components/backend/profile/partials/EmailLogComponent.vue').default);
Vue.component('admin-db-importer', require('./components/backend/profile/partials/DbImporterComponent.vue').default);
Vue.component('user-import-links', require('./components/backend/profile/partials/ImportLinksComponent.vue').default);
Vue.component('user-import-runs', require('./components/backend/profile/partials/ImportRunsComponent.vue').default);
Vue.component('user-import-log', require('./components/backend/profile/partials/ImportLogComponent.vue').default);

Vue.component('property-list-frontend', require('./components/frontend/property/PropertyListComponent.vue').default);
Vue.component('property-list-frontend-list-item', require('./components/frontend/property/PropertyListItemComponent.vue').default);
Vue.component('property-view-frontend', require('./components/frontend/property/PropertyViewComponent.vue').default);
Vue.component('property-view-frontend-header', require('./components/frontend/property/partials/PropertyHeaderComponent.vue').default);
Vue.component('property-view-frontend-description', require('./components/frontend/property/partials/PropertyDescriptionComponent.vue').default);
Vue.component('property-view-frontend-contacts', require('./components/frontend/property/partials/PropertyContactsComponent.vue').default);
Vue.component('property-view-frontend-property-similar', require('./components/frontend/property/partials/PropertySimilarComponent.vue').default);
Vue.component('property-view-frontend-sidebar', require('./components/frontend/property/partials/PropertySidebarComponent.vue').default);
Vue.component('property-view-frontend-actions', require('./components/frontend/property/partials/ActionsComponent.vue').default);
Vue.component('property-view-frontend-price', require('./components/frontend/property/partials/PriceComponent.vue').default);
Vue.component('property-view-frontend-labels', require('./components/frontend/property/partials/LabelsComponent.vue').default);
Vue.component('property-view-frontend-details', require('./components/frontend/property/partials/DetailsComponent.vue').default);

Vue.component('art-list-frontend', require('./components/frontend/art/ArtListComponent.vue').default);
Vue.component('art-list-frontend-list-item', require('./components/frontend/art/ArtListItemComponent.vue').default);
Vue.component('art-view-frontend', require('./components/frontend/art/ArtViewComponent.vue').default);
Vue.component('art-view-frontend-header', require('./components/frontend/art/partials/ArtHeaderComponent.vue').default);
Vue.component('art-view-frontend-description', require('./components/frontend/art/partials/ArtDescriptionComponent.vue').default);
Vue.component('art-view-frontend-contacts', require('./components/frontend/art/partials/ArtContactsComponent.vue').default);
Vue.component('art-view-frontend-property-similar', require('./components/frontend/art/partials/ArtSimilarComponent.vue').default);
Vue.component('art-view-frontend-sidebar', require('./components/frontend/art/partials/ArtSidebarComponent.vue').default);
Vue.component('art-view-frontend-actions', require('./components/frontend/art/partials/ActionsComponent.vue').default);
Vue.component('art-view-frontend-price', require('./components/frontend/art/partials/PriceComponent.vue').default);
Vue.component('art-view-frontend-labels', require('./components/frontend/art/partials/LabelsComponent.vue').default);
Vue.component('art-filters-widget', require('./components/frontend/widgets/ArtListFiltersComponent.vue').default);

Vue.component('franchise-list-frontend', require('./components/frontend/franchise/FranchiseListComponent.vue').default);
Vue.component('franchise-list-frontend-list-item', require('./components/frontend/franchise/FranchiseListItemComponent.vue').default);
Vue.component('franchise-view-frontend', require('./components/frontend/franchise/FranchiseViewComponent.vue').default);
Vue.component('franchise-view-frontend-detail', require('./components/frontend/franchise/partials/FranchiseViewDetailComponent.vue').default);
Vue.component('franchise-view-frontend-tabs', require('./components/frontend/franchise/partials/FranchiseViewTabsComponent.vue').default);

Vue.component('jobentity-list-frontend', require('./components/frontend/jobEntity/JobEntityListComponent.vue').default);
Vue.component('jobentity-list-frontend-list-item', require('./components/frontend/jobEntity/JobEntityListItemComponent.vue').default);
Vue.component('jobentity-view-frontend', require('./components/frontend/jobEntity/JobEntityViewComponent.vue').default);
Vue.component('jobentity-view-frontend-header', require('./components/frontend/jobEntity/partials/JobEntityHeaderComponent.vue').default);
Vue.component('jobentity-view-frontend-description', require('./components/frontend/jobEntity/partials/JobEntityDescriptionComponent.vue').default);
Vue.component('jobentity-view-frontend-contacts', require('./components/frontend/jobEntity/partials/JobEntityContactsComponent.vue').default);
Vue.component('jobentity-view-frontend-sidebar', require('./components/frontend/jobEntity/partials/JobEntitySidebarComponent.vue').default);
Vue.component('jobentity-view-frontend-actions', require('./components/frontend/jobEntity/partials/ActionsComponent.vue').default);
Vue.component('jobentity-view-frontend-price', require('./components/frontend/jobEntity/partials/PriceComponent.vue').default);
Vue.component('jobentity-view-frontend-labels', require('./components/frontend/jobEntity/partials/LabelsComponent.vue').default);

Vue.component('jobentity-edit-admin', require('./components/backend/jobEntity/JobEntityEditComponent.vue').default);
Vue.component('jobentity-edit-admin-description', require('./components/backend/jobEntity/partials/JobEntityDescriptionComponent.vue').default);
Vue.component('jobentity-edit-admin-images', require('./components/backend/jobEntity/partials/JobEntityImagesComponent.vue').default);
Vue.component('jobentity-edit-admin-location', require('./components/backend/jobEntity/partials/JobEntityLocationComponent.vue').default);

Vue.component('impression-list-frontend', require('./components/frontend/impression/ImpressionListComponent.vue').default);
Vue.component('impression-list-frontend-list-item', require('./components/frontend/impression/ImpressionListItemComponent.vue').default);
Vue.component('impession-popup-view', require('./components/frontend/impression/ImpressionPopupComponent.vue').default);

Vue.component('property-edit-admin', require('./components/backend/property/PropertyEditComponent.vue').default);
Vue.component('property-edit-admin-description', require('./components/backend/property/partials/PropertyDescriptionComponent.vue').default);
Vue.component('property-edit-admin-images', require('./components/backend/property/partials/PropertyImagesComponent.vue').default);
Vue.component('property-edit-admin-details', require('./components/backend/property/partials/PropertyDetailsComponent.vue').default);
Vue.component('property-edit-admin-features', require('./components/backend/property/partials/PropertyFeaturesComponent.vue').default);
Vue.component('property-edit-admin-location', require('./components/backend/property/partials/PropertyLocationComponent.vue').default);
Vue.component('property-edit-admin-floor-plan', require('./components/backend/property/partials/PropertyFloorPlanComponent.vue').default);
Vue.component('property-filters-widget', require('./components/frontend/widgets/PropertyListFiltersComponent.vue').default);

Vue.component('user-profile-propertyCategories', require('./components/backend/profile/partials/PropertyCategoryListComponent.vue').default);
Vue.component('property-category-edit-admin', require('./components/backend/profile/partials/PropertyCategoryEditComponent.vue').default);
Vue.component('user-profile-property-category-child', require('./components/backend/profile/partials/PropertyCategoryListChildComponent.vue').default);
Vue.component('property-edit-admin-categories', require('./components/backend/property/partials/PropertyCategoriesComponent.vue').default);
Vue.component('property-edit-admin-add-category', require('./components/backend/property/partials/AddPropertyCategoryFormComponent.vue').default);

Vue.component('art-edit-admin', require('./components/backend/art/ArtEditComponent.vue').default);
Vue.component('art-edit-admin-images', require('./components/backend/art/partials/ArtImagesComponent.vue').default);
Vue.component('art-edit-admin-location', require('./components/backend/art/partials/ArtLocationComponent.vue').default);

Vue.component('user-profile-artCategories', require('./components/backend/profile/partials/ArtCategoryListComponent.vue').default);
Vue.component('art-category-edit-admin', require('./components/backend/profile/partials/ArtCategoryEditComponent.vue').default);
Vue.component('user-profile-art-category-child', require('./components/backend/profile/partials/ArtCategoryListChildComponent.vue').default);
Vue.component('art-edit-admin-categories', require('./components/backend/art/partials/ArtCategoriesComponent.vue').default);
Vue.component('art-edit-admin-add-category', require('./components/backend/art/partials/AddArtCategoryFormComponent.vue').default);

/*Vue.component('artist-view-frontend', require('./components/frontend/artists/ArtistViewComponent.vue').default);
Vue.component('artist-view-frontend-detail', require('./components/frontend/artists/partials/ArtistViewDetailComponent.vue').default);
Vue.component('artist-view-frontend-tabs', require('./components/frontend/artists/partials/ArtistViewTabsComponent.vue').default);
Vue.component('gallery-view-frontend', require('./components/frontend/artists/ArtistViewComponent.vue').default);*/

Vue.component('franchise-edit-admin', require('./components/backend/franchise/FranchiseEditComponent.vue').default);
Vue.component('franchise-edit-admin-images', require('./components/backend/franchise/partials/FranchiseImagesComponent.vue').default);
Vue.component('franchise-edit-admin-location', require('./components/backend/franchise/partials/FranchiseLocationComponent.vue').default);

Vue.component('agency-list-frontend', require('./components/frontend/agencies/AgencyListComponent.vue').default);
Vue.component('agency-list-frontend-list-item', require('./components/frontend/agencies/AgencyListItemComponent.vue').default);
Vue.component('agency-view-frontend', require('./components/frontend/agencies/AgencyViewComponent.vue').default);
Vue.component('agency-view-frontend-detail', require('./components/frontend/agencies/partials/AgencyViewDetailComponent.vue').default);
Vue.component('agency-view-frontend-tabs', require('./components/frontend/agencies/partials/AgencyViewTabsComponent.vue').default);

Vue.component('agent-list-frontend', require('./components/frontend/agents/AgentListComponent.vue').default);
Vue.component('agent-list-frontend-list-item', require('./components/frontend/agents/AgentListItemComponent.vue').default);
Vue.component('agent-view-frontend', require('./components/frontend/agents/AgentViewComponent.vue').default);
Vue.component('agent-view-frontend-detail', require('./components/frontend/agents/partials/AgentViewDetailComponent.vue').default);

Vue.component('header-media', require('./components/frontend/partials/HeaderMediaComponent.vue').default);
Vue.component('footer-media', require('./components/frontend/partials/FooterMediaComponent.vue').default);
Vue.component('page-breadcrumbs', require('./components/frontend/partials/PageBreadcrumbsComponent.vue').default);
Vue.component('search-bar', require('./components/frontend/partials/SearchBarComponent.vue').default);
Vue.component('page-header', require('./components/frontend/partials/PageHeaderComponent.vue').default);
Vue.component('save-search-results', require('./components/frontend/partials/SaveSearchResultsComponent.vue').default);
Vue.component('user-contacts-list', require('./components/frontend/partials/UserContactsListComponent.vue').default);
Vue.component('user-social-networks-list', require('./components/frontend/partials/UserSocialNetworksComponent.vue').default);

Vue.component('professions-users-widget', require('./components/frontend/widgets/ProfessionsUsersWidgetComponent.vue').default);
Vue.component('property-contacts-widget', require('./components/frontend/widgets/PropertyContactsWidgetComponent.vue').default);
Vue.component('art-contacts-widget', require('./components/frontend/widgets/ArtContactsWidgetComponent.vue').default);
Vue.component('jobentity-contacts-widget', require('./components/frontend/widgets/JobEntityContactsWidgetComponent.vue').default);
Vue.component('user-social-networks-widget', require('./components/frontend/widgets/SocialNetworksWidgetComponent.vue').default);
Vue.component('recaptcha', require('./components/frontend/partials/Recaptcha.vue').default);

Vue.component('admin-profile-quotes', require('./components/backend/profile/partials/QuoteListComponent.vue').default);
Vue.component('user-profile-quotesRequests', require('./components/backend/profile/partials/QuotesRequestListComponent.vue').default);
Vue.component('quotesrequest-edit-admin', require('./components/backend/quotesRequest/QuotesRequestEditComponent.vue').default);
Vue.component('quotes-bar', require('./components/frontend/partials/QuotesBarComponent.vue').default);

Vue.component('user-profile-ads', require('./components/backend/profile/partials/AdsListComponent.vue').default);
Vue.component('ads-edit-admin', require('./components/backend/ads/AdsEditComponent.vue').default);
Vue.component('ads', require('./components/frontend/partials/AdsComponent.vue').default);

Vue.component('user-profile-productCategories', require('./components/backend/profile/partials/ProductCategoryListComponent.vue').default);
Vue.component('productcategory-edit-admin', require('./components/backend/profile/partials/ProductCategoryEditComponent.vue').default);
Vue.component('user-profile-product-category-child', require('./components/backend/profile/partials/ProductCategoryListChildComponent.vue').default);

Vue.component('user-profile-products', require('./components/backend/profile/partials/ProductListComponent.vue').default);
Vue.component('product-list-frontend', require('./components/frontend/product/ProductListComponent.vue').default);
Vue.component('product-list-frontend-list-item', require('./components/frontend/product/ProductListItemComponent.vue').default);
Vue.component('product-view-frontend', require('./components/frontend/product/ProductViewComponent.vue').default);
Vue.component('product-view-frontend-header', require('./components/frontend/product/partials/ProductHeaderComponent.vue').default);
Vue.component('product-view-frontend-description', require('./components/frontend/product/partials/ProductDescriptionComponent.vue').default);
Vue.component('product-view-frontend-contacts', require('./components/frontend/product/partials/ProductContactsComponent.vue').default);
Vue.component('product-view-frontend-product-similar', require('./components/frontend/product/partials/ProductSimilarComponent.vue').default);
Vue.component('product-view-frontend-sidebar', require('./components/frontend/product/partials/ProductSidebarComponent.vue').default);
Vue.component('product-view-frontend-actions', require('./components/frontend/product/partials/ActionsComponent.vue').default);
Vue.component('product-view-frontend-price', require('./components/frontend/product/partials/PriceComponent.vue').default);
Vue.component('product-view-frontend-labels', require('./components/frontend/product/partials/LabelsComponent.vue').default);
Vue.component('product-contacts-widget', require('./components/frontend/widgets/ProductContactsWidgetComponent.vue').default);
Vue.component('product-filters-widget', require('./components/frontend/widgets/ProductListFiltersComponent.vue').default);
//Vue.component('product-category-recursive', require('./components/frontend/widgets/ProductCategoryRecursiveComponent.vue').default);

Vue.component('product-edit-admin', require('./components/backend/product/ProductEditComponent.vue').default);
Vue.component('product-edit-admin-images', require('./components/backend/product/partials/ProductImagesComponent.vue').default);
Vue.component('product-edit-admin-location', require('./components/backend/product/partials/ProductLocationComponent.vue').default);
Vue.component('product-edit-admin-categories', require('./components/backend/product/partials/ProductCategoriesComponent.vue').default);
Vue.component('product-edit-admin-add-category', require('./components/backend/product/partials/AddProductCategoryFormComponent.vue').default);

Vue.component('user-profile-wineCategories', require('./components/backend/profile/partials/WineCategoryListComponent.vue').default);
Vue.component('winecategory-edit-admin', require('./components/backend/profile/partials/WineCategoryEditComponent.vue').default);
Vue.component('user-profile-wine-category-child', require('./components/backend/profile/partials/WineCategoryListChildComponent.vue').default);

Vue.component('user-profile-wines', require('./components/backend/profile/partials/WineListComponent.vue').default);
Vue.component('wine-list-frontend', require('./components/frontend/wine/WineListComponent.vue').default);
Vue.component('wine-list-frontend-list-item', require('./components/frontend/wine/WineListItemComponent.vue').default);
Vue.component('wine-view-frontend', require('./components/frontend/wine/WineViewComponent.vue').default);
Vue.component('wine-view-frontend-header', require('./components/frontend/wine/partials/WineHeaderComponent.vue').default);
Vue.component('wine-view-frontend-description', require('./components/frontend/wine/partials/WineDescriptionComponent.vue').default);
Vue.component('wine-view-frontend-contacts', require('./components/frontend/wine/partials/WineContactsComponent.vue').default);
Vue.component('wine-view-frontend-wine-similar', require('./components/frontend/wine/partials/WineSimilarComponent.vue').default);
Vue.component('wine-view-frontend-sidebar', require('./components/frontend/wine/partials/WineSidebarComponent.vue').default);
Vue.component('wine-view-frontend-actions', require('./components/frontend/wine/partials/ActionsComponent.vue').default);
Vue.component('wine-view-frontend-price', require('./components/frontend/wine/partials/PriceComponent.vue').default);
Vue.component('wine-view-frontend-labels', require('./components/frontend/wine/partials/LabelsComponent.vue').default);
Vue.component('wine-contacts-widget', require('./components/frontend/widgets/WineContactsWidgetComponent.vue').default);
Vue.component('wine-filters-widget', require('./components/frontend/widgets/WineListFiltersComponent.vue').default);

Vue.component('wine-edit-admin', require('./components/backend/wine/WineEditComponent.vue').default);
Vue.component('wine-edit-admin-images', require('./components/backend/wine/partials/WineImagesComponent.vue').default);
Vue.component('wine-edit-admin-location', require('./components/backend/wine/partials/WineLocationComponent.vue').default);
Vue.component('wine-edit-admin-categories', require('./components/backend/wine/partials/WineCategoriesComponent.vue').default);
Vue.component('wine-edit-admin-add-category', require('./components/backend/wine/partials/AddWineCategoryFormComponent.vue').default);

Vue.component('user-profile-news', require('./components/backend/profile/partials/NewsListComponent.vue').default);
Vue.component('news-list-frontend', require('./components/frontend/news/NewsListComponent.vue').default);
Vue.component('news-list-frontend-list-item', require('./components/frontend/news/NewsListItemComponent.vue').default);
Vue.component('news-view-frontend', require('./components/frontend/news/NewsViewComponent.vue').default);
Vue.component('news-view-frontend-header', require('./components/frontend/news/partials/NewsHeaderComponent.vue').default);
Vue.component('news-view-frontend-description', require('./components/frontend/news/partials/NewsDescriptionComponent.vue').default);
Vue.component('news-view-frontend-post', require('./components/frontend/news/partials/NewsPostComponent.vue').default);
//Vue.component('news-view-frontend-contacts', require('./components/frontend/news/partials/NewsContactsComponent.vue').default);
//Vue.component('news-view-frontend-news-similar', require('./components/frontend/news/partials/NewsSimilarComponent.vue').default);
Vue.component('news-view-frontend-actions', require('./components/frontend/news/partials/ActionsComponent.vue').default);
Vue.component('news-view-frontend-labels', require('./components/frontend/news/partials/LabelsComponent.vue').default);
//Vue.component('news-contacts-widget', require('./components/frontend/widgets/NewsContactsWidgetComponent.vue').default);
//Vue.component('news-filters-widget', require('./components/frontend/widgets/NewsListFiltersComponent.vue').default);

Vue.component('news-edit-admin', require('./components/backend/news/NewsEditComponent.vue').default);
Vue.component('news-edit-admin-images', require('./components/backend/news/partials/NewsImagesComponent.vue').default);
//Vue.component('news-edit-admin-location', require('./components/backend/news/partials/NewsLocationComponent.vue').default);

Vue.component('user-profile-furnitureCategories', require('./components/backend/profile/partials/FurnitureCategoryListComponent.vue').default);
Vue.component('furniturecategory-edit-admin', require('./components/backend/profile/partials/FurnitureCategoryEditComponent.vue').default);
Vue.component('user-profile-furniture-category-child', require('./components/backend/profile/partials/FurnitureCategoryListChildComponent.vue').default);

Vue.component('user-profile-furnitures', require('./components/backend/profile/partials/FurnitureListComponent.vue').default);
Vue.component('furniture-list-frontend', require('./components/frontend/furniture/FurnitureListComponent.vue').default);
Vue.component('furniture-list-frontend-list-item', require('./components/frontend/furniture/FurnitureListItemComponent.vue').default);
Vue.component('furniture-view-frontend', require('./components/frontend/furniture/FurnitureViewComponent.vue').default);
Vue.component('furniture-view-frontend-header', require('./components/frontend/furniture/partials/FurnitureHeaderComponent.vue').default);
Vue.component('furniture-view-frontend-description', require('./components/frontend/furniture/partials/FurnitureDescriptionComponent.vue').default);
Vue.component('furniture-view-frontend-contacts', require('./components/frontend/furniture/partials/FurnitureContactsComponent.vue').default);
Vue.component('furniture-view-frontend-furniture-similar', require('./components/frontend/furniture/partials/FurnitureSimilarComponent.vue').default);
Vue.component('furniture-view-frontend-sidebar', require('./components/frontend/furniture/partials/FurnitureSidebarComponent.vue').default);
Vue.component('furniture-view-frontend-actions', require('./components/frontend/furniture/partials/ActionsComponent.vue').default);
Vue.component('furniture-view-frontend-price', require('./components/frontend/furniture/partials/PriceComponent.vue').default);
Vue.component('furniture-view-frontend-labels', require('./components/frontend/furniture/partials/LabelsComponent.vue').default);
Vue.component('furniture-contacts-widget', require('./components/frontend/widgets/FurnitureContactsWidgetComponent.vue').default);
Vue.component('furniture-filters-widget', require('./components/frontend/widgets/FurnitureListFiltersComponent.vue').default);

Vue.component('furniture-edit-admin', require('./components/backend/furniture/FurnitureEditComponent.vue').default);
Vue.component('furniture-edit-admin-images', require('./components/backend/furniture/partials/FurnitureImagesComponent.vue').default);
Vue.component('furniture-edit-admin-location', require('./components/backend/furniture/partials/FurnitureLocationComponent.vue').default);
Vue.component('furniture-edit-admin-categories', require('./components/backend/furniture/partials/FurnitureCategoriesComponent.vue').default);
Vue.component('furniture-edit-admin-add-category', require('./components/backend/furniture/partials/AddFurnitureCategoryFormComponent.vue').default);


Vue.component('user-profile-goodCategories', require('./components/backend/profile/partials/GoodCategoryListComponent.vue').default);
Vue.component('goodcategory-edit-admin', require('./components/backend/profile/partials/GoodCategoryEditComponent.vue').default);
Vue.component('user-profile-good-category-child', require('./components/backend/profile/partials/GoodCategoryListChildComponent.vue').default);

Vue.component('user-profile-goods', require('./components/backend/profile/partials/GoodListComponent.vue').default);
Vue.component('good-list-frontend', require('./components/frontend/good/GoodListComponent.vue').default);
Vue.component('good-list-frontend-list-item', require('./components/frontend/good/GoodListItemComponent.vue').default);
Vue.component('good-view-frontend', require('./components/frontend/good/GoodViewComponent.vue').default);
Vue.component('good-view-frontend-header', require('./components/frontend/good/partials/GoodHeaderComponent.vue').default);
Vue.component('good-view-frontend-description', require('./components/frontend/good/partials/GoodDescriptionComponent.vue').default);
Vue.component('good-view-frontend-contacts', require('./components/frontend/good/partials/GoodContactsComponent.vue').default);
Vue.component('good-view-frontend-good-similar', require('./components/frontend/good/partials/GoodSimilarComponent.vue').default);
Vue.component('good-view-frontend-sidebar', require('./components/frontend/good/partials/GoodSidebarComponent.vue').default);
Vue.component('good-view-frontend-actions', require('./components/frontend/good/partials/ActionsComponent.vue').default);
Vue.component('good-view-frontend-price', require('./components/frontend/good/partials/PriceComponent.vue').default);
Vue.component('good-view-frontend-labels', require('./components/frontend/good/partials/LabelsComponent.vue').default);
Vue.component('good-contacts-widget', require('./components/frontend/widgets/GoodContactsWidgetComponent.vue').default);
Vue.component('good-filters-widget', require('./components/frontend/widgets/GoodListFiltersComponent.vue').default);

Vue.component('good-edit-admin', require('./components/backend/good/GoodEditComponent.vue').default);
Vue.component('good-edit-admin-images', require('./components/backend/good/partials/GoodImagesComponent.vue').default);
Vue.component('good-edit-admin-location', require('./components/backend/good/partials/GoodLocationComponent.vue').default);
Vue.component('good-edit-admin-categories', require('./components/backend/good/partials/GoodCategoriesComponent.vue').default);
Vue.component('good-edit-admin-add-category', require('./components/backend/good/partials/AddGoodCategoryFormComponent.vue').default);

Vue.component('seller-view-frontend', require('./components/frontend/seller/SellerViewComponent.vue').default);
Vue.component('seller-view-frontend-detail', require('./components/frontend/seller/partials/SellerViewDetailComponent.vue').default);
Vue.component('seller-view-frontend-tabs', require('./components/frontend/seller/partials/SellerViewTabsComponent.vue').default);

Vue.component('wineseller-view-frontend', require('./components/frontend/wineseller/WinesellerViewComponent.vue').default);
Vue.component('wineseller-view-frontend-detail', require('./components/frontend/wineseller/partials/WinesellerViewDetailComponent.vue').default);
Vue.component('wineseller-view-frontend-tabs', require('./components/frontend/wineseller/partials/WinesellerViewTabsComponent.vue').default);

Vue.component('furnitureseller-view-frontend', require('./components/frontend/furnitureseller/FurnituresellerViewComponent.vue').default);
Vue.component('furnitureseller-view-frontend-detail', require('./components/frontend/furnitureseller/partials/FurnituresellerViewDetailComponent.vue').default);
Vue.component('furnitureseller-view-frontend-tabs', require('./components/frontend/furnitureseller/partials/FurnituresellerViewTabsComponent.vue').default);

Vue.component('brand-view-frontend', require('./components/frontend/brand/BrandViewComponent.vue').default);
Vue.component('brand-view-frontend-detail', require('./components/frontend/brand/partials/BrandViewDetailComponent.vue').default);
Vue.component('brand-view-frontend-tabs', require('./components/frontend/brand/partials/BrandViewTabsComponent.vue').default);

Vue.component('user-profile-designCategories', require('./components/backend/profile/partials/DesignCategoryListComponent.vue').default);
Vue.component('design-category-edit-admin', require('./components/backend/profile/partials/DesignCategoryEditComponent.vue').default);
Vue.component('user-profile-design-category-child', require('./components/backend/profile/partials/DesignCategoryListChildComponent.vue').default);

Vue.component('user-profile-designs', require('./components/backend/profile/partials/DesignListComponent.vue').default);
Vue.component('design-list-frontend', require('./components/frontend/design/DesignListComponent.vue').default);
Vue.component('design-list-frontend-list-item', require('./components/frontend/design/DesignListItemComponent.vue').default);
Vue.component('design-view-frontend', require('./components/frontend/design/DesignViewComponent.vue').default);
Vue.component('design-view-frontend-header', require('./components/frontend/design/partials/DesignHeaderComponent.vue').default);
Vue.component('design-view-frontend-description', require('./components/frontend/design/partials/DesignDescriptionComponent.vue').default);
Vue.component('design-view-frontend-product-similar', require('./components/frontend/design/partials/DesignSimilarComponent.vue').default);
//Vue.component('design-view-frontend-good-similar', require('./components/frontend/design/partials/DesignSimilarComponent.vue').default);
//Vue.component('design-view-frontend-product-similar', require('./components/frontend/design/partials/DesignSimilarComponent.vue').default);
Vue.component('design-view-frontend-sidebar', require('./components/frontend/design/partials/DesignSidebarComponent.vue').default);
Vue.component('design-view-frontend-actions', require('./components/frontend/design/partials/ActionsComponent.vue').default);
Vue.component('design-view-frontend-price', require('./components/frontend/design/partials/PriceComponent.vue').default);
Vue.component('design-view-frontend-labels', require('./components/frontend/design/partials/LabelsComponent.vue').default);
Vue.component('design-contacts-widget', require('./components/frontend/widgets/DesignContactsWidgetComponent.vue').default);
Vue.component('design-filters-widget', require('./components/frontend/widgets/DesignListFiltersComponent.vue').default);

Vue.component('design-edit-admin', require('./components/backend/design/DesignEditComponent.vue').default);
Vue.component('design-edit-admin-images', require('./components/backend/design/partials/DesignImagesComponent.vue').default);
Vue.component('design-edit-admin-location', require('./components/backend/design/partials/DesignLocationComponent.vue').default);
Vue.component('design-edit-admin-categories', require('./components/backend/design/partials/DesignCategoriesComponent.vue').default);
Vue.component('design-edit-admin-add-category', require('./components/backend/design/partials/AddDesignCategoryFormComponent.vue').default);

//Vue.component('google-ads', require('./components/frontend/partials/GoogleAdsComponent.vue').default);

Vue.prototype.$eventHub = new Vue(); // Global event bus

const app = new Vue({
    el: '#app'
});

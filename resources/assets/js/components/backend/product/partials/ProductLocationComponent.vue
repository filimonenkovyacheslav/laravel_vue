<template>
    <div class="account-block form-step">
        <div class="add-title-tab">
            <h3>{{ trans('Item location') }}</h3>
            <div class="add-expand"></div>
        </div>
        <div class="add-tab-content">
            <div class="add-tab-row push-padding-bottom location-fields">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="country">{{ trans('Country') }}</label>
                            <select name="country" id="country" v-model="$parent.entity.country" class="form-control">
                                <option v-for="v, k in $parent.params.countries" :value="k">{{ v }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="state">{{ trans('State') }}</label>
                            <input type="text" name="state" id="state" v-model="$parent.entity.state" class="form-control" />
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="region">{{ trans('Greater Region') }}</label>
                            <input type="text" name="region" id="region" v-model="$parent.entity.region" class="form-control" />
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="city">{{ trans('City') }}</label>
                            <input type="text" name="city" id="city" v-model="$parent.entity.city" class="form-control"/>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="postal_code">{{ trans('Postcode') }}</label>
                            <input type="text" name="postal_code" id="postal_code" v-model="$parent.entity.postal_code" class="form-control"/>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="suburb">{{ trans('Suburb') }}</label>
                            <input type="text" name="suburb" id="suburb" v-model="$parent.entity.suburb" class="form-control"/>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="street">{{ trans('Street') }}</label>
                            <input type="text" name="street" id="street" v-model="$parent.entity.street" class="form-control"/>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="house">{{ trans('House') }}</label>
                            <input type="text" name="house" id="house" v-model="$parent.entity.house" class="form-control"/>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label for="address">{{ trans('Address') }} <a href="#addAddress" class="btn btn-sm" @click.stop="refreshAddress()"><i class="fa fa-refresh"></i></a></label>
                            <input type="text" name="address" id="address" v-model="$parent.entity.address" class="form-control"/>
                        </div>
                    </div>
                </div>
            </div>
            <!--<div class="add-tab-row">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="map_canvas" id="map"></div>
                        <button type="button" id="reset-marker-position" :data-map="$parent.entity.map_address" :data-lat="$parent.entity.lat" :data-lng="$parent.entity.lng" class="btn btn-primary" style="display:none;" @click="resetMarkerPosition('reset-marker-position')">{{ trans('Reset Marker') }}</button>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="map_address">{{ trans('Location') }}</label>
                            <input type="text" name="map_address" id="map_address" v-model="$parent.entity.map_address" class="form-control" />
                        </div>
                        <div class="form-group">
                            <label for="lat">{{ trans('Latitude') }}</label>
                            <input type="text" name="lat" id="lat" v-model="$parent.entity.lat" class="form-control" />
                        </div>
                        <div class="form-group">
                            <label for="lng">{{ trans('Longitude') }}</label>
                            <input type="text" name="lng" id="lng" v-model="$parent.entity.lng" class="form-control" />
                        </div>
                    </div>
                </div>
            </div>-->
        </div>
    </div>
</template>

<script>
    /**
     * Get data via props in blade template <vue_template :props></vue_template>
     */
    export default {
        mounted: function() {
            var self = this;
                        
            /*this.$eventHub.$on('entityLoaded', function() {
                var parent = $('.add-tab-row'),
                    countries = self.$parent.params.countries_codes,
                    countriesNames = self.$parent.params.countries_names,
                    options = { 'countries': countries, 'draggable': true };
                
                self.geoSearchAutocompleate(document.getElementById('map_address'), {
                    setMarker: true,
                    provider: 'bing',
                    onSelect: function(item, event) {
                        if(parent.find('input[name="address"]').val() == '') {
                            self.$parent.entity.address = item.label;
                        }
                        self.$parent.entity.lat = item.lat;
                        self.$parent.entity.lng = item.lng;
                        self.$parent.entity.city = item.city;
                        self.$parent.entity.state = item.state;
                        self.$parent.entity.postal_code = item.postal_code;
                        if (item.iso2) {
                            self.$parent.entity.country = (item.iso2 in countries ? countries[item.iso2] : '');
                        } else {
                            self.$parent.entity.country = (item.country in countriesNames ? countriesNames[item.country] : '');
                        }
                        self.$parent.entity.map_address = item.label;
                        parent.find('input[name="lat"]').val(item.lat);
                        parent.find('input[name="lng"]').val(item.lng).trigger('change');
                    }
                });
                
                self.initMap('map', { 'lat': self.$parent.entity.lat, 'lng': self.$parent.entity.lng }, options);
                parent.find('input[name="lat"], input[name="lng"]').on('change', function() {
                    var lat = parent.find('input[name="lat"]').val(),
                        lng = parent.find('input[name="lng"]').val();
                    self.updateMapAddressData(document.getElementById('map'), lat, lng, options);
                    self.updateMarkerPosition(lat, lng);
                });
            });*/
        },
        methods: {
            refreshAddress: function() {
                var self = this,
                    block = $('.location-fields'),
                    address = '';
                block.find('input:not(#address), select').reverse().each(function() {
                    var elem = $(this);
                    if (elem.val() != '') {
                        address += (address.length ? ', ' : '') + (elem.is('input') ? elem.val() : elem.find('option:selected').html());
                    }
                });
                self.$parent.entity.address = address;
            }
        }
    }
</script>

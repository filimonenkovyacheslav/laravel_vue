<template>
    <div class="user-dashboard-right dashboard-with-panel">
        <div class="board-header board-header-4">
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <div class="board-header-left">
                        <h3 v-if="params.ad_user.id" class="board-title">{{ trans('Edit User Ad') }}</span></h3>
                        <h3 v-else class="board-title">{{ trans('New User Ad') }}</span></h3>
                    </div>
                    <div class="board-header-right">
                        <ol class="breadcrumb">
                            <li itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
                                <a itemprop="url" v-bind:href:href="route('home')">
                                    <span itemprop="title">{{ trans('Home') }}</span>
                                </a>
                            </li>
                            <li v-if="params.ad_user.id" class="active">{{ trans('Edit User Ad') }}</li>
                            <li v-else class="active">{{ trans('New User Ad') }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="dashboard-content-area dashboard-fix">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="profile-content-area">
                        <form action="/save-ad-user" id="form-ad-user" method="post" enctype="multipart/form-data">
                            <div class="account-block account-profile-block">
                                <div class="row">
                                    <div class="col-md-6 col-sm-12 col-xs-12">
                                        <div class="form-group">
                                            <label>{{ trans('Name') }}</label>
                                            <input type="text" name="name" :value="params.ad_user.name" class="form-control"/>
                                        </div>
                                        <div class="my-avatar">
                                            <label>{{ trans('Image') }}</label>
                                            <div class="houzez-ad-media">
                                                <div class="houzez-thumb">
                                                    <div class="figure-image"><img class="ad-media" :src="getImageUrl(params.ad_user.image.name)" alt="Ad media"></div>
                                                </div>
                                            </div>
                                            <div class="profile-img-controls">
                                                <div class="houzez-upload-errors"></div>
                                                <div class="houzez-upload-container"></div>
                                            </div>
                                            <div class="logo-upload-container" style="position: relative;">
                                                <button type="button" class="btn btn-primary btn-block" @click="uploadImage">{{ trans('Update Image') }}</button>
                                                <input type="file" name="media_id" class="logo-upload-input" @change="onImageChange" accept="image/jpeg,.jpg,.jpeg,image/gif,.gif,image/png,.png" style="display: none;" />
                                                <input type="hidden" name="media" :value="params.ad_user.media" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12 col-xs-12">
                                        <div class="form-group">
                                            <label>{{ trans('Url') }}</label>
                                            <input type="text" name="url" :value="params.ad_user.url" class="form-control"/>
                                        </div>
                                        <div class="form-group">
                                            <label>{{ trans('Role') }}</label>
                                            <select name="role_name" :value="params.ad_user.role_name" class="form-control">
                                                <option v-for="title, name in params.ad_user_roles" :value="name"> {{ title }} </option>
                                            </select>
                                        </div>
                                        <div class="form-group" id="ad-user-professions" :style="'display:'+(params.ad_user.role_name == 'professional' ? 'block' : 'none')+';'">
                                            <label>{{ trans('Professions') }}</label>
                                            <v-select multiple searchable :value="getValueForMultiselect(params.ad_user.professions, params.professions, params.professions_sort)" :options="getOptionsSort(params.professions, params.professions_sort)" :onChange="updateMultiselect"></v-select>
                                            <input type="hidden" name="professions" id="professions" :value="params.ad_user.professions">
                                        </div>
                                        <div class="form-group">
                                            <input type="hidden" name="id" :value="params.ad_user.id">
                                            <input type="hidden" name="_token" v-model="csrf">
                                            <button class="btn btn-primary pull-right" style="margin-top:30px">{{ trans('Save User Ad') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="account-block account-profile-block">
                                <div class="row">
                                    <div class="col-md-6 col-sm-12 col-xs-12">
                                        <div class="form-group">
                                            <label>{{ trans('Location') }}</label>
                                            <input type="text" name="map_address" id="map_address" :value="params.ad_user.map_address" class="form-control"/>
                                            <input type="hidden" name="lat" :value="params.ad_user.lat">
                                            <input type="hidden" name="lng" :value="params.ad_user.lng">
                                        </div>
                                        <div class="map_canvas" id="map"></div>
                                        <button type="button" id="reset-marker-position" :data-map="params.ad_user.address" :data-lat="params.ad_user.lat" :data-lng="params.ad_user.lng" class="btn btn-primary" style="display:none;" @click="resetMarkerPosition('reset-marker-position')">{{ trans('Reset Marker') }}</button>
                                    </div>
                                    <div class="col-md-6 col-sm-12 col-xs-12">
                                        <div class="form-group">
                                            <label>{{ trans('Country') }}</label>
                                            <select name="country" :value="params.ad_user.country" class="form-control">
                                                <option value="">{{ trans('None') }}</option>
                                                <option v-for="title, id in params.countries" :value="id"> {{ title }} </option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>{{ trans('State') }}</label>
                                            <input type="text" name="state" :value="params.ad_user.state" class="form-control"/>
                                        </div>
                                        <div class="form-group">
                                            <label>{{ trans('City') }}</label>
                                            <input type="text" name="city" :value="params.ad_user.city" class="form-control"/>
                                        </div>
                                        <div class="form-group">
                                            <label>{{ trans('Street') }}</label>
                                            <input type="text" name="street" :value="params.ad_user.street" class="form-control"/>
                                        </div>
                                        <div class="form-group">
                                            <label>{{ trans('House') }}</label>
                                            <input type="text" name="house" :value="params.ad_user.house" class="form-control"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['params'],
        mounted: function() {
            var self = this,
                parent = $('#form-ad-user'),
                countries = this.params.countries_codes,
                profList = $('#ad-user-professions');

            self.geoSearchAutocompleate(document.getElementById('map_address'), {
                setMarker: true,
                onSelect: function(item, event) {
                    parent.find('input[name="lat"]').val(item.lat);
                    parent.find('input[name="lng"]').val(item.lng);
                    parent.find('input[name="city"]').val(item.city);
                    parent.find('input[name="state"]').val(item.state);
                    parent.find('input[name="street"]').val(item.street);
                    parent.find('input[name="house"]').val(item.house);
                    parent.find('select[name="country"]').val(item.iso2 in countries ? countries[item.iso2] : '');
                }
            });

            this.initMap('map', { 'lat': this.getValue(this.params.ad_user.lat), 'lng': this.getValue(this.params.ad_user.lng) });

            parent.find('select[name="role_name"]').on('change', function() {
                var selected = $(this).find('option:selected').val();
                if(selected == 'professional') {
                    profList.show();
                } else {
                    profList.hide();
                }
            });
        },
        methods: {
            onImageChange: function(e) {
                var files = e.target.files || e.dataTransfer.files;
                if (!files.length) return false;
                this.createImage(files[0], $(e.target).closest('div.my-avatar').find('img.ad-media'));
            },
            createImage: function(file, img) {
                var self = this,
                    reader = new FileReader();

                reader.onload = function(e) {
                    img.attr('src', e.target.result);
                };
                reader.readAsDataURL(file);
            },
            uploadImage: function(e) {
                $(e.target).closest('div.logo-upload-container').find('.logo-upload-input').click();
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
            }
        }
    }
</script>
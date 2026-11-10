<template>
    <div class="dashboard-content-area dashboard-fix">
        <message-bar :message="messageData" :errors="errorsList" :isError="errorsExist"></message-bar>
        <div class="container">
            <div class="row dashboard-inner-main">
                <div class="col-lg-10 col-md-9 col-sm-12 dashboard-inner-left">
                    <form id="save-property-form" action="/save-property" method="post" enctype="multipart/form-data" class="add-frontend-property">
                        <div class="submit-form-wrap">
                            <div class="account-block form-step active">
                                <div class="add-title-tab">
                                    <h3>{{ trans('Property description and price') }}</h3>
                                    <div class="add-expand"></div>
                                </div>
                                <div class="add-tab-content">
                                    <div class="add-tab-row push-padding-bottom">
                                        <div class="row">
                                            <div class="col-sm-12" v-if="params.user_role=='administrator' || params.agency_agents[params.user_role]">
                                                <div :class="['form-group', errorsList.author ? 'has-danger' : '']">
                                                    <label for="author_name">{{ trans('Author') }} *</label>
                                                    <input type="text" name="author_name" id="author_name" v-model="entity.author_name"
                                                    :class="['form-control', errorsList.author ? 'form-control-danger' : '']" :placeholder="trans('Enter user name or ID')" />
                                                    <span v-if="errorsList.author" :class="['form-control-feedback']">{{ errorsList.author[0] }}</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div :class="['form-group', errorsList.title ? 'has-danger' : '']">
                                                    <label for="title">{{ trans('Property Title') }} *</label>
                                                    <input type="text" name="title" id="title" v-model="entity.title" :class="['form-control', errorsList.title ? 'form-control-danger' : '']"/>
                                                    <span v-if="errorsList.title" :class="['form-control-feedback']">{{ errorsList.title[0] }}</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label for="slug">{{ trans('Slug') }}</label>
                                                    <input type="text" v-if="params.user_role!='administrator'" name="slug" id="slug" disabled v-model="entity.slug" class="form-control"/>
													<input type="text" v-if="params.user_role=='administrator'" name="slug" id="slug" v-model="entity.slug" class="form-control"/>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label>{{ trans('Description') }}</label>
                                                    <tinymce name="description" id="description" :value="entity.description" :content="entity.description"></tinymce>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="add-tab-row push-padding-bottom">
                                        <div class="row">
                                            <div class="col-sm-4 property_status_container">
                                                <div class="form-group">
                                                    <label for="property_status">{{ trans('Property Status') }}</label>
                                                    <select name="property_status" id="property_status" v-model="entity.property_status" class="form-control" @change="propertyStatusChange">
                                                        <option value="">{{ trans('None') }}</option>
                                                        <option v-for="v in params.property_statuses" :value="v.id">{{ v.label }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-4 property_type_container" :style="propertyTypeDisplay ? '' : 'display: none;'">
                                                <div class="form-group">
                                                    <label for="property_type">{{ trans('Type') }}</label>
                                                    <select name="property_type" id="property_type" v-model="entity.property_type" class="form-control" @change="propertyTypeChange">
                                                        <option value="">{{ trans('None') }}</option>
                                                        <option v-for="v in params.property_types" :value="v.id">{{ v.label }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-4 property_subtype_container" :style="propertySubtypeDisplay ? '' : 'display: none;'">
                                                <div class="form-group">
                                                    <label for="property_subtype">{{ trans('Subtype') }}</label>
                                                    <select name="property_subtype" id="property_subtype" v-model="entity.property_subtype" class="form-control">
                                                        <option value="">{{ trans('None') }}</option>
                                                        <option v-for="v in params.property_subtypes" :value="v.id">{{ v.label }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-4 property-status-subopt" :style="propertyRentScheduleDisplay ? '' : 'display: none;'">
                                                <div class="form-group">
                                                    <label for="property_rent_schedule">{{ trans('Rent Schedule') }}</label>
                                                    <select name="property_rent_schedule" id="property_rent_schedule" v-model="entity.property_rent_schedule" class="form-control">
                                                        <option value="">{{ trans('None') }}</option>
                                                        <option v-for="v in params.property_rent_schedule" :value="v.id">{{ v.label }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="add-tab-row push-padding-bottom">
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <div :class="['form-group', errorsList.price ? 'has-danger' : '']">
                                                    <label for="price">{{ trans('Price') }}</label>
                                                    <input type="text" name="price" id="price" v-model="entity.price_default" :class="['form-control', errorsList.price ? 'form-control-danger' : '']"/>
                                                    <span v-if="errorsList.price" :class="['form-control-feedback']">{{ errorsList.price[0] }}</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="currency_code">{{ trans('Currency') }}</label>
                                                    <select name="currency_code" id="currency_code" v-model="entity.currency_code" class="form-control">
                                                        <option v-for="item, index in params.currencies" :value="index">{{ item }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>&nbsp;</label>
                                                    <input type="checkbox" name="price_hidden" id="price_hidden" v-model="entity.price_hidden" @change="switchCheckboxIcon" style="display: none;">
                                                    <div class="btn-group">
                                                        <label for="price_hidden" class="btn btn-default">
                                                            <span :class="'checkbox-icon fa' + (entity.price_hidden == 1 ? ' fa-check' : '')"></span>
                                                        </label>
                                                        <label for="price_hidden" class="btn btn-default active" title="Hide Price">Hide Price</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="price_second">{{ trans('Second Price (Optional)') }}</label>
                                                    <input type="text" name="price_second" id="price_second" v-model="entity.price_second" class="form-control"/>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="price_before">{{ trans('Price Prefix (ex: Start from)') }}</label>
                                                    <input type="text" name="price_before" id="price_before" v-model="entity.price_before" class="form-control"/>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="price_after">{{ trans('After Price Label (ex: monthly)') }}</label>
                                                    <input type="text" name="price_after" id="price_after" v-model="entity.price_after" class="form-control"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <property-edit-admin-categories></property-edit-admin-categories>
                            <property-edit-admin-images></property-edit-admin-images>
                            <property-edit-admin-details></property-edit-admin-details>
                            <property-edit-admin-features></property-edit-admin-features>
                            <property-edit-admin-location></property-edit-admin-location>
                            <address-keywords v-if="params.user_role=='administrator'"></address-keywords>
                            <property-edit-admin-floor-plan></property-edit-admin-floor-plan>
                            <input type="hidden" name="id" v-model="entity.id">
                            <input type="hidden" name="lang_id" v-model="entity.lang_id">
                            <input type="hidden" name="author" v-model="entity.author">
                            <input type="hidden" name="status" v-model="entity.status">
                            <input type="hidden" name="_token" v-model="csrf">
                        </div>
                    </form>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-12 dashboard-inner-right">
                    <div class="dashboard-sidebar">
                        <div class="dashboard-sidebar-inner">
                            <button type="submit" class="btn btn-default btn-block" @click.stop="onSubmit()">{{ trans('Save') }}</button>
                            <!--<button id="save_as_draft" class="btn btn-default btn-block">Save as draft</button>-->
                            <a v-if="entity && entity.slug" :href="route('property.view.frontend', {'slug': entity.slug})" class="btn btn-default btn-block">{{ trans('Property View') }}</a>
                            <a :href="route('user.profile.properties')" class="btn btn-default btn-block">{{ trans('Properties List') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <property-edit-admin-add-category></property-edit-admin-add-category>
        <add-address-keyword></add-address-keyword>
    </div>
</template>

<script>
/**
 * Get data via props in blade template <vue_template :props></vue_template>
 */
    export default {
        data: function() {
            return {
                propOptsDependencies: null,
                propertyTypeDisplay: false,
                propertySubtypeDisplay: false,
                propertyRentScheduleDisplay: false
            }
        },
        props: ['params'],
        created: function() {
            var self = this,
                id = this.params.id ? '/' + this.params.id : '';

            axios.post('/api/property' + id, { _token: this.csrf }).then(function(response) {
                self.entity = response.data.entity;
                self.$eventHub.$emit('entityLoaded');
            }).catch(function(error) {
                console.log(error);
            });
        },
        mounted: function() {
            var self = this,
                form = $('#save-property-form'),
                userRole = this.params.user_role;

            this.$eventHub.$on('entityLoaded', function() {
                self.propOptsDependencies = self.params.property_type_links;
                self._prerareValuesForSelects();
                self.propertyStatusChange();
            });
            //console.log(this.params);

            if(userRole == 'administrator' || userRole in this.params.agency_agents) {
                this.getUserAutocomplete(form.find('input[name="author_name"]'), {
                    'role': userRole == 'administrator' ? 'all' : 'my_agents',
                    onSelect: function(item, event) {
                        form.find('input[name="author"]').val(item.id);
                        self.entity.author = item.id;
                        self.entity.author_name = item.label;
                    }
                });
            }
        },
        methods: {
            propertyStatusChange: function(e) {
                var self = this,
                    propertyTypeOpts = $('select[name="property_type"] option'),
                    propStatus = this.entity.property_status;

                this.propertyTypeDisplay = propStatus > 0;
                this._propertySubtypeDisplayUpdate();
                this.propertyRentScheduleDisplay = propStatus == 1;
                if(e) {
                    this.entity.property_type = '';
                    this.propertyTypeChange();
                }
                if(typeof this.propOptsDependencies.status_types[propStatus] != 'undefined') {
                    propertyTypeOpts.hide();
                    propertyTypeOpts.filter(function(index) {
                        var value = $(this).attr('value');
                        return (self.inArray(value, self.propOptsDependencies.status_types[propStatus]) || value == '');
                    }).show();
                } else {
                    propertyTypeOpts.show();
                }
            },
            propertyTypeChange: function(e) {
                var self = this,
                    propertySubtypeOpts = $('select[name="property_subtype"] option'),
                    propStatus = this.entity.property_status,
                    propType = this.entity.property_type;

                this._propertySubtypeDisplayUpdate();
                if(e) {
                    this.entity.property_subtype = '';
                }
                if(typeof this.propOptsDependencies.type_status_subtypes[propType] != 'undefined'
                    && typeof this.propOptsDependencies.type_status_subtypes[propType][propStatus] != 'undefined'
                ) {
                    propertySubtypeOpts.hide();
                    propertySubtypeOpts.filter(function(index) {
                        var value = $(this).attr('value');
                        return (self.inArray(value, self.propOptsDependencies.type_status_subtypes[propType][propStatus]) || value == '');
                    }).show();
                } else {
                    propertySubtypeOpts.show();
                }
            },
            _propertySubtypeDisplayUpdate: function() {
                this.propertySubtypeDisplay = this.inArray(this.entity.property_type, Object.keys(this.propOptsDependencies.type_status_subtypes));
            },
            _prerareValuesForSelects: function() {
                this.entity.property_status = this.entity.property_status === null ? '' : this.entity.property_status;
                this.entity.property_type = this.entity.property_type === null ? '' : this.entity.property_type;
                this.entity.property_subtype = this.entity.property_subtype === null ? '' : this.entity.property_subtype;
                this.entity.property_rent_schedule = this.entity.property_rent_schedule === null ? '' : this.entity.property_rent_schedule;
            },
            onSubmit: function() {
                var self = this,
                    photos = $('.upload-inputs-shell .upload-input');

                tinyMCE.triggerSave();
				self.entity.description = $('#save-property-form [name="description"]').val();
                self.entity.price = $('#save-property-form input[name="price"]').val();
                self.entity.categories = $('#save-property-form input[name="categories"]').val();
                self.entity.photos = [];
                for(var i = 0; i < photos.length; i++) {
                    var photoId = parseInt($(photos[i]).val());
                    if(!this.inArray(photoId, self.entity.photos)) {
                        self.entity.photos.push(photoId);
                    }
                }
                self.entity.featured_image = $('input[name="featured_image"]').val();

                axios.post('/save-property', self.entity).then(function(response) {
                    self.messageData = response.data.message ? response.data.message : '';
                    self.errorsExist = response.data.errors_exist ? response.data.errors_exist : false;
                    self.errorsList = response.data.errors ? response.data.errors : [];
                    self.entity = response.data.entity ? response.data.entity : self.entity;

                    self._prerareValuesForSelects();

                    setTimeout(function() {
                        var featuredImageId = $('.file-uploader input[name="featured_image"]').val(),
                            featuredImage = $('.file-uploader .upload-block[data-id="'+featuredImageId+'"]');
                        $('.file-uploader .upload-featured').hide();
                        if(featuredImage.length > 0) {
                            featuredImage.prependTo('.file-uploader .upload-uploads-shell');
                            featuredImage.find('.upload-featured').show();
                        }
                    }, 200);

                    if(response.data.redirect) {
                        setTimeout(function() {
                            window.location.href = response.data.redirect;
                        }, 500);
                    }
                }).catch(function(error) {
                    console.log(error);
                });
            }
        }
    }
</script>

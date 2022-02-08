<template>
    <div class="dashboard-content-area dashboard-fix">
        <message-bar :message="messageData" :errors="errorsList" :isError="errorsExist"></message-bar>
        <div class="container">
            <div class="row dashboard-inner-main">
                <div class="col-lg-10 col-md-9 col-sm-12 dashboard-inner-left">
                    <form id="save-form" action="/save-good" method="post" enctype="multipart/form-data" class="add-frontend-property">
                        <div class="submit-form-wrap">
                            <div class="account-block form-step active">
                                <div class="add-title-tab">
                                    <h3>{{ trans('Item description and price') }}</h3>
                                    <div class="add-expand"></div>
                                </div>
                                <div class="add-tab-content">
                                    <div class="add-tab-row push-padding-bottom">
                                        <div class="row">
                                            <div class="col-sm-12" v-if="params.user_role=='administrator'">
                                                <div :class="['form-group', errorsList.author ? 'has-danger' : '']">
                                                    <label for="author_name">{{ trans('Author') }} *</label>
                                                    <input type="text" name="author_name" id="author_name" v-model="entity.author_name"
                                                           :class="['form-control', errorsList.author ? 'form-control-danger' : '']" :placeholder="trans('Enter seller name or ID')" />
                                                    <span v-if="errorsList.author" :class="['form-control-feedback']">{{ errorsList.author[0] }}</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div :class="['form-group', errorsList.title ? 'has-danger' : '']">
                                                    <label for="title">{{ trans('Title') }} *</label>
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
                                            <div class="col-sm-12" v-if="params.user_role=='administrator'">
                                                <div class="form-group">
                                                    <label for="position">{{ trans('Position') }}</label>
                                                    <input type="number" min="0" max="1000" name="position" id="position" v-model="entity.position" class="form-control"/>
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
                            
                            <good-edit-admin-categories></good-edit-admin-categories>
                            <good-edit-admin-images></good-edit-admin-images>
                            <good-edit-admin-location></good-edit-admin-location>
                            <address-keywords v-if="params.user_role=='administrator'"></address-keywords>
                            
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
                            <button type="submit" class="btn btn-success btn-block" @click.stop="onSubmit()">{{ trans('Save') }}</button>
                            <!--<button id="save_as_draft" class="btn btn-default btn-block">Save as draft</button>-->
                            <a v-if="entity && entity.slug" :href="route('good.view.frontend', {'slug': entity.slug})" class="btn btn-default btn-block">{{ trans('View') }}</a>
                            <a :href="route('user.profile.goods')" class="btn btn-default btn-block">{{ trans('Good List') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <good-edit-admin-add-category></good-edit-admin-add-category>
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
            
            axios.post('/api/good' + id, { _token: this.csrf }).then(function(response) {
                self.entity = response.data.entity;
                self.$eventHub.$emit('entityLoaded');
            }).catch(function(error) {
                console.log(error);
            });
        },
        mounted: function() {
            var self = this,
                form = $('#save-form'),
                userRole = this.params.user_role;
            
            if(userRole == 'administrator' || userRole in this.params.agency_agents) {
                this.getUserAutocomplete(form.find('input[name="author_name"]'), {
                    'role': 'all',
                    onSelect: function(item, event) {
                        form.find('input[name="author"]').val(item.id);
                        self.entity.author = item.id;
                        self.entity.author_name = item.label;
                    }
                });
            }
        },
        methods: {
            onSubmit: function() {
                var self = this,
                    photos = $('.upload-inputs-shell .upload-input');
                
                tinyMCE.triggerSave();
                self.entity.description = $('#save-form [name="description"]').val();
                self.entity.price = $('#save-form input[name="price"]').val();
                self.entity.categories = $('#save-form input[name="categories"]').val();
                self.entity.photos = [];
                for(var i = 0; i < photos.length; i++) {
                    var photoId = parseInt($(photos[i]).val());
                    if(!this.inArray(photoId, self.entity.photos)) {
                        self.entity.photos.push(photoId);
                    }
                }
                self.entity.featured_image = $('input[name="featured_image"]').val();
                
                axios.post('/save-good', self.entity).then(function(response) {
                    self.messageData = response.data.message ? response.data.message : '';
                    self.errorsExist = response.data.errors_exist ? response.data.errors_exist : false;
                    self.errorsList = response.data.errors ? response.data.errors : [];
                    self.entity = response.data.entity ? response.data.entity : self.entity;
                    
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

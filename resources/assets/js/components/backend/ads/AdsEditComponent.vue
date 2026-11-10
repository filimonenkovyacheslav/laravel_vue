<template>
    <div class="dashboard-content-area dashboard-fix">
        <message-bar :message="messageData" :errors="errorsList" :isError="errorsExist"></message-bar>
        <div class="container">
            <div class="row dashboard-inner-main">
                <div class="col-lg-10 col-md-9 col-sm-12 dashboard-inner-left">
                    <form id="save-ads-form" name="saveAdsForm" action="/save-ads" method="post" enctype="multipart/form-data" class="save-ads-form">
                        <div class="submit-form-wrap">
                            <div class="account-block form-step active">
                                <div class="add-title-tab">
                                    <h3>{{ trans('Image Video Ad') }}</h3>
                                    <div class="add-expand"></div>
                                </div>
                                <div class="add-tab-content">
                                    <div class="add-tab-row push-padding-bottom">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div :class="['form-group', errorsList.title ? 'has-danger' : '']">
                                                    <label for="title">{{ trans('Title') }} *</label>
                                                    <input type="text" name="title" id="title" v-model="entity.title" :class="['form-control', errorsList.title ? 'form-control-danger' : '']"/>
                                                    <span v-if="errorsList.title" :class="['form-control-feedback']">{{ errorsList.title[0] }}</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label for="title">{{ trans('URL') }} </label>
                                                    <input type="text" name="url" id="url" v-model="entity.url" class="form-control"/>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label for="title">{{ trans('Order') }} </label>
                                                    <input type="number" name="order" id="order" v-model="entity.order" class="form-control"/>
                                                </div>
                                            </div>

                                            <div class="col-sm-12 col-xs-12" v-if="fields">
                                                <template v-for="field, index in fields.relation">
                                                    <html-element :field="field" :index="index" :key="index" :params="param"></html-element>
                                                </template>
                                            </div>

                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label for="address">{{ trans('Location') }}</label>
                                                    <input type="text" id="address" name="address" v-model="entity.address"
                                                           class="form-control" :placeholder="trans('Enter location')" />
                                                    <input type="hidden" name="country_id" id="country_id" v-model="entity.country_id"/>
                                                    <input type="hidden" name="city" id="city" v-model="entity.city"/>
                                                    <input type="hidden" name="state" id="state" v-model="entity.state"/>
                                                    <input type="hidden" name="country_name" id="country_name" v-model="entity.country_name"/>
                                                    <input type="hidden" name="iso3" id="iso3"/>
                                                </div>
                                            </div>
                                            <div class="col-sm-12" v-if="params.user_role=='administrator'">
                                                <div class="form-group">
                                                    <div class="form-group">
                                                        <label for="keywords">{{ trans('Keywords') }}</label>
                                                        <textarea name="keywords" rows="3" id="keywords" v-model="entity.keywords" :class="['form-control', errorsList.keywords ? 'form-control-danger' : '']"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 figure-file" v-if="params.user_role=='administrator'">
                                                <div class="row align-items-end">
                                                    <div class="col-md-8 col-lg-9">
                                                        <div :class="['form-group', errorsList.file_link ? 'has-danger' : '']">
                                                            <label for="file_link">{{ trans('Use video/image link or upload own file') }}</label>
                                                            <input type="text" name="file_link" id="file_link" @change="onFileLinkChange" v-model="entity.file_link" :class="['form-control', errorsList.file_link ? 'form-control-danger' : '']"/>
                                                            <span v-if="errorsList.file_link" :class="['form-control-feedback']">{{ errorsList.file_link[0] }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 col-lg-3">
                                                        <div id="file-upload-container" style="position: relative;">
                                                            <span class="profile-upload-info" :data-selected="trans('File is selected')"></span>
                                                            <button type="button" class="btn btn-primary btn-block" @click="uploadFile">{{ trans('Upload File') }}</button>
                                                            <p class="profile-img-info">{{ trans('Delete uploaded file') }}
                                                                <i class="fa fa-trash upload-delete" @click="deleteFile"></i>
                                                            </p>
                                                            <input type="file" name="fileNew" id="file-upload-input" @change="onFileChange" style="visibility:hidden;width:0;height:0" />
                                                            <input type="hidden" name="upload" :value="getValue(entity.file)"  />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="figure-image">
                                                    <template v-if="entity.file_link && entity.file_type == 1">
                                                        <img class="file" width="700" :src="entity.file_link" style="max-width:100%;height:auto;"/>
                                                    </template>
                                                    <template v-else-if="entity.file_link && entity.file_type == 2">
                                                        <video controls width="700" height="310">
                                                            <source :src="entity.file_link" :type="entity.video_type">
                                                        </video>
                                                    </template>
                                                    <template v-else-if="entity.file_link">
                                                        <iframe class="file" width="700" height="310" :src="entity.file_link" style="max-width:100%;overflow:hidden;"></iframe>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="ads_id" v-model="entity.ads_id">
                            <input type="hidden" name="status" v-model="entity.status">
                            <input type="hidden" name="_token" v-model="csrf">
                        </div>
                    </form>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-12 dashboard-inner-right">
                    <div class="dashboard-sidebar">
                        <div class="dashboard-sidebar-inner">
                            <button type="submit" class="btn btn-save-ads btn-success btn-block" @click.stop="onSubmit()">{{ trans('Save') }}</button>
                            <a :href="route('user.profile.ads')" class="btn btn-default btn-block"><i class="fa fa-bars" aria-hidden="true"></i> {{ trans('Ads List') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="save-loader">
            <div class="save-loader-inner">
                <img src="/images/loader.gif" alt="">
            </div>
        </div>
    </div>
</template>

<script>
    /**
     * Get data via props in blade template <vue_template :props></vue_template>
     */
    export default {
        data: function() {
            return {
                file: '',
                param: {
                    type: []
                },
                fields: {},
            }
        },
        props: ['params'],
        created: function() {
            var self = this,
                id = this.params.ads_id ? '/' + this.params.ads_id : '';
            
            if (id.length) {
                axios.post('/api/ads' + id, {_token: this.csrf}).then(function (response) {
                    self.entity = response.data.entity;
                    self.entity.video_type = self.checkVideoType();                   
                    self.$eventHub.$emit('entityLoaded');
                }).catch(function (error) {
                    console.log(error);
                });

                axios.get('/api/getAdsTypes' + id, {_token: this.csrf}).then(function (response) {
                    self.param.type = response.data.types;                   
                }).catch(function (error) {
                    console.log(error);
                });
            }

            self.getAdsCategories();            
        },
        mounted: function() {
            var self = this,
                form = $('#save-ads-form');
            if(this.params.user_role=='administrator') {
                /*this.getDataAutocomplete(form.find('input[name="address"]'), {'url': '/search-country'},
                    function(item, event) {
                        form.find('input[name="country_id"]').val(item.id);
                        self.entity.country_id = item.id;
                        self.entity.country_name = item.label;
                    });*/
                this.geoHereAutocompleate(document.getElementById('address'), {
                    setMarker: false,
                    onSelect: function(item, event) {
                        self.entity.country_name = item.country;
                        self.entity.city = item.city;
                        self.entity.state = item.state;
                        self.entity.address = item.label;
                        form.find('input[name="iso3"]').val(item.iso3);
                    }
                });
            }
        },
        methods: {
            onFileChange: function(e) {
                var files = e.target.files || e.dataTransfer.files;
                if (!files.length) {
                    $('.profile-upload-info').text('');
                    return false;
                }
                $('.file_link').val('');
                $('.profile-upload-info').text($('.profile-upload-info').data('selected'));
                //this.createFile(files[0]);
            },
            onFileLinkChange: function(e) {
                //$('.file').attr('src', e.target.value);
            },
            createFile: function(file) {
                var self = this,
                    reader = new FileReader();
                
                reader.onload = function(e) {
                    $('#file_link').val(e.target.result);
                };
                reader.readAsDataURL(file);
            },
            uploadFile: function() {
                $(this.$el).find('#file-upload-input').click();
            },
            deleteFile: function(e) {
                var container = $(e.target).closest('.figure-file');
                container.find('input').val('');
                container.find('.file').attr('src', '');
            },
            checkVideoType: function() {
                var re = /(?:\.([^.]+))?$/;
                var type = 'video/mp4',
                    ext = re.exec(this.entity.file_link)[1],
                    extTypes = {
                        'mp4': 'video/mp4',
                        'ogg': 'video/ogg',
                        '3gp': 'video/3gpp',
                        'mpeg': 'video/mpeg',
                        'mpg': 'video/mpeg',
                        'mov': 'video/quicktime',
                        'webm': 'video/webm',
                        'avi': 'video/avi'
                    };
                if (typeof ext !== 'undefined') {
                    type = typeof extTypes[ext] !== 'undefined' ? extTypes[ext] : type;
                }
                
                return type;
            },
            onSubmit: function() {
                var self = this,
                    oldForm = document.forms.saveAdsForm,
                    formData = new FormData(oldForm);
                
                $('.save-loader').addClass('active');

                //console.log($('#save-ads-form').serialize())
                
                axios.post('/save-ads', formData).then(function(response) {
                    self.messageData = response.data.message ? response.data.message : '';
                    self.errorsExist = response.data.errors_exist ? response.data.errors_exist : false;
                    self.errorsList = response.data.errors ? response.data.errors : [];
                    self.entity = response.data.entity ? response.data.entity : self.entity;
                    
                    $('.save-loader').removeClass('active');
                    
                    if(response.data.redirect) {
                        setTimeout(function() {
                            window.location.href = response.data.redirect;
                        }, 500);
                    }
                }).catch(function(error) {
                    console.log('error');
                    console.log(error);
                    $('.save-loader').removeClass('active');
                });
            },
            getAdsCategories: function() {
                var self = this;
                axios.post('/api/adsCategories', { _token: this.csrf }).then(function(response) {
                    self.fields = response.data.categories;
                }).catch(function(error) {
                    console.log(error);
                });
            }
        }
    }
</script>
<style scoped>

</style>

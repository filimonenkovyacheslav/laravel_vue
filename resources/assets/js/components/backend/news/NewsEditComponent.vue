<template>
    <div class="dashboard-content-area dashboard-fix">
        <message-bar :message="messageData" :errors="errorsList" :isError="errorsExist"></message-bar>
        <div class="container">
            <div class="row dashboard-inner-main">
                <div class="col-lg-10 col-md-9 col-sm-12 dashboard-inner-left">
                    <form id="save-form" action="/save-news" name="saveNewsForm" method="post" enctype="multipart/form-data" class="add-frontend-property">
                        <div class="submit-form-wrap">
                            <div class="account-block form-step active">
                                <div class="add-title-tab">
                                    <h3>{{ trans('News description') }}</h3>
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
                                </div>
                            </div>
                            
                            <news-edit-admin-images></news-edit-admin-images>

                            <simple-keywords></simple-keywords>
                            
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
                            <a v-if="entity && entity.slug" :href="route('news.view.frontend', {'slug': entity.slug})" class="btn btn-default btn-block">{{ trans('View') }}</a>
                            <a :href="route('user.profile.news')" class="btn btn-default btn-block">{{ trans('News List') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <add-simple-keyword></add-simple-keyword>
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

            axios.post('/api/news' + id, { _token: this.csrf }).then(function(response) {
                console.log(response.data.entity)
                self.entity = response.data.entity;
                self.entity.video_type = self.checkVideoType();
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
                    photos = $('.upload-inputs-shell .upload-input');
                
                tinyMCE.triggerSave();
                self.entity.description = $('#save-form [name="description"]').val();
                self.entity.photos = [];
                for(var i = 0; i < photos.length; i++) {
                    var photoId = parseInt($(photos[i]).val());
                    if(!this.inArray(photoId, self.entity.photos)) {
                        self.entity.photos.push(photoId);
                    }
                }
                self.entity.featured_image = $('input[name="featured_image"]').val();
                self.entity.fileNew = $('input[name="fileNew"]').val();
                self.entity.file_link = $('input[name="file_link"]').val();
                
                axios.post('/save-news', self.entity).then(function(response) {
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
                    else{
                        setTimeout(function() {
                            window.location.href = window.location.href;
                        }, 1000);
                    }
                }).catch(function(error) {
                    console.log(error);
                });
            }
        }
    }
</script>

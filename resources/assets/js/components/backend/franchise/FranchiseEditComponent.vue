<template>
    <div class="dashboard-content-area dashboard-fix">
        <message-bar :message="messageData" :errors="errorsList" :isError="errorsExist"></message-bar>
        <div class="container">
            <ol class="pay-step-bar">
                <li class="pay-step-block active"><span>{{ trans('Create Listings') }}</span></li>
                <li class="pay-step-block "><span>{{ trans('Done') }}</span></li>
            </ol>
            <div class="row dashboard-inner-main">
                <div class="col-lg-10 col-md-9 col-sm-12 dashboard-inner-left">
                    <form id="save-franchise-form" action="/save-franchise" method="post" enctype="multipart/form-data" class="add-frontend-property">
                        <div class="submit-form-wrap">
                            <div class="account-block form-step active">
                                <div class="add-title-tab">
                                    <h3>{{ trans('Franchise description') }}</h3>
                                    <div class="add-expand"></div>
                                </div>
                                <div class="add-tab-content">
                                    <div class="add-tab-row push-padding-bottom">
                                        <div class="row">
                                            <div class="col-sm-12" v-if="params.user_role=='administrator'">
                                                <div :class="['form-group', errorsList.author ? 'has-danger' : '']">
                                                    <label for="author_name">{{ trans('Author') }} *</label>
                                                    <input type="text" name="author_name" id="author_name" v-model="entity.author_name"
                                                    :class="['form-control', errorsList.author ? 'form-control-danger' : '']" :placeholder="trans('Enter user name or ID')" />
                                                    <span v-if="errorsList.author" :class="['form-control-feedback']">{{ errorsList.author[0] }}</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div :class="['form-group', errorsList.title ? 'has-danger' : '']">
                                                    <label for="title">{{ trans('Franchise Name') }} *</label>
                                                    <input type="text" name="title" id="title" v-model="entity.title" :class="['form-control', errorsList.title ? 'form-control-danger' : '']"/>
                                                    <span v-if="errorsList.title" :class="['form-control-feedback']">{{ errorsList.title[0] }}</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label for="slug">{{ trans('Slug') }}</label>
                                                    <input type="text" name="slug" id="slug" disabled v-model="entity.slug" class="form-control"/>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div :class="['form-group', errorsList.founded ? 'has-danger' : '']">
                                                    <label for="founded">{{ trans('Franchise Business founded') }}</label>
                                                    <input type="text" name="founded" id="founded" v-model="entity.founded" :class="['form-control', errorsList.founded ? 'form-control-danger' : '']"/>
                                                    <span v-if="errorsList.founded" :class="['form-control-feedback']">{{ errorsList.founded[0] }}</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div :class="['form-group', errorsList.fee ? 'has-danger' : '']">
                                                    <label for="fee">{{ trans('Franchise Fee') }}</label>
                                                    <input type="text" name="fee" id="fee" v-model="entity.fee" :class="['form-control', errorsList.fee ? 'form-control-danger' : '']"/>
                                                    <span v-if="errorsList.fee" :class="['form-control-feedback']">{{ errorsList.fee[0] }}</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div :class="['form-group', errorsList.investment ? 'has-danger' : '']">
                                                    <label for="investment">{{ trans('Capital Investment Required') }}</label>
                                                    <input type="text" name="investment" id="investment" v-model="entity.investment" :class="['form-control', errorsList.investment ? 'form-control-danger' : '']"/>
                                                    <span v-if="errorsList.investment" :class="['form-control-feedback']">{{ errorsList.investment[0] }}</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div :class="['form-group', errorsList.terms ? 'has-danger' : '']">
                                                    <label for="terms">{{ trans('Agreement Terms') }}</label>
                                                    <input type="text" name="terms" id="terms" v-model="entity.terms" :class="['form-control', errorsList.terms ? 'form-control-danger' : '']"/>
                                                    <span v-if="errorsList.terms" :class="['form-control-feedback']">{{ errorsList.terms[0] }}</span>
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
                            <franchise-edit-admin-images></franchise-edit-admin-images>
                            <franchise-edit-admin-location></franchise-edit-admin-location>
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
                            <a v-if="entity && entity.slug" :href="route('franchise.view.frontend', {'slug': entity.slug})" class="btn btn-default btn-block">{{ trans('Franchise View') }}</a>
                            <!-- <a :href="route('user.profile.franchises')" class="btn btn-default btn-block">{{ trans('Franchise List') }}</a> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
/**
 * Get data via props in blade template <vue_template :props></vue_template>
 */
    export default {
        props: ['params'],
        created: function() {
            var self = this,
                id = this.params.id ? '/' + this.params.id : '';

            axios.post('/api/franchise' + id, { _token: this.csrf }).then(function(response) {
                self.entity = response.data.entity;
                self.$eventHub.$emit('entityLoaded');
            }).catch(function(error) {
                console.log(error);
            });
        },
        mounted: function() {
            var self = this,
                form = $('#save-franchise-form');

            if(this.params.user_role=='administrator') {
                this.getUserAutocomplete(form.find('input[name="author_name"]'), {
                    role: 'franchise',
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
                tinyMCE.triggerSave();

                var self = this,
                    formData = $('#save-franchise-form').serialize();

                axios.post('/save-franchise', formData).then(function(response) {
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

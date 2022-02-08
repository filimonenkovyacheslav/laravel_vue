<template v-if="params.user_role=='administrator'">
    <div class="dashboard-content-area dashboard-fix">
        <message-bar :message="messageData" :errors="errorsList" :isError="errorsExist"></message-bar>
        <div class="container">
            <div class="row dashboard-inner-main">
                <div class="col-lg-10 col-md-9 col-sm-12 dashboard-inner-left">
                    <form id="save-jobEntity-form" name="saveJobEntityForm" action="/save-jobEntity" method="post" enctype="multipart/form-data" class="add-frontend-jobEntity">
                        <div class="submit-form-wrap">

                            <div class="account-block form-step active">
                                <div class="add-title-tab">
                                    <h3>{{ trans('Job') }}</h3>
                                    <div class="add-expand"></div>
                                </div>
                                <div class="add-tab-content">
                                    <div class="add-tab-row push-padding-bottom">
                                        <div class="row">
                                            <div class="col-sm-12" v-if="params.user_role=='administrator'" >
                                                <div :class="['form-group', errorsList.author ? 'has-danger' : '']">
                                                    <label for="author_name">{{ trans('Author') }} *</label>
                                                    <input type="text" name="author_name" id="author_name" v-model="entity.author_name"
                                                    :class="['form-control', errorsList.author ? 'form-control-danger' : '']" :placeholder="trans('Enter user name or ID')" />
                                                    <span v-if="errorsList.author" :class="['form-control-feedback']">{{ errorsList.author[0] }}</span>
                                                </div>
                                            </div>
											<div class="col-sm-12">
												<div class="form-group">
													<label for="slug">{{ trans('Slug') }}</label>
													<input type="text" name="slug" id="slug" disabled v-model="entity.slug" class="form-control"/>
												</div>
											</div>
											<div class="col-sm-12" >
                                                <div :class="['form-group', errorsList.title ? 'has-danger' : '']">
                                                    <label for="title">{{ trans('Title') }} *</label>
                                                    <input type="text" name="title" id="title" v-model="entity.title" :class="['form-control', errorsList.title ? 'form-control-danger' : '']"/>
                                                    <span v-if="errorsList.title" :class="['form-control-feedback']">{{ errorsList.title[0] }}</span>
                                                </div>
                                            </div>
											<div class="col-sm-12">
                                                <div :class="['form-group', errorsList.short_description ? 'has-danger' : '']">
                                                    <label>{{ trans('Short Description') }} *</label>
                                                    <tinymce name="short_description" id="short_description" :value="entity.short_description" :content="entity.short_description"></tinymce>
                                                </div>
                                            </div>
											<div class="col-sm-12">
                                                <div :class="['form-group', errorsList.description ? 'has-danger' : '']">
                                                    <label>{{ trans('Description') }} *</label>
                                                    <tinymce name="description" id="description" :value="entity.description" :content="entity.description"></tinymce>
                                                </div>
                                            </div>
											<div class="col-sm-4 job_type_container">
                                                <div class="form-group">
                                                    <label for="job_type">{{ trans('Type') }} *</label>
                                                    <select name="job_type" id="job_type" v-model="entity.job_type" :class="['form-control', errorsList.job_type ? 'has-danger' : '']" @change="">
														<option value="" :selected="true" :disabled="true">{{ trans('Choose Job Type') }}</option>
                                                        <option v-for="item, index in params.job_entity_types" :value="index">{{ item }}</option>
                                                    </select>
                                                </div>
                                            </div>
											<div class="col-sm-4 job_category_id_container">
                                                <div class="form-group">
                                                    <label for="job_category">{{ trans('Category') }} *</label>
                                                    <select name="job_category_id" id="job_category_id" v-model="entity.job_category_id" :class="['form-control', errorsList.job_category_id ? 'has-danger' : '']"@change="">
														<option value="" :selected="true" :disabled="true">{{ trans('Choose Job Category') }}</option>
                                                        <option v-for="item, index in params.job_categories" :value="index">{{ item }}</option>
                                                    </select>
                                                </div>
                                            </div>
											<div class="col-sm-12">
                                                <div class="form-group">
                                                    <label for="company_name">{{ trans('Company Name') }} *</label>
													<input type="text" name="company_name" id="company_name" v-model="entity.company_name" :class="['form-control', errorsList.company_name ? 'form-control-danger' : '']"/>
                                                    <span v-if="errorsList.company_name" :class="['form-control-feedback']">{{ errorsList.company_name[0] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
								</div>
							</div>

							<div class="account-block form-step active">
								<div class="add-title-tab">
									<h3>{{ trans('Logo') }}</h3>
									<div class="add-expand"></div>
								</div>
								<div class="add-tab-content">
									<div class="add-tab-row">
										<div class="col-md-3 col-sm-12 col-xs-12">
	                                        <div class="my-avatar profile-image figure-block">
	                                            <div id="houzez_profile_photo">
	                                                <div class="houzez-thumb">
	                                                    <div class="figure-image"><img class="avatar-image" alt="User Image" :src="getImageUrl(photoImage)"></div>
	                                                </div>
	                                            </div>
	                                            <div class="profile-img-controls">
	                                                <div id="houzez_upload_errors"></div>
	                                                <div id="plupload-container"></div>
	                                            </div>
	                                            <div id="profile-upload-containder" style="position: relative;">
	                                                <button type="button" class="btn btn-primary btn-block" @click="uploadImage">{{ trans('Upload Logo') }}</button>
	                                                <p class="profile-img-info">* {{ trans('minimum 140px x 140px') }}</p>
	                                                <input type="file" name="photoNew" id="profile-upload-input" @change="onImageChange" accept="image/jpeg,.jpg,.jpeg,image/gif,.gif,image/png,.png" style="display:none" />
													<input type="hidden" name="photo" :value="getValue(entity.photo)"  />
	                                            </div>
	                                        </div>
	                                    </div>
									</div>
								</div>
							</div>

							<jobentity-edit-admin-location></jobentity-edit-admin-location>

							<div class="account-block form-step active">
								<div class="add-title-tab">
									<h3>{{ trans('Job salary') }}</h3>
									<div class="add-expand"></div>
								</div>
								<div class="add-tab-content">
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
                                                        <!-- <option v-for="item, index in params.currencies" :value="index">{{ item }}</option> -->
														<option value="36">{{ trans('Australian Dollar (AUD)') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
													<label for="price_hidden">{{ trans('Hide price (optional)') }}</label>
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
											<div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="job_salary_type">{{ trans('Salary type') }}</label>
													<select name="job_salary_type" id="job_salary_type" v-model="entity.job_salary_type" class="form-control" >
														<option :disabled="true">{{ trans('Choose Job Salary Type') }}</option>
                                                        <option v-for="item, index in params.job_entity_salary_types" :value="index">{{ item }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

							<jobentity-edit-admin-images></jobentity-edit-admin-images>


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
                            <a v-if="entity && entity.slug" :href="route('jobEntity.view.frontend', {'slug': entity.slug})" class="btn btn-default btn-block">{{ trans('View') }}</a>
                            <a :href="route('user.profile.jobEntities')" class="btn btn-default btn-block">{{ trans('Jobs List') }}</a>
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
        data: function() {
            return {
                propOptsDependencies: null,
                jobTypeDisplay: false,
				photoImage: '',
            }
        },
        props: ['params', 'entityType'],
        created: function() {
            var self = this,
                id = this.params.id ? '/' + this.params.id : '';

            axios.post('/api/jobEntity' + id, { _token: this.csrf }).then(function(response) {
                self.entity = response.data.entity;
                self.$eventHub.$emit('entityLoaded');
            }).catch(function(error) {
                console.log(error);
            });
        },
        mounted: function() {
            var self = this,
                form = $('#save-jobEntity-form'),
                userRole = this.params.user_role;

            this.$eventHub.$on('entityLoaded', function() {
                self.propOptsDependencies = self.params.job_type_links;
                self._prerareValuesForSelects();
                self.jobStatusChange();
            });
            //console.log(this.params);


            if(userRole == 'administrator') {
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
			onImageChange: function(e) {
                var files = e.target.files || e.dataTransfer.files;
                if (!files.length) return false;
                this.createImage(files[0]);
            },
            createImage: function(file) {
                var self = this,
                    reader = new FileReader();

                reader.onload = function(e) {
                    $(self.$el).find('.avatar-image').attr('src', e.target.result);
                };
                reader.readAsDataURL(file);
            },
            uploadImage: function() {
                $(this.$el).find('#profile-upload-input').click();
            },
            jobStatusChange: function(e) {
                var self = this;
            },
            jobTypeChange: function(e) {
                var self = this;
            },
            _prerareValuesForSelects: function() {
                this.entity.job_status = this.entity.job_status === null ? '' : this.entity.job_status;
                this.entity.job_type = this.entity.job_type === null ? '' : this.entity.job_type;
				this.entity.job_salary_type = this.entity.job_salary_type === null ? '' : this.entity.job_salary_type;
				this.entity.job_category_id = this.entity.job_category_id === null ? '' : this.entity.job_category_id;
				this.entity.photoImage = this.entity.photoImage === null ? '' : this.entity.photoImage;
				this.photoImage = this.entity.photoImage.name;
            },
            onSubmit: function() {
                tinyMCE.triggerSave();

                var self = this,
					oldForm = document.forms.saveJobEntityForm,
					formData = new FormData(oldForm);
                    //formData = $('#save-jobEntity-form').serialize();
				var imagefile = document.querySelector('#profile-upload-input');
				formData.append("photoNew", imagefile.files[0]);

                axios.post('/save-jobEntity', formData).then(function(response) {
                    self.messageData = response.data.message ? response.data.message : '';
                    self.errorsExist = response.data.errors_exist ? response.data.errors_exist : false;
                    self.errorsList = response.data.errors ? response.data.errors : [];
                    self.entity = response.data.entity ? response.data.entity : self.entity;

                    self._prerareValuesForSelects();

                    // setTimeout(function() {
                    //     var featuredImageId = $('.file-uploader input[name="featured_image"]').val(),
                    //         featuredImage = $('.file-uploader .upload-block[data-id="'+featuredImageId+'"]');
                    //     $('.file-uploader .upload-featured').hide();
                    //     if(featuredImage.length > 0) {
                    //         featuredImage.prependTo('.file-uploader .upload-uploads-shell');
                    //         featuredImage.find('.upload-featured').show();
                    //     }
                    // }, 200);

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

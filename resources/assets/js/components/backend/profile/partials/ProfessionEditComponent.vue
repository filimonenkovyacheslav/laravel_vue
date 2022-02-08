<template>
    <div class="dashboard-content-area dashboard-fix">
        <div class="container">
            <div class="row dashboard-inner-main">
                <div class="col-lg-10 col-md-9 col-sm-12 dashboard-inner-left">
                    <form id="save-profession-form" name="saveProfessionForm" enctype="multipart/form-data" action="/save-profession" method="POST" class="add-frontend-profession">
                        <div class="submit-form-wrap">
                            <html-element :field="params.fields.slug" :index="params.fields.slug.index" :params="params"></html-element>
                            <html-element :field="params.fields.name" :index="params.fields.name.index" :params="params"></html-element>
                            <div class="form-group">
                              <label for="parent_id">{{ trans('Parent category') }}</label>
                              <select id="parent_id" class="form-control" name="parent_id" :value="profession.parent_id">
                                <option value="0">{{ trans('Choose parent profession') }}</option>
                                <option v-if="id != profession.profession_id" v-for="name, id in params.professions_parent" :value="id"> {{ name }} </option>
                              </select>
                            </div>
              <div class="account-block form-step active">
								<div class="add-title-tab">
									<h3>{{ trans('Logo') }}</h3>
									<div class="add-expand"></div>
								</div>
								<div class="add-tab-content">
									<div class="add-tab-row">
										<div class="col-md-4 col-sm-4 col-xs-4" >
											<div class="my-avatar profile-image figure-block">
												<div id="houzez_profile_photo">
													<div class="houzez-thumb">
														<div class="figure-image"><img class="img-logo-link" alt="User Image" :src="params.profession.imgLogoLink"></div>
													</div>
												</div>
												<div class="profile-img-controls">
													<div id="houzez_upload_errors"></div>
													<div id="plupload-container"></div>
												</div>
												<div id="profile-upload-containder" style="position: relative;">
													<button type="button" class="btn btn-primary btn-block" @click="uploadImage">{{ trans('Upload Logo') }}</button>
													<p class="profile-img-info">* {{ trans('minimum 140px x 140px') }}</p>
													<input type="file" name="imgLogoNew" id="profile-upload-input" @change="onImageChange" accept="image/jpeg,.jpg,.jpeg,image/gif,.gif,image/png,.png" style="display:none" />
													<input type="hidden" name="img_logo" :value="getValue(params.profession.img_logo)"  />
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="account-block form-step active">
								<div class="add-title-tab">
									<h3>{{ trans('Background') }}</h3>
									<div class="add-expand"></div>
								</div>
								<div class="add-tab-content">
									<div class="add-tab-row">
										<div class="col-md-4 col-sm-4 col-xs-4" >
											<div class="my-avatar profile-image figure-block">
												<div id="houzez_profile_photo">
													<div class="houzez-thumb">
														<div class="figure-image"><img class="img-background-link" alt="User Image" :src="params.profession.imgBackgroundLink"></div>
													</div>
												</div>
												<div class="profile-img-controls">
													<div id="houzez_upload_errors"></div>
													<div id="plupload-container"></div>
												</div>
												<div id="profile-upload-containder" style="position: relative;">
													<button type="button" class="btn btn-primary btn-block" @click="uploadImageBackground">{{ trans('Upload Background') }}</button>
													<input type="file" name="imgBackgroundNew" id="profile-upload-input-background" @change="onImageChangeBackground" accept="image/jpeg,.jpg,.jpeg,image/gif,.gif,image/png,.png" style="display:none" />
													<input type="hidden" name="img_background" :value="getValue(params.profession.img_background)"  />
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
                            <input type="hidden" name="_token" v-model="csrf">
                            <html-element :field="params.fields.profession_id" :index="params.fields.profession_id.index" :params="params"></html-element>
                        </div>
                    </form>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-12 dashboard-inner-right">
                    <div class="dashboard-sidebar">
                        <div class="dashboard-sidebar-inner">
                            <button type="submit" class="btn btn-default btn-block" @click.stop="onSubmit()">{{ trans('Save') }}</button>
                            <a v-bind:href="route('profession.edit.admin')" class="btn btn-default btn-block">{{ trans('Add New') }}</a>
                            <a v-bind:href="route('user.profile.professions')" class="btn btn-default btn-block">{{ trans('Professions List') }}</a>
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
                profession: {
                    profession_id: (this.params.profession && this.params.profession.profession_id) ? this.params.profession.profession_id : '',
                    parent_id: (this.params.profession && this.params.profession.parent_id) ? this.params.profession.parent_id : 0,
                },
            }
      },
        props: ['params'],
		methods: {

			onImageChange: function(e) {
                var files = e.target.files || e.dataTransfer.files;
                if (!files.length) return false;
                this.createImage(files[0]);
            },

			onImageChangeBackground: function(e) {
                var files = e.target.files || e.dataTransfer.files;
                if (!files.length) return false;
                this.createImageBackground(files[0]);
            },

            createImage: function(file) {
                var self = this,
                    reader = new FileReader();

                reader.onload = function(e) {
                    $(self.$el).find('.img-logo-link').attr('src', e.target.result);
                };
                reader.readAsDataURL(file);
            },

			createImageBackground: function(file) {
                var self = this,
                    reader = new FileReader();

                reader.onload = function(e) {
                    $(self.$el).find('.img-background-link').attr('src', e.target.result);
                };
                reader.readAsDataURL(file);
            },

            uploadImage: function() {
                $(this.$el).find('#profile-upload-input').click();
            },

			uploadImageBackground: function() {
                $(this.$el).find('#profile-upload-input-background').click();
            },

            onSubmit: function() {
                tinyMCE.triggerSave();

                var self = this,
					oldForm = document.forms.saveProfessionForm,
					formData = new FormData(oldForm);
                    //formData = $('#save-jobEntity-form').serialize();
				var imagefile = document.querySelector('#profile-upload-input');
				formData.append("imgLogoNew", imagefile.files[0]);

                axios.post('/save-profession', formData).then(function(response) {

                    self.messageData = response.data.message ? response.data.message : '';
                    self.errorsExist = response.data.errors_exist ? response.data.errors_exist : false;
                    self.errorsList = response.data.errors ? response.data.errors : [];
                    self.entity = response.data.entity ? response.data.entity : self.entity;

                    if(response.data.message === 'Done') {
                        setTimeout(function() {
                            window.location.href = response.data.route;
                        }, 500);
                    }

                }).catch(function(error) {

                    console.log(error);

                });
            }
        }
    }
</script>

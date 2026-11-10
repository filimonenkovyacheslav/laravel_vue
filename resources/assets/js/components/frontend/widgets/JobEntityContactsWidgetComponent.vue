<template>
    <div class="widget widget-contacts detail-block">
        <div class="detail-title" v-if="params.entity.user && params.entity.user.type!='administrator'">
            <h2 class="title-left">{{ trans('Contact info') }}</h2>
            <div class="title-right"></div>
        </div>
        <form action="/agent-send-message" id="agentSendMessage" enctype="multipart/form-data" method="POST">
            <div class="media agent-media" v-if="params.entity.user && params.entity.user.type!='administrator'">
                <div class="media-left figure-block">
                    <a v-if="params.entity.user.agency && params.entity.user.agency.id" v-bind:href="route(params.entity.user.agency.type + '.view.frontend', {'slug': getCompanySlug(params.entity.user.agency)})">
                        <div class="figure-image"><img :src="getImageUrl(params.entity.user.agency.photoImage.name)"></div>
                    </a>
                    <a v-else :href="route(params.entity.user.type + '.view.frontend', {'slug': getCompanySlug(params.entity.user)})">
                        <div class="figure-image"><img :src="getImageUrl(params.entity.user.photoImage.name)"></div>
                    </a>
                </div>
                <div class="media-body">
                    <strong>
                        <a :href="route(params.entity.user.type + '.view.frontend', {'slug': getCompanySlug(params.entity.user)})">{{ trans('View my listings') }}</a>
                    </strong>
                    <dl v-if="params.entity.user.agency && params.entity.user.agency.id">
                        <dd>
                            <!--<i class="fa fa-user"></i>-->
                            {{ getCompanyName(params.entity.user.agency) }}
                        </dd>
                        <dd v-if="params.entity.user.agency.phone">
                            <span>
                                <i class="fa fa-phone"></i>
                                <span class="clickToShowPhone" style="display: none;">{{ params.entity.user.agency.phone }}</span>
                                <span class="clickToShowButtonPhone">{{ params.entity.user.agency.phone.slice(0,4) }}...</span>
                            </span>
                        </dd>
                        <user-social-networks-list :user="params.entity.user.agency"></user-social-networks-list>
                    </dl>
                    <dl v-else>
                        <dd>
                            <!--<i class="fa fa-user"></i>-->
                            {{ getCompanyName(params.entity.user) }}
                        </dd>
                        <dd v-if="params.entity.user.phone">
                            <span>
                                <i class="fa fa-phone"></i>
                                <span class="clickToShowPhone" style="display: none;">{{ params.entity.user.phone }}</span>
                                <span class="clickToShowButtonPhone">{{ params.entity.user.phone.slice(0,4) }}...</span>
                            </span>
                        </dd>
                        <user-social-networks-list :user="params.entity.user"></user-social-networks-list>
                    </dl>
                </div>
            </div>
            <div class="detail-title-inner">
                <h4 class="title-inner">{{ trans('Apply for this job') }}
                    <a v-if="params.entity.user && params.entity.user.agency && params.entity.user.agency.id" v-bind:href="route(params.entity.user.type + '.view.frontend', {'slug': getCompanySlug(params.entity.user)})">
                        {{ params.entity.user.first_name + ' ' + params.entity.user.last_name }}
                    </a>
                </h4>
            </div>
            <input type="hidden" name="email_template" value="job_send_message">
            <input v-if="params.entity.user" type="hidden" name="user_first_name" :value="params.entity.user.first_name">
            <input v-if="params.entity.user" type="hidden" name="user_last_name" :value="params.entity.user.last_name">
            <input v-if="params.entity.user" type="hidden" name="user_slug" :value="params.entity.user.slug">
            <input type="hidden" name="entity_permalink" :value="route('jobEntity.view.frontend', {'slug': params.entity.slug})">
            <input type="hidden" name="entity_title" :value="params.entity.title">
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <div class="form-group">
                        <input class="form-control" name="from_name" value="" :placeholder="trans('Your Name')" type="text">
                    </div>
                </div>
                <div class="col-sm-12 col-xs-12">
                    <div class="form-group">
                        <input class="form-control" name="from_phone" value="" :placeholder="trans('Phone')" type="text">
                    </div>
                </div>
                <div class="col-sm-12 col-xs-12">
                    <div class="form-group">
                        <input class="form-control" name="from_email" value="" :placeholder="trans('Email')" type="email">
                    </div>
                </div>
				<div class="col-sm-12 col-xs-12">
                    <div class="form-group">
						<div class="job-widget-contacts-wrapper">
							<div class="job-widget-contacts-file-button" @click="uploadFiles">{{trans('Upload files')}}</div>
							<div class="job-widget-contacts-file-button-label">{{trans('Upload your resume, cover letter and relevant files')}}</div>
						</div>
                        <input style="display:none;" id="files" name="files[]" value="" type="file" multiple accept=".xlsx,.xls,image/*,.doc, .docx,.ppt, .pptx,.txt,.pdf">
						<div class="maxFileSize">*{{trans('Maximum uploaded files size 20 MB')}}</div>
						<div class="maxFileSizeError">*{{trans('You have exceeded the maximum file size')}}</div>
                    </div>
                </div>
                <div class="col-sm-12 col-xs-12">
                    <div class="form-group">
                        <textarea class="form-control" name="message" rows="5" :placeholder="trans('Message')"></textarea>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <recaptcha :captcha="captcha"></recaptcha>
            </div>
            <input type="hidden" name="_token" v-model="csrf">
            <button class="agent_contact_form btn btn-black">{{ trans('Send') }}</button>
            <div class="form_messages"></div>
        </form>
    </div>
</template>

<script>
    /**
     * Get data via props in blade template <vue_template :props></vue_template>
     */
    export default {
    props: ['params', 'captcha'],
		methods: {
			uploadFiles: function(e) {
				$('#files').click();
            },
			submitForm: function(e) {
				$("#agentSendMessage").submit(function(e){
					e.preventDefault();
					var file = document.getElementById("files").files[0];
					if (file) {
						var fileSize = file.size / 1024 / 1024;
					}
					if (fileSize < 20 || typeof file === 'undefined' ) {
						$("#agentSendMessage")[0].submit();
					} else {
						$('.maxFileSize').hide();
						$('.maxFileSizeError').fadeIn();
						setTimeout(function(){
							$('.maxFileSizeError').hide();
							$('.maxFileSize').fadeIn();
						}, 5000)
					}
				});
			}
		},
		mounted: function() {
			this.submitForm();
		}
	}
</script>

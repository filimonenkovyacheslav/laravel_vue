<template>
    <div class="profile-detail-block company-detail">
        <div class="row">
            <div class="col-md-3 col-sm-3 col-xs-12">
                <div class="profile-image figure-block">
                    <!--<div class="figure-image" :style="getBgImageStyle(params.user.photoImage.name)"></div>-->
                    <div class="figure-image"><img :src="this.getImageUrl(params.entity.logo.name)"></div>
                </div>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <div class="profile-description">
                    <p class="agent-title">{{ params.entity.title}}</p>
                    <h4 class="position">{{ params.entity.address ? params.entity.address : params.entity.map_address}}</h4>
                    <ul class="agency-contact">
                        <li><strong>Franchise founded:</strong> {{ params.entity.founded}}</li>
                        <li><strong>Franchise Fee:</strong> {{ params.entity.fee}}</li>
                        <li><strong>Investment Required:</strong> {{ params.entity.investment}}</li>
                        <li><strong>Agreement Terms:</strong> {{ params.entity.terms}}</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-3 col-sm-4 col-xs-12">
                <div class="form-small">
                    <p class="agent-contact-title">{{ trans('Inquire about this franchise') }}</p>
                    <form method="post" action="/agent-send-message" id="franchise_detail_contact_form">
                        <input type="hidden" name="email_template" value="franchise_send_message">
                        <input type="hidden" name="user_first_name" :value="params.entity.user.first_name">
                        <input type="hidden" name="user_last_name" :value="params.entity.user.last_name">
                        <input type="hidden" name="user_slug" :value="params.entity.user.slug">
                        <input type="hidden" name="entity_permalink" :value="route('franchise.view.frontend', {'slug': params.entity.slug})">
                        <input type="hidden" name="entity_title" :value="params.entity.title">
                        <div class="form-group">
                            <input type="text" name="from_name" id="from_name" :placeholder="trans('Your Name')" class="form-control">
                        </div>
                        <div class="form-group">
                            <input type="text" name="from_phone" id="from_phone" :placeholder="trans('Phone')" class="form-control">
                        </div>
                        <div class="form-group">
                            <input type="email" name="from_email" id="from_email" :placeholder="trans('Email')" class="form-control">
                        </div>
                        <div class="form-group">
                            <textarea class="form-control" name="message" rows="5" :placeholder="trans('Message')">{{ trans('Hello, I am interested in') }} {{ params.entity.title }}</textarea>
                        </div>
                        <div class="form-group" style="overflow: hidden; border-radius: 4px; border-right: 1px solid #d8d8d8; height: 62px;">
                            <recaptcha :captcha="captcha" compact="1"></recaptcha>
                        </div>
                        <input type="hidden" name="_token" v-model="csrf">
                        <button type="submit" id="agent_detail_contact_btn" class="btn btn-secondary btn-block">
                            {{ trans('SEND MESSAGE') }}                </button>
                    </form>
                    <div id="form_messages"></div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-12"></div>
        </div>
    </div>
</template>

<script>
    /**
     * Get data via props in blade template <vue_template :props></vue_template>
     */
    export default {
        props: ['params', 'captcha']
    }
</script>
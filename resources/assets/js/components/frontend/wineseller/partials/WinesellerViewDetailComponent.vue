<template>
    <div class="profile-detail-block company-detail">
        <div class="row">
            <div class="col-md-4 col-sm-4 col-xs-12">
                <div class="profile-image figure-block">
                    <div class="figure-image"><img :src="params.user.img_logo" :alt="params.user.first_name +' '+ params.user.last_name"></div>
                </div>
            </div>
            <div class="col-md-8 col-sm-8 col-xs-12">
                <div class="form-small">
                    <p class="agent-contact-title">{{ trans('CONTACT') }}&nbsp;{{ params.user.first_name}} {{ params.user.last_name}}</p>
                    <form method="post" action="/agent-send-message" id="agent_detail_contact_form">
                        <div class="row">
                            <div class="col-md-6 col-xs-12">
                                <div class="form-group">
                                    <input type="text" name="from_name" id="from_name" :placeholder="trans('Your Name')" class="form-control">
                                </div>
                                <div class="form-group">
                                    <input type="text" name="from_phone" id="from_phone" :placeholder="trans('Phone')" class="form-control">
                                </div>
                                <div class="form-group">
                                    <input type="email" name="from_email" id="from_email" :placeholder="trans('Email')" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6 col-xs-12">
                                <div class="form-group">
                                    <textarea name="message" :placeholder="trans('Message')" id="message" rows="3" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-xs-12">
                                <button v-if="params.user_role" type="submit" id="agent_detail_contact_btn" class="btn btn-black btn-block">{{ trans('SEND MESSAGE') }}</button>
                                <button class="btn btn-black btn-block" onclick="window.location.href=route('home')" v-else>{{ trans('SEND MESSAGE') }}</button>
                            </div>
                            <div class="col-md-6 col-xs-12">
                                <div class="form-group">
                                    <recaptcha :captcha="captcha" compact="1"></recaptcha>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="_token" v-model="csrf">
                        <input type="hidden" name="email_template" value="agent_send_message">
                        <input type="hidden" name="user_first_name" :value="params.user.first_name">
                        <input type="hidden" name="user_last_name" :value="params.user.last_name">
                        <input type="hidden" name="user_slug" :value="params.user.slug">
                        <input type="hidden" name="entity_permalink" :value="route(params.route_name, {'slug': params.user.slug})">
                    </form>
                    <div id="form_messages"></div>
                </div>
            </div>
            <div class="col-md-12 col-sm-12 col-xs-12">
                <ul class="detail-tabs nav nav-tabs" id="agencyTab" role="tablist">
                    <li v-if="params.user.description && params.user.description.length" class="nav-item">
                        <a class="nav-link active" id="overview-tab" data-toggle="tab" href="#overview" role="tab" aria-controls="overview" aria-selected="true">{{ trans('Overview') }}</a>
                    </li>
                    <li v-if="this.inArray(1, params.user.uploadsTypes)" class="nav-item">
                        <a class="nav-link" id="photo-tab" data-toggle="tab" href="#photo" role="tab" aria-controls="photo" aria-selected="false">{{ trans('Photos') }}</a>
                    </li>
                    <li v-if="this.inArray(2, params.user.uploadsTypes)" class="nav-item">
                        <a class="nav-link" id="video-tab" data-toggle="tab" href="#video" role="tab" aria-controls="video" aria-selected="false">{{ trans('Videos') }}</a>
                    </li>
                    <li v-if="params.user.wines && params.user.wines.total > 0" class="nav-item">
                        <a class="nav-link" id="wines-tab" data-toggle="tab" href="#wines" role="tab" aria-controls="wines" aria-selected="false">{{ trans('Wines') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="contacts-tab" data-toggle="tab" href=".widget.widget-contacts" role="tab" aria-controls="contacts" aria-selected="false">{{ trans('Contact') }}</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
    /**
     * Get data via props in blade template <vue_template :props></vue_template>
     */
    export default {
        props: ['params', 'captcha'],
        mounted: function() {
            $('a.nav-link').on('click', function() {
                var elementClick = $(this).attr('href');
                $('html').animate({ scrollTop: $(elementClick).offset().top }, 1100);
            });
        },
    }
</script>

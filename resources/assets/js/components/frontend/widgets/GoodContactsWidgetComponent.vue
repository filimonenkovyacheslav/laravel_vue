<template>
    <div class="widget widget-contacts detail-block">
        <div class="detail-title" v-if="params.entity.user && params.entity.user.type!='administrator'">
            <h2 class="title-left">{{ trans('Contact info') }}</h2>
            <div class="title-right"></div>
        </div>
        <form action="/agent-send-message" method="POST">
            <div class="media agent-media" v-if="params.entity.user && params.entity.user.type!='administrator'">
                <div class="media-left figure-block">
                    <a :href="route('seller.view.frontend', {'slug': getCompanySlug(params.entity.user)})">
                        <div class="figure-image"><img :src="getImageUrl(params.entity.user.photoImage.name)"></div>
                    </a>
                </div>
                <div class="media-body">
                    <strong>
                        <a :href="route('seller.view.frontend', {'slug': getCompanySlug(params.entity.user)})">{{ trans('View my Marketplace items') }}</a>
                    </strong>
                    <dl>
                        <dd>{{ getCompanyName(params.entity.user) }}</dd>
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
                <h4 class="title-inner">{{ trans('Contact') }}</h4>
            </div>
            <input type="hidden" name="email_template" value="property_send_message">
            <input v-if="params.entity.user" type="hidden" name="user_first_name" :value="params.entity.user.first_name">
            <input v-if="params.entity.user" type="hidden" name="user_last_name" :value="params.entity.user.last_name">
            <input v-if="params.entity.user" type="hidden" name="user_slug" :value="params.entity.user.slug">
            <input type="hidden" name="entity_permalink" :value="route('good.view.frontend', {'slug': params.entity.slug})">
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
                        <textarea class="form-control" name="message" rows="5" :placeholder="trans('Message')"></textarea>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <recaptcha :captcha="captcha"></recaptcha>
            </div>
            <input type="hidden" name="_token" v-model="csrf">
            <button class="agent_contact_form btn btn-black" v-if="params.user_role">{{ trans('Request info') }}</button>
            <button class="btn btn-black" onclick="window.location.href=route('home')" v-else>{{ trans('Request info') }}</button>
            <div class="form_messages"></div>
        </form>
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

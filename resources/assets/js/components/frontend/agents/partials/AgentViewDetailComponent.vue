<template>
    <div class="profile-detail-block">
        <div class="row">
            <div class="col-sm-4 col-xs-12">
                <div class="profile-image figure-block">
                    <div class="figure-image" :style="getBgImageStyle(params.user.photoImage.name)"></div>
                </div>
            </div>
            <div class="col-md-4 col-sm-8 col-xs-12">
                <div class="profile-description">
                    <user-social-networks-widget :user="params.user"></user-social-networks-widget>
                    <p class="agent-title">{{ params.user.first_name}} {{ params.user.last_name}}</p>
                    <!--<p v-if="params.user.agency_agent='agent' && params.user.agency" class="page-title-left">-->
                        <!--<a :href="route(params.user.agency.type + '.view.frontend', {'slug': getCompanySlug(params.user.agency)})">-->
                            <!--<span v-if="params.user.agency.company_name">{{ params.user.agency.company_name }}</span>-->
                            <!--<span v-else>{{ params.user.agency.first_name}} {{ params.user.agency.last_name}}</span>-->
                        <!--</a>-->
                    <!--</p>-->
                    <p class="position">{{ params.user.position}} at {{ params.user.company_name }}</p>
                    <p v-html="params.user.description"></p>
                    <user-contacts-list :user="params.user" :className="'profile-contact'"></user-contacts-list>
                    <user-social-networks-list :user="params.user" :className="'profile-social'"></user-social-networks-list>
                </div>
            </div>
            <div class="col-md-4 col-sm-12 col-xs-12">
                <div class="form-small">
                    <p class="agent-contact-title">{{ trans('CONTACT') }} {{ params.user.first_name}} {{ params.user.last_name}}</p>
                    <form method="post" action="/agent-send-message" id="agent_detail_contact_form">
                        <input type="hidden" name="email_template" value="agent_send_message">
                        <input type="hidden" name="user_name" :value="params.user.name">
                        <input type="hidden" name="user_first_name" :value="params.user.first_name">
                        <input type="hidden" name="user_last_name" :value="params.user.last_name">
                        <input type="hidden" name="user_slug" :value="params.user.slug">
                        <input type="hidden" name="entity_permalink" :value="route(params.route_name, {'slug': params.user.slug})">
                        <!--<input type="hidden" id="target_email" name="target_email" value="carl@phuket-properties.co.th">
                        <input type="hidden" name="agent_detail_ajax_nonce" id="agent_detail_ajax_nonce" value="3584871b49">
                        <input type="hidden" name="action" value="houzez_contact_agent">-->
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
                            <textarea name="message" id="message" rows="3" class="form-control">{{ trans('Hi') }} {{ params.user.first_name}} {{ params.user.last_name}}, {{ trans('I saw your profile on %s and wanted to see if you could help me').replace('%s', params.site_name) }}</textarea>
                        </div>
                        <input type="hidden" name="_token" v-model="csrf">
                        <button type="submit" id="agent_detail_contact_btn" class="btn btn-secondary btn-block">{{ trans('SEND MESSAGE') }}</button>
                    </form>
                    <div id="form_messages"></div>
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
        props: ['params']
    }
</script>

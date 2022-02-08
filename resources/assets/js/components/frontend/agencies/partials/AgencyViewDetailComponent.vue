<template>
    <div class="profile-detail-block company-detail">
        <div class="row">
            <div class="col-md-4 col-sm-4 col-xs-12">
                <div class="profile-image figure-block">
                    <div class="figure-image"><img :src="params.user.img_logo"></div>
                </div>
            </div>
            <!--<div class="col-md-4 col-sm-4 col-xs-12">-->
                <!--<div class="profile-description">-->
                    <!--<user-social-networks-widget :user="params.user"></user-social-networks-widget>-->
                    <!--<h3 v-if="params.user.is_agency">{{ getCompanyName(params.user) }}</h3>-->
                    <!--<span v-else>-->
                        <!--<p class="agent-title">{{ params.user.first_name}} {{ params.user.last_name}}</p>-->
                        <!--&lt;!&ndash;<p class="position">{{ params.user.position}} at {{ params.user.company_name }}</p>&ndash;&gt;-->
                        <!--<p class="position" v-if="params.user.agency && params.user.agency.id">-->
                            <!--at <a :href="route(params.user.agency.type + '.view.frontend', {'slug': getCompanySlug(params.user.agency)})" target="_blank">{{ getCompanyName(params.user.agency) }}</a>-->
                        <!--</p>-->
                    <!--</span>-->
                    <!--<h4 class="position">{{ params.user.address ? params.user.address : params.user.map_address}}</h4>-->
                    <!--<user-contacts-list :user="params.user" :className="'agency-contact'"></user-contacts-list>-->
                    <!--<user-social-networks-list :user="params.user" :className="'profile-social'"></user-social-networks-list>-->
                    <!--<ul class="profile-rating">-->
                        <!--<li v-if="params.user.properties.length>0">-->
                            <!--<span>Properties listed:</span>-->
                            <!--<span v-if="params.user.properties">{{ params.user.properties.length }}</span>-->
                            <!--<span v-else>0</span>-->
                        <!--</li>-->
                        <!--<li v-if="params.user.agency_agents=='agency'"><span>{{ params.agency_agents[params.user.type].title }}: </span>-->
                            <!--<span v-if="params.user.agents">{{ params.user.agents.length }}</span>-->
                            <!--<span v-else>0</span>-->
                        <!--</li>-->
                    <!--</ul>-->
                <!--</div>-->
            <!--</div>-->
            <div class="col-md-8 col-sm-8 col-xs-12">
                <div class="form-small">
                    <p v-if="params.user.is_agency" class="agent-contact-title">{{ trans('CONTACT') }}<br /> {{ params.user.company_name }}</p>
                    <p v-else class="agent-contact-title">{{ trans('CONTACT') }}<br /> {{ params.user.first_name}} {{ params.user.last_name}}</p>
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
                                    <textarea v-if="params.user.is_agency" name="message" :placeholder="trans('Message')" id="message" rows="3" class="form-control"></textarea>
                                    <textarea v-else name="message" :placeholder="trans('Message')" id="message" rows="3" class="form-control">{{ trans('Hi') }} {{ params.user.first_name}} {{ params.user.last_name}}, {{ trans('I saw your profile on %s and wanted to see if you could help me').replace('%s', params.site_name) }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-xs-12">
                                <button type="submit" id="agent_detail_contact_btn" class="btn btn-black btn-block">{{ trans('SEND MESSAGE') }}</button>
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
                        <!--<input type="hidden" id="target_email" name="target_email" value="austria@athome-network.com">
                        <input type="hidden" name="agent_detail_ajax_nonce" id="agent_detail_ajax_nonce" value="3584871b49">
                        <input type="hidden" name="action" value="houzez_contact_agent">-->
                    </form>
                    <div id="form_messages"></div>
                </div>
            </div>
            <div class="col-md-12 col-sm-12 col-xs-12">
                <ul class="detail-tabs nav nav-tabs" id="agencyTab" role="tablist">
                    <li v-if="params.user.description && params.user.description.length" class="nav-item">
                        <a class="nav-link active" id="overview-tab" data-toggle="tab" href="#overview" role="tab" aria-controls="overview" aria-selected="true">{{ trans('Overview') }}</a>
                    </li>
                    <!--<li class="nav-item">-->
                        <!--<a class="nav-link" id="hours-tab" data-toggle="tab" href="#hours" role="tab" aria-controls="hours" aria-selected="false">{{ trans('Opening Hours') }}</a>-->
                    <!--</li>-->
                    <li v-if="this.inArray(1, params.user.uploadsTypes)" class="nav-item">
                        <a class="nav-link" id="photo-tab" data-toggle="tab" href="#photo" role="tab" aria-controls="photo" aria-selected="false">{{ trans('Photos') }}</a>
                    </li>
                    <li v-if="this.inArray(2, params.user.uploadsTypes)" class="nav-item">
                        <a class="nav-link" id="video-tab" data-toggle="tab" href="#video" role="tab" aria-controls="video" aria-selected="false">{{ trans('Videos') }}</a>
                    </li>
                    <li v-if="params.user.agency_agents == 'agency' && params.user.agents && params.user.agents.total>0" class="nav-item">
                        <a class="nav-link" id="agents-tab" data-toggle="tab" href="#agents" role="tab" aria-controls="agents" aria-selected="false">{{ params.agency_agents[params.user.type].title }}</a>
                    </li>
                    <li v-if="params.user.designs && params.user.designs.total>0" class="nav-item">
                        <a class="nav-link" id="design-tab" data-toggle="tab" href="#designs" role="tab" aria-controls="design" aria-selected="false">
                            <template v-if="inArray(params.user.type, ['architect_firm', 'architect'])">{{ trans('Projects') }}</template>
                            <template v-else-if="inArray(params.user.type, ['building_company', 'building_company_agent'])">{{ trans('Projects') }}</template>
                            <template v-else>{{ trans('Designs') }}</template>
                        </a>
                    </li>
                    <li v-if="params.user.type != 'professional' && params.user.properties && params.user.properties.total>0" class="nav-item">
                        <a class="nav-link" id="listing-tab" data-toggle="tab" href="#listing" role="tab" aria-controls="listing" aria-selected="false">{{ trans('Listings') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="contacts-tab" data-toggle="tab" href=".widget.widget-contacts" role="tab" aria-controls="contacts" aria-selected="false">{{ trans('Contact') }}</a>
                    </li>
                    <!--<li class="nav-item">-->
                        <!--<a class="nav-link" id="location-tab" data-toggle="tab" href="#location" role="tab" aria-controls="location" aria-selected="false">{{ trans('Map') }}</a>-->
                    <!--</li>-->
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
            
            $(document).ready(function(){
                $('a.nav-link').eq(0).addClass('active');
            });
        },
    }
</script>

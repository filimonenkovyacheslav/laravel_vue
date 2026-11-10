<template>
<div id="section-body" :data-id="params.user.id" style="padding-top: 0;">
    <div class="page-media-header" :style="params.user.headerMedia.type == 1 ? { backgroundImage: `url('${params.user.img_background}')` } : ''">
        <template v-if="params.user.headerMedia.type == 2">
            <video class="profile-video-bg" loop="loop" autoplay="" muted="">
                <source :src="params.user.img_background" :type="getVideoType(params.user.headerMedia.name)"/>
            </video>
        </template>
        <div :class="['container', params.user.headerMedia.type == 2 ? 'container-video-bg' : '']">
            <div id="compare-controller" class="compare-panel">
                <div class="compare-panel-header">
                    <h4 class="title"> {{ trans('Compare Listings') }} <span class="panel-btn-close pull-right"><i class="fa fa-times"></i></span></h4>
                </div>
                <div id="compare-properties-basket"></div>
            </div>
            <div class="page-title breadcrumb-top">
                <div class="page-title-left">
                    <h1 v-if="params.user.is_agency" class="title-head">{{ getCompanyName(params.user) }}</h1>
                    <h1 v-else class="title-head">{{ params.user.first_name}} {{ params.user.last_name}}</h1>
                    <div class="address">{{ params.user.address ? params.user.address : params.user.map_address}}</div>
                </div>
                <div class="page-title-right">
                    <ol class="breadcrumb">
                        <li itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
                            <a itemprop="url" :href="route('home')">
                                <span itemprop="title">{{ trans('Home') }}</span>
                            </a>
                        </li>
                        <li v-if="params.user.is_agency" class="active">{{ getCompanyName(params.user) }}</li>
                        <li v-else class="active">{{ params.user.first_name}} {{ params.user.last_name}}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="page-content">
        <div class="page-content-header">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12">
                        <agency-view-frontend-detail :params="params" :captcha="captcha"></agency-view-frontend-detail>
                    </div>
                </div>
            </div>
        </div>
        <div class="page-content-body">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                        <div id="content-area">
                            <agency-view-frontend-tabs :params="params"></agency-view-frontend-tabs>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                        <aside id="sidebar" class="agency-sidebar sidebar-white">
                            <div class="widget widget-contacts">
                                <div class="detail-title">
                                    <span>{{ trans('Contact') }}</span>
                                </div>
                                <div class="profile-description">
                                    <user-social-networks-widget :user="params.user"></user-social-networks-widget>
                                    <div class="agent-title" v-if="params.user.is_agency">{{ getCompanyName(params.user) }}</div>
                                    <div v-else>
                                    <div class="agent-title">{{ params.user.first_name}} {{ params.user.last_name}}</div>
                                    <!--<div class="position">{{ params.user.position}} at {{ params.user.company_name }}</div>-->
                                    <div class="position" v-if="params.user.agency && params.user.agency.id">
                                        at <a :href="route(params.user.agency.type + '.view.frontend', {'slug': getCompanySlug(params.user.agency)})" target="_blank">{{ getCompanyName(params.user.agency) }}</a>
                                    </div>
                                    </div>
                                    <h4 class="address">{{ params.user.address ? params.user.address : params.user.map_address}}</h4>
                                    <user-contacts-list :user="params.user" :className="'agency-contact'"></user-contacts-list>
                                    <user-social-networks-list :user="params.user" :className="'profile-social'"></user-social-networks-list>
                                    <ul class="profile-rating">
                                        <li v-if="params.user.properties && params.user.properties.total>0">
                                            <span>Properties listed:</span>
                                            <span v-if="params.user.properties">{{ params.user.properties.total }}</span>
                                            <span v-else>0</span>
                                        </li>
                                        <li v-if="params.user.agency_agents=='agency' && params.user.agents.length"><span>{{ params.agency_agents[params.user.type].title }}: </span>
                                            <span v-if="params.user.agents">{{ params.user.agents.length }}</span>
                                            <span v-else>0</span>
                                        </li>
                                        <li v-if="params.user.designs && params.user.designs.total>0">
                                            <span>
                                                <template v-if="inArray(params.user.type, ['architect_firm', 'architect'])">{{ trans('Projects') }}:</template>
                                                <template v-else-if="inArray(params.user.type, ['building_company', 'building_company_agent'])">{{ trans('Projects') }}:</template>
                                                <template v-else>{{ trans('Designs') }}:</template>
                                            </span>
                                            <span>{{ params.user.designs.total }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
							<div v-if="params.user.opening_hours && params.user.opening_hours.length" class="widget">
                                <div class="detail-title">
                                    <span>{{ trans('Opening hours') }}</span>
                                </div>
                                <div class="detail-block profile-hours" v-html="params.user.opening_hours"></div>
                            </div>
                            <div v-if="params.opening_fields" class="widget">
                                <div class="detail-title">
                                    <span>{{ trans('Opening hours') }}</span>
                                </div>
                                <div class="row" v-for="value, k in params.opening_fields" v-if="params.user[k]">
                                    <div class="col-lg-5 col-md-5 col-sm-5 col-xs-5" v-html="value"></div>
                                    <div class="col-lg-7 col-md-7 col-sm-7 col-xs-7" v-html="params.user[k]"></div>
                                </div>
                            </div>
                            <div class="widget">
                                <div class="detail-title">
                                    <span>{{ trans('Map') }}</span>
                                </div>
                                <div class=" detail-block profile-map">
                                    <div class="map_canvas" id="map"></div>
                                </div>
                            </div>
                        </aside>
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
        props: ['params', 'captcha'],
        mounted: function() {
            var self = this;
            this.initMap('map', { 'lat': self.params.user.lat, 'lng': self.params.user.lng }, { draggable: false });
        }
    }
</script>
<style>
    .background-video{
        max-width: 100%;
        height: auto;
    }
</style>

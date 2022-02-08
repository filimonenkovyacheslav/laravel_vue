<template>
    <div id="section-body" :data-id="params.user.id" style="padding-top: 0;">
        <div class="page-media-header" :style="params.user.headerMedia.type == 1 ? { backgroundImage: `url('${params.user.img_background}')` } : ''">
            <template v-if="params.user.headerMedia.type == 1">
                <img :src="params.user.img_background" alt="" class="page-media-header-img">
            </template>
            <template v-else-if="params.user.headerMedia.type == 2">
                <video class="profile-video-bg" loop="loop" autoplay="" muted="">
                    <source :src="params.user.img_background" :type="getVideoType(params.user.headerMedia.name)"/>
                </video>
            </template>
            <div :class="['container', params.user.headerMedia.type == 2 ? 'container-video-bg' : '']">
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
                            <seller-view-frontend-detail :params="params" :captcha="captcha"></seller-view-frontend-detail>
                        </div>
                    </div>
                </div>
            </div>
            <div class="page-content-body">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                            <div id="content-area">
                                <seller-view-frontend-tabs :params="params"></seller-view-frontend-tabs>
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
                                        <div class="agent-title">{{ params.user.first_name}} {{ params.user.last_name}}</div>
                                        <h4 class="address">{{ params.user.address ? params.user.address : params.user.map_address}}</h4>
                                        <user-contacts-list :user="params.user" :className="'agency-contact'"></user-contacts-list>
                                        <user-social-networks-list :user="params.user" :className="'profile-social'"></user-social-networks-list>
                                        <ul class="profile-rating">
                                            <li v-if="params.user.arts && params.user.arts.total>0">
                                                <span>Art listed:</span>
                                                <span v-if="params.user.arts">{{ params.user.arts.total }}</span>
                                                <span v-else>0</span>
                                            </li>
                                        </ul>
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

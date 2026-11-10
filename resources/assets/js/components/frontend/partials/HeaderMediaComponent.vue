<template>
    <div class="header-media container-fluid">
        <div class="row">
            <div class="banner col-md-12 col-xs-12" :style="getBgImageStyle(params.homeimage.main_image.name)">
                <a v-if="params.homeimage.url" :href="params.homeimage.url" target="_blank">
                    <div class="homepage-video-container">
                        <video v-if="params.homeimage.main_image.type==2" preload="auto" autoplay="true" loop="loop" muted="muted" volume="0">
                            <source :src="getImageUrl(params.homeimage.main_image.name)">
                            {{ trans('Your browser does not support the video tag.') }}
                        </video>
                    </div>
                    <div class="row col-md-12 col-xs-12 homepage-logo-title">
                        <div class="col-md-6 col-xs-12">
                            <img v-if="params.homeimage.logo" :src="getImageUrl(params.homeimage.logo_image.name)">
                        </div>
                        <div class="col-md-6 col-xs-12" style="text-align: right;">
                            <div v-if="params.homeimage.title" class="homepage-image-title">{{ params.homeimage.title }}</div>
                        </div>
                    </div>
                </a>
                <div v-else>
                    <div class="homepage-video-container" v-if="params.homeimage.main_image.type==2">
                        <video preload="auto" autoplay="true" loop="loop" muted="muted" volume="0">
                            <source :src="getImageUrl(params.homeimage.main_image.name)">
                            {{ trans('Your browser does not support the video tag.') }}
                        </video>
                    </div>
                    <div class="row col-md-12 col-xs-12 homepage-logo-title">
                        <div class="col-md-6 col-xs-12">
                            <img v-if="params.homeimage.logo" :src="getImageUrl(params.homeimage.logo_image.name)">
                        </div>
                        <div class="col-md-6 col-xs-12" style="text-align: right;">
                            <div v-if="params.homeimage.title" class="homepage-image-title">{{ params.homeimage.title }}</div>
                        </div>
                    </div>
                </div>
                <a v-if="params.route_name == 'home'" :href="route('page.quotes')" class="btn btn-default main-quotes-button">{{ trans('Get Recommendations') }}</a>
                <search-bar v-if="params.route_name == 'home'" :params="params" :is_home="true"></search-bar>
                <quotes-bar :params="params" v-if="params.route_name == 'page.quotes'"></quotes-bar>
            </div>
        </div>
    </div>
</template>

<script>
    /**
     * Get data via props in blade template <vue_template :props></vue_template>
     */
    export default {
        props: ['params'],
    }
</script>

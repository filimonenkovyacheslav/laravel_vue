<template>
    <div class="detail-content-tabber">
        <div class="detail-bar">
            <div :class="'detail-content-tabber' + (params.entity.gallery ? ' detail-top detail-top-slideshow' : '')">
                <ul class="detail-tabs nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="description-tab" data-toggle="tab" href="#description" role="tab" aria-controls="description" aria-selected="true">{{ trans('Description') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="gallery-tab" data-toggle="tab" href="#gallery" role="tab" aria-controls="gallery" aria-selected="false">{{ trans('Gallery') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="downloads-tab" data-toggle="tab" href="#downloads" role="tab" aria-controls="downloads" aria-selected="false">{{ trans('Downloads') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="location-tab" data-toggle="tab" href="#location" role="tab" aria-controls="location" aria-selected="false">{{ trans('Map') }}</a>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
                        <div class="profile-overview detail-block" v-html="params.entity.description"></div>
                    </div>
                    <div class="tab-pane fade" id="gallery" role="tabpanel" aria-labelledby="gallery-tab">
                        <div class="profile-gallery detail-block">
                            <div class="detail-slider-wrap">
                                <div class="detail-slider owl-carousel owl-theme" data-slider-id="1">
                                    <div class="item user-item" v-for="item in params.entity.uploadsList" v-if="item.type!=0 && item.is_featured!=1" :style="item.type == 1 ? getBgImageStyle(item.name) : ''">
                                        <div v-if="item.caption" class="caption">
                                            <h3>{{ item.caption }}</h3>
                                        </div>
                                        <video controls style="width: 100%; max-height: 600px;" v-if="item.type == 2">
                                            <source :src="getImageUrl(item.name)">
                                            {{ trans('Your browser does not support the video tag.') }}
                                        </video>
                                    </div>
                                </div>
                                <div class="detail-slider-nav owl-thumbs" data-slider-id="1">
                                    <button class="owl-thumb-item" v-for="item in params.entity.uploadsList" v-if="item.type!=0 && item.is_featured!=1" :style="item.type == 1 ? getBgImageStyle(item.name) : ''">
                                        <video style="width: 100%; max-height: 75px;" disabled="disabled" v-if="item.type == 2">
                                            <source :src="getImageUrl(item.name)">
                                            {{ trans('Your browser does not support the video tag.') }}
                                        </video>
                                    </button>
                                    <div style="clear: both;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="downloads" role="tabpanel" aria-labelledby="downloads-tab">
                        <div class="profile-downloads detail-block">
                        	<ul>
	                            <li v-for="item in params.entity.uploadsList" v-if="item.type==0">
	                            	<a :href="'/uploads/' + item.name" target="_blank">{{ getClearFileName(item.name) }}</a>
	                            </li>
                            </ul>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="location" role="tabpanel" aria-labelledby="location-tab">
                        <div class="profile-map detail-block">
                            <div class="map_canvas" id="map"></div>
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
        props: ['params'],
        mounted: function() {
            this.initMap('map', { 'lat': this.params.entity.lat, 'lng': this.params.entity.lng }, { draggable: false });
            $('.owl-carousel').owlCarousel({
                items: 1,
                loop: true,
                nav : true,
                navText: ['<i class="fa fa-angle-left"></i>', '<i class="fa fa-angle-right"></i>'],
                dots: false,
                thumbs: true,
                thumbsPrerendered: true
            });
        }
    }
</script>

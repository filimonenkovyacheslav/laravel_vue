<template>
    <div class="detail-content-tabber">
        <div class="detail-bar">
            <div class="detail-content-tabber">
                <div class="tab-content" id="agencyTabContent">
                    <div v-if="params.user.description && params.user.description.length" class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab" style="display: block !important;opacity: 1 !important;">
                        <div class="profile-overview detail-block">
                            <div class="detail-title">
                                <span>{{ trans('Overview') }}</span>
                            </div>
                            <div v-html="params.user.description"></div>
                        </div>
                    </div>
                    <div v-if="params.user.services && params.user.services.length" class="tab-pane fade" id="services" role="tabpanel" aria-labelledby="services-tab" style="display: block !important;opacity: 1 !important;">
                        <div class="profile-overview detail-block">
                            <div class="detail-title">
                                <span>{{ trans('Products & Services') }}</span>
                            </div>
                            <div v-html="params.user.services"></div>
                        </div>
                    </div>
                    
                    <div v-if="params.user.projects && params.user.projects.length" class="tab-pane fade" id="projects" role="tabpanel" aria-labelledby="projects-tab" style="display: block !important;opacity: 1 !important;">
                        <div class="detail-projects detail-block slider-block">
                            <div class="detail-title">
                                <span>{{ trans('Projects') }}</span>
                            </div>
                            <div class="projects-pills nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                <a v-for="item, index in params.user.projects" v-html="item.title" :class="['nav-link', index ? '' : 'active']" :id="'v-pills-'+item.id+'-tab'" data-toggle="pill" :href="'#v-pills-'+item.id" role="tab" :aria-controls="'v-pills-'+item.id" aria-selected="true"></a>
                            </div>
                            <div class="projects-data tab-content" id="v-pills-tabContent">
                                <div v-for="item, index in params.user.projects" :class="['content', 'tab-pane', 'fade', 'show', index ? '' : 'active']" :id="'v-pills-'+item.id" role="tabpanel" :aria-labelledby="'v-pills-'+item.id+'-tab'">
                                    <div class="title" v-html="item.title"></div>
                                    <div class="description" v-html="item.description"></div>
                                    <div v-if="item.uploadsList && item.uploadsList.length" class="images detail-slider owl-carousel owl-theme">
                                        <div v-if="projImg.type == 1" v-for="projImg in item.uploadsList" class="item" :data-src="getImageUrl(projImg .name)" :style="getBgImageStyle(projImg .name)">
                                            <img :src="getImageUrl(projImg .name)" alt="" style="display: none;" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="this.inArray(1, params.user.uploadsTypes)" class="tab-pane fade" id="photo" role="tabpanel" aria-labelledby="photo-tab" style="display: block !important;opacity: 1 !important;">
                        <div class="detail-photo detail-block target-block">
                            <div class="detail-title">
                                <span>{{ trans('Photos') }}</span>
                            </div>
                            <div id="photo-slider" class="detail-slider-wrap" v-if="params.user.uploadsList && params.user.uploadsList.length">
                                <div class="detail-slider owl-carousel owl-theme">
                                    <div v-if="item.type == 1" v-for="item in params.user.uploadsList" class="item" :data-src="getImageUrl(item.name)" :style="getBgImageStyle(item.name)">
                                        <img :src="getImageUrl(item.name)" alt="" style="display: none;" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div v-if="this.inArray(2, params.user.uploadsTypes)" class="tab-pane fade" id="video" role="tabpanel" aria-labelledby="video-tab" style="display: block !important;opacity: 1 !important;">
                        <div class="detail-video detail-block target-block">
                            <div class="detail-title">
                                <span>{{ trans('Videos') }}</span>
                            </div>
                            <div id="video-slider" class="detail-slider-wrap" v-if="params.user.uploadsList && params.user.uploadsList.length">
                                <div class="detail-slider owl-carousel owl-theme" data-slider-id="3">
                                    <div class="item" v-if="item.type == 2" v-for="item in params.user.uploadsList">
                                        <video controls style="width: 100%; max-height: 600px;">
                                            <source :src="getImageUrl(item.name)">
                                            {{ trans('Your browser does not support the video tag.') }}
                                        </video>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="params.user.products && params.user.products.total>0" class="tab-pane fade" id="products" role="tabpanel" aria-labelledby="products-tab" style="display: block !important;opacity: 1 !important;">
                        <div class="profile-properties detail-block">
                            <div class="property-filter-wrap table-list">
                                <div class="detail-title no-margin table-cell">
                                    <span>{{ trans('Products') }}</span>
                                </div>
                                <div class="sort-tab table-cell text-right">
                                    <span>{{ trans('Sort by') }}:</span>
                                    <sort-order-selectbox :route_name="params.route_name"></sort-order-selectbox>
                                </div>
                            </div>
                            <div>
                                <div class="grid-view">
                                    <product-list-frontend-list-item :entities="params.user.products.data" :params="params"></product-list-frontend-list-item>
                                </div>
                                <pagination :pagination="params.user.products"></pagination>
                            </div>
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
        data: function() {
            return {
                photosCount: 0,
                videosCount: 0,
                projectsCount: 0
            }
        },
        props: ['params'],
        mounted: function() {
            //this.initMap('map', { 'lat': this.params.user.lat, 'lng': this.params.user.lng });
            for(var i = 0; i < this.params.user.uploadsList.length; i++) {
                switch(this.params.user.uploadsList[i].type) {
                    case 1:
                        this.photosCount++;
                        break;
                    case 2:
                        this.videosCount++;
                        break;
                    default:
                        break;
                }
            }
            $('.detail-projects .owl-carousel').owlCarousel({
                items: 1,
                loop: true,
                nav : true,
                navText: ['<i class="fa fa-angle-left"></i>', '<i class="fa fa-angle-right"></i>'],
                dots: false,
                thumbs: false
            });
            $('.detail-projects .owl-carousel').lightGallery({
                selector: '.owl-item:not(.cloned) .item'
            });
            this.projectsCount = this.params.user.projects.length;
            
            $('.detail-video .owl-carousel').owlCarousel({
                items: 1,
                loop: true,
                nav : true,
                navText: ['<i class="fa fa-angle-left"></i>', '<i class="fa fa-angle-right"></i>'],
                dots: false,
                thumbs: false
            });
            
            if(this.videosCount > 1) {
                $('.detail-video .detail-slider-wrap').addClass('navs-active');
            }
            
            jQuery(document).ready(function(){
                
                $('.detail-photo .owl-carousel').owlCarousel({
                    items: this.photosCount >= 3 ? 3 : this.photosCount,
                    margin: 5,
                    loop: true,
                    nav : true,
                    navText: ['<i class="fa fa-angle-left"></i>', '<i class="fa fa-angle-right"></i>'],
                    dots: false,
                    thumbs: false,
                    responsive: {
                        0:{
                            items: 1
                        },
                        800:{
                            items: this.photosCount >= 2 ? 2 : this.photosCount
                        },
                        1200:{
                            items: this.photosCount >= 3 ? 3 : this.photosCount
                        }
                    }
                });
                
                $('.detail-photo .owl-carousel').lightGallery({
                    selector: '.owl-item:not(.cloned) .item'
                });
                
                if(this.photosCount > 1) {
                    $('.detail-photo .detail-slider-wrap').addClass('navs-active');
                }
            });
        }
    }
</script>

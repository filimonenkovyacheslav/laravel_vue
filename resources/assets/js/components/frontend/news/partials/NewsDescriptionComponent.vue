<template>    
    <div class="detail-content-tabber">
        <div class="tab-content news-post-content" id="newsTabContent">            
            <div class="row detail-top detail-top-slideshow">
                <div class="col-sm-12 col-xs-12">
                    <div class="detail-media">
                        <div class="tab-content">
                            <div id="gallery" v-if="params.uploadsList && params.uploadsList[0].id != 0">
                                <property-view-frontend-labels :entityData="params" :className="'d-none'"></property-view-frontend-labels>
                                <div class="detail-slider-wrap">
                                    <div class="detail-slider owl-carousel owl-theme" data-slider-id="1">
                                        <div class="item" :class="(item.is_featured)?'active':''" v-for="item in params.uploadsList" :style="item.type == 1 ? getBgImageStyle(item.name) : ''">
                                            <video controls style="width: 100%; max-height: 600px;" v-if="item.type == 2">
                                                <source :src="getImageUrl(item.name)">
                                                {{ trans('Your browser does not support the video tag.') }}
                                            </video>
                                        </div>
                                    </div>
                                    <!-- <div class="detail-slider-nav owl-thumbs" data-slider-id="1">
                                        <button class="owl-thumb-item" v-for="item in params.uploadsList" :style="item.type == 1 ? getBgImageStyle(item.name) : ''">
                                            <video style="width: 100%; max-height: 75px;" disabled="disabled" v-if="item.type == 2">
                                                <source :src="getImageUrl(item.name)">
                                                {{ trans('Your browser does not support the video tag.') }}
                                            </video>
                                        </button>
                                        <div style="clear: both;"></div>
                                    </div> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="body-left table-cell">
                <div class="info-row">
                    <div class="label-wrap hide-on-grid"></div>
                    <h2 class="property-title">
                        <a :href="route('news.view.frontend', {'slug': slug})">{{ title }}</a>
                    </h2>                               
                </div>
            </div> 
            
            <div class="tab-pane fade" id="description" role="tabpanel" aria-labelledby="description-tab" style="display: block !important;opacity: 1 !important;">
                <div class="property-description detail-block target-block">
                    <p id="newsDescription">
                        <span v-html="(params.description.length > limit)?(descriptionArr[0]+' ...'):descriptionArr[0]">
                        </span>
                    </p>
                    <!-- <a v-if="params.description.length > limit" @click="readMore()" class="read-more">{{ trans('Read more') }}</a> -->
                    <a :href="route('news.view.frontend', {'slug': slug})">{{ trans('Read more') }}</a>
                </div>
            </div>
            
            <!-- <div v-if="this.inArray(1, params.uploadsTypes)" class="tab-pane fade" id="photo" role="tabpanel" aria-labelledby="photo-tab" style="display: block !important;opacity: 1 !important;">
                <div class="detail-photo detail-block target-block">
                    <div class="detail-title">
                        <span>{{ trans('Photos') }}</span>
                    </div>
                    <div id="photo-slider" class="detail-slider-wrap" v-if="params.uploadsList && params.uploadsList.length">
                        <div class="detail-slider owl-carousel owl-theme">
                            <div v-if="item.type == 1" v-for="item in params.uploadsList" class="item" :data-src="getImageUrl(item.name)" :style="getBgImageStyle(item.name)">
                                <img :src="getImageUrl(item.name)" alt="" style="display: none;" />
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
            <!-- <div v-if="this.inArray(2, params.uploadsTypes)" class="tab-pane fade" id="video" role="tabpanel" aria-labelledby="video-tab" style="display: block !important;opacity: 1 !important;">
                <div class="detail-video detail-block target-block">
                    <div class="detail-title">
                        <span>{{ trans('Videos') }}</span>
                    </div>
                    <div id="video-slider" class="detail-slider-wrap" v-if="params.uploadsList && params.uploadsList.length">
                        <div class="detail-slider owl-carousel owl-theme" data-slider-id="3">
                            <div class="item" v-if="item.type == 2" v-for="item in params.uploadsList">
                                <video controls style="width: 100%; max-height: 600px;">
                                    <source :src="getImageUrl(item.name)">
                                    {{ trans('Your browser does not support the video tag.') }}
                                </video>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->

            <div class="body-left table-cell">
                <hr>
                <h6 class="news-author">
                    <a :href="'/' + userSlug">{{ author }}</a>
                </h6>                               
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
                descriptionArr:[],
                limit: 500,
                title: '',
                author: '',
                slug: '',
                userSlug: '',
                photosCount: 0,
                videosCount: 0
            }
        },
        props: ['params'],
        mounted: function() {
            this.title = this.params.title;
            this.slug = this.params.slug;
            this.author = this.params.author_name.split(' (')[0];
            this.userSlug = this.params.user.slug;
            this.descriptionArr = this.chunkSubstr(this.params.description, this.limit);
                       
            $('#gallery .owl-carousel').owlCarousel({
                items: 1,
                loop: true,
                nav : true,
                navText: ['<i class="fa fa-angle-left"></i>', '<i class="fa fa-angle-right"></i>'],
                dots: false,
                thumbs: true,
                thumbsPrerendered: true
            });
             $('a.nav-link').on('click', function() {
                var elementClick = $(this).attr('href');
                $('html').animate({ scrollTop: $(elementClick).offset().top }, 1100);
            });

            for(var i = 0; i < this.params.uploadsList.length; i++) {
                switch(this.params.uploadsList[i].type) {
                    case 1:
                        this.photosCount++;
                        break;
                    case 2:
                        this.videosCount++;
                        this.params.uploadsList.splice(0, 0, this.params.uploadsList.splice(i, 1)[0]);
                        break;
                    default:
                        break;
                }
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
                
                $('.detail-video .owl-carousel').owlCarousel({
                    items: 1,
                    loop: true,
                    nav : true,
                    navText: ['<i class="fa fa-angle-left"></i>', '<i class="fa fa-angle-right"></i>'],
                    dots: false,
                    thumbs: false
                });
                $('.detail-photo .owl-carousel').lightGallery({
                    selector: '.owl-item:not(.cloned) .item'
                });
                $('.detail-projects .owl-carousel').lightGallery({
                    selector: '.owl-item:not(.cloned) .item'
                });
                if(this.photosCount > 1) {
                    $('.detail-photo .detail-slider-wrap').addClass('navs-active');
                }
                if(this.videosCount > 1) {
                    $('.detail-video .detail-slider-wrap').addClass('navs-active');
                }             
            });
        },
        methods:{
            chunkSubstr: function (str, size) {
                const numChunks = Math.ceil(str.length / size)
                const chunks = new Array(numChunks)
                for (let i = 0, o = 0; i < numChunks; ++i, o += size) {
                    chunks[i] = str.substr(o, size)
                }
                return chunks
            },
            readMore: function (){
                this.limit += 500
                this.descriptionArr = this.chunkSubstr(this.params.description, this.limit)
            }
        }
    }
</script>

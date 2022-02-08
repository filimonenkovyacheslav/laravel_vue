<template>
    <section :class="'detail-top' + (params.entity.uploadsList && params.entity.uploadsList.length ? ' detail-top-slideshow' : '')">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="header-detail">
                        <div class="header-left">
                            <div class="table-list">
                                <div class="table-cell">
                                    <h1>{{ params.entity.title }}</h1>
                                </div>
                            </div>
                            <property-view-frontend-price :entityData="params.entity" :className="''"></property-view-frontend-price>
                            <!--<address class="property-address">{{ params.entity.address }}</address>-->
                        </div>
                        <div class="header-right">
                            <div class="table-cell hidden-sm hidden-xs">
                                <property-view-frontend-labels  :entityData="params.entity" :className="''"></property-view-frontend-labels>
                            </div>
                            <property-view-frontend-actions :entityData="params.entity" :userRole="params.user_role" :items="['share-btn', 'favorite-btn', 'print-btn']"></property-view-frontend-actions>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <div class="detail-media">
                        <div class="tab-content">
                            <div id="gallery" v-if="params.entity.uploadsList && params.entity.uploadsList.length">
                                <property-view-frontend-labels :entityData="params.entity" :className="'d-none'"></property-view-frontend-labels>
                                <div class="detail-slider-wrap">
                                    <div class="detail-slider owl-carousel owl-theme" data-slider-id="1">
                                        <div class="item" v-for="item in params.entity.uploadsList" :style="item.type == 1 ? getBgImageStyle(item.name) : ''">
                                            <video controls style="width: 100%; max-height: 600px;" v-if="item.type == 2">
                                                <source :src="getImageUrl(item.name)">
                                                {{ trans('Your browser does not support the video tag.') }}
                                            </video>
                                        </div>
                                    </div>
                                    <div class="detail-slider-nav owl-thumbs" data-slider-id="1">
                                        <button class="owl-thumb-item" v-for="item in params.entity.uploadsList" :style="item.type == 1 ? getBgImageStyle(item.name) : ''">
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
                        <ul class="detail-tabs nav nav-tabs" id="propertyTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="description-tab" data-toggle="tab" href="#description" role="tab" aria-controls="description" aria-selected="true">{{ trans('Description') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="address-tab" data-toggle="tab" href="#address" role="tab" aria-controls="address" aria-selected="false">{{ trans('Address') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="detail-tab" data-toggle="tab" href="#detail" role="tab" aria-controls="detail" aria-selected="false">{{ trans('Details') }}</a>
                            </li>
                            <li v-if="params.entity.featuresList && params.entity.featuresList.length" class="nav-item">
                                <a class="nav-link" id="features-tab" data-toggle="tab" href="#features" role="tab" aria-controls="features" aria-selected="false">{{ trans('Features') }}</a>
                            </li>
                            <li v-if="this.inArray(1, params.entity.uploadsTypes)" class="nav-item">
                                <a class="nav-link" id="photo-tab" data-toggle="tab" href="#photo" role="tab" aria-controls="photo" aria-selected="false">{{ trans('Photos') }}</a>
                            </li>
                            <li v-if="this.inArray(2, params.entity.uploadsTypes)" class="nav-item">
                                <a class="nav-link" id="video-tab" data-toggle="tab" href="#video" role="tab" aria-controls="video" aria-selected="false">{{ trans('Video') }}</a>
                            </li>
                            <li v-if="params.entity.floors && params.entity.floors.length" class="nav-item">
                                <a class="nav-link" id="floor-plans-tab" data-toggle="tab" href="#floor-plans" role="tab" aria-controls="floor-plans" aria-selected="false">{{ trans('Floors Plans') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="contacts-tab" data-toggle="tab" href=".widget.widget-contacts" role="tab" aria-controls=".widget.widget-contacts" aria-selected="false">{{ trans('Contact') }}</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
    /**
     * Get data via props in blade template <vue_template :props></vue_template>
     */
    export default {
        props: ['params'],
        mounted: function() {
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
        }
    }
</script>

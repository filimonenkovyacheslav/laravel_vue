<template>
    <div class="detail-content-tabber">
        <div class="tab-content" id="propertyTabContent">
            <div class="tab-pane fade" id="description" role="tabpanel" aria-labelledby="description-tab" style="display: block !important;opacity: 1 !important;">
                <div class="property-description detail-block target-block">
                    <div class="detail-title">
                        <img src="/images/items_icons/overview.png" />
                        <span>{{ trans('Description') }}</span>
                    </div>
                    <p v-html="params.entity.description" id="propertyDescription"></p>
                    <!--<input type="button" id="btnTranslate" value="Translate" />-->
                </div>
            </div>
            <div class="tab-pane fade" id="address" role="tabpanel" aria-labelledby="address-tab" style="display: block !important;opacity: 1 !important;">
                <div class="detail-address detail-block target-block">
                    <div class="detail-title">
                        <img src="/images/items_icons/map.png" />
                        <span>{{ trans('Address') }}</span>
                        <div v-if="params.entity.address || params.entity.map_address" class="title-right">
                            <a target="_blank" :href="'http://maps.google.com/?q=' + (params.entity.address ? params.entity.address : params.entity.map_address)">{{ trans('Open on Google Maps') }} <i class="fa fa-map-marker"></i></a>
                        </div>
                    </div>
                    <ul class="list-three-col">
                        <li class="detail-city"><strong>{{ trans('City') }}: </strong>{{ params.entity.city }}</li>
                        <li class="detail-country"><strong>{{ trans('Country') }}: </strong><span v-if="params.entity.country!=null">{{ params.entity.country.name }}</span></li>
                    </ul>
                </div>
            </div>
            <div class="tab-pane fade" id="detail" role="tabpanel" aria-labelledby="detail-tab" style="display: block !important;opacity: 1 !important;">
                <div class="detail-list detail-block target-block">
                    <div class="detail-title">
                        <img src="/images/items_icons/detail.png" />
                        <span>{{ trans('Detail') }}</span>
                    </div>
                    <ul class="list-three-col">
                        <li><strong>{{ trans('Property ID') }}:</strong> {{ params.entity.id }}</li>
                        <li v-if="!params.entity.price_hidden">
                            <strong>{{ trans('Price') }}:</strong>
                                <span v-if="params.entity.price_default > 1">
                                    <span>{{ params.entity.price_view.local.price }}</span>
                                    <span v-if="params.entity.price_view.default.price != params.entity.price_view.local.price"> / </span>
                                    <span v-if="params.entity.price_view.default.price != params.entity.price_view.local.price">{{ params.entity.price_view.default.price }}</span>
                                </span>
                            <span v-else>POA</span>
                        </li>
                        <li v-if="params.entity.property_area_view.sqmeter.value || params.entity.property_area_view.sqfeet.value"><strong>{{ trans('Size') }}:</strong>
                            <span v-if="params.entity.property_area_view.sqmeter.value">{{ params.entity.property_area_view.sqmeter.value }}{{ params.entity.property_area_view.sqmeter.symbol }}</span>
                            <span v-if="params.entity.property_area_view.sqfeet.value"> / </span>
                            <span v-if="params.entity.property_area_view.sqfeet.value">{{ params.entity.property_area_view.sqfeet.value }}{{ params.entity.property_area_view.sqfeet.symbol }}</span>
                        </li>
                        <li v-if="params.entity.bedrooms"><strong>{{ trans('Bedrooms') }}:</strong> {{ params.entity.bedrooms }}</li>
                        <li v-if="params.entity.bathrooms"><strong>{{ trans('Bathrooms') }}:</strong> {{ params.entity.bathrooms }}</li>
                        <li v-if="params.entity.garage"><strong>{{ trans('Garages') }}:</strong> {{ params.entity.garage }}</li>
                        <li v-if="params.entity.land_area_view.sqmeter.value || params.entity.land_area_view.sqfeet.value"><strong>{{ trans('Land') }}:</strong>
                            <span v-if="params.entity.land_area_view.sqmeter.value">{{ params.entity.land_area_view.sqmeter.value }}{{ params.entity.land_area_view.sqmeter.symbol }}</span>
                            <span v-if="params.entity.land_area_view.sqfeet.value"> / </span>
                            <span v-if="params.entity.land_area_view.sqfeet.value">{{ params.entity.land_area_view.sqfeet.value }}{{ params.entity.land_area_view.sqfeet.symbol }}</span>
                        </li>
                        <li v-if="params.entity.garage_area_view.sqmeter.value || params.entity.garage_area_view.sqfeet.value"><strong>{{ trans('Garage') }}:</strong>
                            <span v-if="params.entity.garage_area_view.sqmeter.value">{{ params.entity.garage_area_view.sqmeter.value }}{{ params.entity.garage_area_view.sqmeter.symbol }}</span>
                            <span v-if="params.entity.garage_area_view.sqfeet.value"> / </span>
                            <span v-if="params.entity.garage_area_view.sqfeet.value">{{ params.entity.garage_area_view.sqfeet.value }}{{ params.entity.garage_area_view.sqfeet.symbol }}</span>
                        </li>
                        <li v-if="params.entity.property_status_view.label" class="prop_status"><strong>{{ trans('Status') }}:</strong> {{ params.entity.property_status_view.label }}</li>
                        <li v-if="params.entity.property_type_view.label" class="prop_type"><strong>{{ trans('Type') }}:</strong> {{ params.entity.property_type_view.label }}</li>
                        <li v-if="params.entity.property_subtype_view.label" class="prop_subtype"><strong>{{ trans('Subtype') }}:</strong> {{ params.entity.property_subtype_view.label }}</li>
                    </ul>
                </div>
            </div>
            <div v-if="params.entity.featuresList && params.entity.featuresList.length" class="tab-pane fade" id="features" role="tabpanel" aria-labelledby="features-tab" style="display: block !important;opacity: 1 !important;">
                <div class="detail-features detail-block target-block">
                    <div class="detail-title">
                        <img src="/images/items_icons/features.png" />
                        <span>{{ trans('Features') }}</span>
                    </div>
                    <ul class="list-three-col list-features">
                        <li v-for="item in params.entity.featuresList">
                            <a :href="route('property.list.frontend', {'params': '?features[]=' + item.feature_id})">
                                <i class="fa fa-check"></i>{{ item.name }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div v-if="this.inArray(1, params.entity.uploadsTypes)" class="tab-pane fade" id="photo" role="tabpanel" aria-labelledby="photo-tab" style="display: block !important;opacity: 1 !important;">
                <div class="detail-photo detail-block target-block">
                    <div class="detail-title">
                        <img src="/images/items_icons/photo.png" />
                        <span>{{ trans('Photos') }}</span>
                    </div>
					<div id="photo-slider" class="detail-slider-wrap" v-if="params.entity.uploadsList && params.entity.uploadsList.length">
						<div class="detail-slider owl-carousel owl-theme">
							<div v-if="item.type == 1" v-for="item in params.entity.uploadsList" class="item" :data-src="getImageUrl(item.name)" :style="getBgImageStyle(item.name)">
								<img :src="getImageUrl(item.name)" alt="" style="display: none;" />
							</div>
						</div>
					</div>
                </div>
            </div>
            <div v-if="this.inArray(2, params.entity.uploadsTypes)" class="tab-pane fade" id="video" role="tabpanel" aria-labelledby="video-tab" style="display: block !important;opacity: 1 !important;">
                <div class="detail-video detail-block target-block">
                    <div class="detail-title">
                        <img src="/images/items_icons/video.png" />
                        <span>{{ trans('Video') }}</span>
                    </div>
                    <div id="video-slider" class="detail-slider-wrap" v-if="params.entity.uploadsList && params.entity.uploadsList.length">
                        <div class="detail-slider owl-carousel owl-theme" data-slider-id="3">
                            <div class="item" v-if="item.type == 2" v-for="item in params.entity.uploadsList">
                                <video controls style="width: 100%; max-height: 600px;">
                                    <source :src="getImageUrl(item.name)">
                                    {{ trans('Your browser does not support the video tag.') }}
                                </video>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="params.entity.floors && params.entity.floors.length" class="tab-pane fade" id="floor-plans" role="tabpanel" aria-labelledby="floor-plans-tab" style="display: block !important;opacity: 1 !important;">
                <div class="detail-features detail-block target-block">
                    <div class="detail-title">
                        <img src="/images/items_icons/overview.png" />
                        <span>{{ trans('Floor Plans') }}</span>
                    </div>
                    <div class="floors-list-view">
                        <div v-for="item, order in params.entity.floors" :key="item.id" class="floor-item">
                            <h4 v-if="item.title"><strong>{{ item.title }}</strong></h4>
                            <div v-if="item.price">
                                <strong>{{ trans('Price') }}:</strong>
                                <span v-if="item.price_default > 1">
                                    <span>{{ item.price_view.local.price }}</span>
                                    <span v-if="item.price_view.default.price != item.price_view.local.price"> / </span>
                                    <span v-if="item.price_view.default.price != item.price_view.local.price">{{ item.price_view.default.price }}</span>
                                </span>
                            </div>
                            <div v-if="item.area_size_view.sqmeter.value || item.area_size_view.sqfeet.value">
                                <strong>{{ trans('Area Size') }}:</strong>
                                <span>
                                    <span v-if="item.area_size_view.sqmeter.value">{{ item.area_size_view.sqmeter.value }}{{ item.area_size_view.sqmeter.symbol }}</span>
                                    <span v-if="item.area_size_view.sqfeet.value"> / </span>
                                    <span v-if="item.area_size_view.sqfeet.value">{{ item.area_size_view.sqfeet.value }}{{ params.entity.land_area_view.sqfeet.symbol }}</span>
                                </span>
                            </div>
                            <div v-if="item.bedrooms"><strong>{{ trans('Bedrooms') }}:</strong> {{ item.bedrooms }}</div>
                            <div v-if="item.bathrooms"><strong>{{ trans('Bathrooms') }}:</strong> {{ item.bathrooms }}</div>
                            <div v-if="item.description"><strong>{{ trans('Description') }}:</strong> {{ item.description }}</div>
                            <hr />
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
                videosCount: 0
            }
        },
        props: ['params'],
        mounted: function() {
            for(var i = 0; i < this.params.entity.uploadsList.length; i++) {
                switch(this.params.entity.uploadsList[i].type) {
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

			})

            /*$("#btnTranslate").click(function () {
                var url = "https://translation.googleapis.com/language/translate/v2?key=AIzaSyBMcdCg1AF2Ckgthqw2LcR_3gfxCrBcHm8";
                url += "&source=" + 'en';
                url += "&target=" + 'ru';
                url += "&q=" + escape($('#propertyDescription').text());
                $.get(url, function (data, status) {
                    $('#propertyDescription').text(data.data.translations[0].translatedText);
                });
            });*/
        }
    }
</script>

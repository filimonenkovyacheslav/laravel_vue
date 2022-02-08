<template>
    <div class="detail-content-tabber">
        <div class="tab-content" id="jobEntityTabContent">
            <div class="tab-pane fade" id="description" role="tabpanel" aria-labelledby="description-tab" style="display: block !important;opacity: 1 !important;">
				<div class="jobEntity-description detail-block target-block">
                    <div class="detail-title">
                        <img src="/images/items_icons/overview.png" />
                        <span>{{ trans('Description') }}</span>
                    </div>
                    <p v-html="params.entity.description" id="jobEntityDescription"></p>
                    <!--<input type="button" id="btnTranslate" value="Translate" />-->
                </div>
            </div>
            <div v-if="params.entity.address || params.entity.map_address" class="tab-pane fade" id="address" role="tabpanel" aria-labelledby="address-tab" style="display: block !important;opacity: 1 !important;">
            <div class="detail-address detail-block target-block">
                    <div class="detail-title">
                        <img src="/images/items_icons/map.png" />
                        <span>{{ trans('Address') }}</span>
                        <div v-if="params.entity.address || params.entity.map_address" class="title-right">
                            <a target="_blank" :href="'http://maps.google.com/?q=' + (params.entity.address ? params.entity.address : params.entity.map_address)">{{ trans('Open on Google Maps') }} <i class="fa fa-map-marker"></i></a>
                        </div>
                    </div>
                    <ul class="list-three-col">
                        <li class="detail-city"><strong>{{ trans('City') }}: </strong><span v-if="params.entity.city">{{ params.entity.city }}</span></li>
                        <li class="detail-state"><strong>{{ trans('State') }}: </strong><span v-if="params.entity.state">{{ params.entity.state }}</span></li>
                    </ul>
                </div>
            </div>

			<div v-if="this.inArray(1, params.entity.uploadsTypes)" class="tab-pane fade" id="photo" role="tabpanel" aria-labelledby="photo-tab" style="display: block !important;opacity: 1 !important;">
				<div class="detail-photo detail-block slider-block">
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
				<div class="detail-video detail-block slider-block">
					<div class="detail-title">
						<img src="/images/items_icons/video.png" />
						<span>{{ trans('Video') }}</span>
					</div>
					<div id="video-slider" class="detail-slider-wrap" v-if="params.entity.uploadsList && params.entity.uploadsList.length">
						<div class="detail-slider owl-carousel owl-theme">
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
			$('.detail-projects .owl-carousel').owlCarousel({
                items: 1,
                loop: true,
                nav : false,
                dots: true,
                thumbs: false
            });
			$('.detail-photo .owl-carousel').lightGallery({
			    selector: '.owl-item:not(.cloned) .item'
			});
			$('.detail-projects .owl-carousel').lightGallery({
			    selector: '.owl-item:not(.cloned) .item'
			});
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

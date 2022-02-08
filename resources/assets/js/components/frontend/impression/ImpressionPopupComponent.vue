<template>
    <div>
        <div class="figure-block">
            <figure class="item-thumb">
                <a class="hover-effect" href="#" @click="show">
                    <div v-if="property.type == 1" class="figure-image" v-bind:style="getBgImageStyle(property.name)"></div>
                    <div v-if="property.type == 2" class="figure-image">
                        <video style="width: 100%;" disabled="disabled">
                            <source :src="getImageUrl(property.name)">
                            {{ trans('Your browser does not support the video tag.') }}
                        </video>
                    </div>
                </a>
            </figure>
        </div>
        <modal :name="'popup-slider'+property.id">
            <div class="modal-mask">
                <div class="modal-wrapper">
                    <div class="modal-container">
                        <div class="modal-body">
                            <div class="detail-slider-wrap">
                                <div class="detail-slider owl-carousel owl-theme" data-slider-id="1">
                                    <div class="item" style="width: 100%;" v-for="item in property.uploads" :style="item.type == 1 ? getBgImageStyle(item.name) : ''">
                                        <video controls style="width: 100%;" v-if="item.type == 2">
                                            <source :src="getImageUrl(item.name)">
                                            {{ trans('Your browser does not support the video tag.') }}
                                        </video>
                                    </div>
                                </div>
                                <div class="detail-slider-nav owl-thumbs" data-slider-id="1">
                                    <button class="owl-thumb-item" v-for="item in property.uploads" :style="item.type == 1 ? getBgImageStyle(item.name) : ''">
                                        <video style="width: 100%; max-height: 75px;" disabled="disabled" v-if="item.type == 2">
                                            <source :src="getImageUrl(item.name)">
                                            {{ trans('Your browser does not support the video tag.') }}
                                        </video>
                                    </button>
                                    <div style="clear: both;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-close">
                            <button class="btn" @click="hide">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </modal>
    </div>
</template>

<script>
    export default {
        props: ['property'],
        mounted: function() {
            this.popupName = 'popup-slider' + this.property.id;
        },
        methods: {
            hide: function(e) {
                this.$modal.hide(this.popupName);
            },
            show: function(e) {
                
                this.$modal.show(this.popupName, {width: '90%', height: '90%'});
                 var self = this;
                
                setTimeout(function() {
                    $('.modal-container').css({'width': (window.innerWidth * 0.9) + 'px', 'height': (window.innerHeight * 0.9) + 'px'});
                    $('.modal-container').css({'max-width': (window.innerWidth * 0.9) + 'px', 'max-height': (window.innerHeight * 0.9) + 'px'});
                    var popupWidth = $('.modal-container').width(),
                        popupHeight = $(window).innerHeight() * 0.9 - 70;
                
                    $('.owl-carousel').owlCarousel({
                        items: 1,
                        loop: true,
                        nav : true,
                        navText: ['<i class="fa fa-angle-left"></i>', '<i class="fa fa-angle-right"></i>'],
                        dots: false,
                        thumbs: true,
                        thumbsPrerendered: true
                    });

                    self.resizeSlider(popupWidth, popupHeight);

                    $(window).on('resize', function() {
                        $('.modal-container').css({'width': (window.innerWidth * 0.9) + 'px', 'height': (window.innerHeight * 0.9) + 'px'});
                        $('.modal-container').css({'max-width': (window.innerWidth * 0.9) + 'px', 'max-height': (window.innerHeight * 0.9) + 'px'});                      

                        self.resizeSlider($('.modal-container').width(), $('.modal-container').height());

                    }).trigger('resize');
                }, 10);

                return false;
            }
        }
    }
</script>

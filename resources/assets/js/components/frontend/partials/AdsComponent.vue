<template>
    <div class="ads-wrapper mb-4" v-if="params.ads">
        <div class="row">
            <div class="col-lg-8">
                <template v-if="entity.file_type && entity.file_type == 1">
                    <div class="ads-image-box d-flex align-items-center" style="width:700px;height:300px;overflow:hidden">
                        <a :href="entity.url" v-if="entity.title" class="ads-element" target="_blank">
                            <img :src="entity.file_link" :alt="entity.title" height="310">
                        </a>
                    </div>
                </template>
                <template v-else>
                    <div id="ads-video-youtube"></div>
                    <template v-if="entity && entity.file_link && entity.file_link.indexOf('uploads/') > -1">
                        <video width="700" height="310" class="ads-video-box" playsinline autoplay muted controls>
                            <source :src="entity.file_link">
                        </video>
                    </template>
                    <template v-else>
                        <iframe class="ads-video-box" :src="entity.file_link" width="700" height="310" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; muted" allowfullscreen></iframe>
                    </template>
                </template>
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
        created: function() {
            var self = this;
            this.entity = this.params.ads;
            this.YTPlayer = require('yt-player');         
        },
        mounted: function(){
            var self = this;
            var src = this.entity ? this.entity.file_link : undefined;
            if (typeof src === 'undefined' || typeof this.entity === 'undefined') {
                $('.ads-wrapper').remove();
                return false;
            }
            
            var ytBox = $('#ads-video-youtube'),
                vIframe = $('.ads-video-box'),
                videoCode;
            if (ytBox.length) {
                var isYoutube = (src.match(/youtube.com/i)) ? true : false;
                if (isYoutube) {
                    var isYoutubeEmbed = (src.match(/youtube.com\/embed/i)) ? true : false;
                    if (!isYoutubeEmbed) {
                        var url = new URL(src);
                        videoCode = url.searchParams.get("v");
                    } else {
                        videoCode = new URL(src).pathname.split('/').pop();
                    }
                    
                    vIframe.remove();
                    
                    var player = new this.YTPlayer(ytBox[0], {
                        height: 310,
                        width: 700,
                        autoplay: true
                    });
                    player.load(videoCode);
                    player.play();
                } else {
                    ytBox.remove();
                }
                
                var autoplay = src.indexOf("autoplay");
                if (autoplay == -1) {
                    if (src.indexOf("?") == -1) {
                        src += '?autoplay=1';
                    } else {
                        src += '&autoplay=1';
                    }
                }
                
                src += '&muted=1&#t=0s';
                
                this.entity.file_link = src;
                //vIframe.attr('src', src);
                
                $(document).ready(function(){
                    var first = true,
                        video = vIframe.get(0);
                    
                    video.addEventListener('canplay', function() {
                        if (first) {
                            video.click();
                            video.muted = true;
                            video.play();
                            first = false;
                        }
                    });
                });
            }
        },
        methods: {
            unmutedVideo: function(video){
                var interval = setInterval(function(){
                    if (video.muted || video.paused) {
                        video.muted = false;
                        video.play();
                        clearInterval(interval);
                    }
                },1000);
            }
        }
    }
</script>

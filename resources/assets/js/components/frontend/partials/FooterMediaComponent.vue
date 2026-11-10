<template>
    <div class="ads-wrapper" id="ads-footer" v-if="params.footerimage.main_image && params.footerimage.main_image.name">
        <div class="row">
            <div class="ads-footer-wrapper col-12">
                <template v-if="params.footerimage.main_image.type!=2">
                    <div class="ads-image-box">
                        <a v-if="params.footerimage.url" :href="params.footerimage.url" target="_blank">
                            <img :src="getImageUrl(params.footerimage.main_image.name)" alt="Footer Ads">
                        </a>
                        <div v-else>
                            <img :src="getImageUrl(params.footerimage.main_image.name)" alt="Footer Ads">
                        </div>
                    </div>
                </template>
                <template v-else>
                    <a v-if="params.footerimage.url" :href="params.footerimage.url" target="_blank">
                        <video preload="auto" autoplay="true" loop="loop" muted="muted" volume="0" style="width:100%">
                            <source :src="getImageUrl(params.footerimage.main_image.name)">
                                {{ trans('Your browser does not support the video tag.') }}
                        </video>
                    </a>
                    <div v-else>
                        <video preload="auto" autoplay="true" loop="loop" muted="muted" volume="0" style="width:100%">
                            <source :src="getImageUrl(params.footerimage.main_image.name)">
                                {{ trans('Your browser does not support the video tag.') }}
                        </video>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "FooterMediaComponent",
        props: ['params'],
        mounted: function(){
            //$('#ads-footer').appendTo('#section-body>.container');
            $('#section-body>.container, .section-detail-content>.container, .page-content-body>.container').append(this.$el);
        }
    }
</script>

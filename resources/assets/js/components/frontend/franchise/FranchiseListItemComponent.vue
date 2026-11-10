<template>
    <div class="franchise-listing">
        <div class="agency-block" :data-id="item.id" v-for="item, index in entities" :data-property-id="item.id">
            <div class="media">
                <div class="media-left">
                    <figure class="figure-block">
                        <!--<span class="label-featured label label-success">{{ trans('Featured') }}</span>
                        <a :href="route('franchise.view.frontend', {'slug': item.slug})">
                            <div class="figure-image"><img :src="getImageUrl(getFeaturedImageName(item.uploadsList))"></div>
                        </a>-->
                        <a class="hover-effect" :href="route('franchise.view.frontend', {'slug': item.slug})">
                            <div class="figure-image"><img :src="getImageUrl(item.logo.name)"></div>
                        </a>
                    </figure>
                </div>
                <div class="media-body franchise-body">
                    <div class="agency-body-left">
                        <div class="agency-description">
                            <h2 class="property-title">
                                <a :href="route('franchise.view.frontend', {'slug': item.slug})">{{ item.title }}</a>
                            </h2>
                            <h4 class="position">{{ item.address ? item.address : item.map_address}}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['params', 'entities'],
        mounted: function() {
            var self = this;
            $('#sort-users').on('change', function() {
                window.location = self.modifyUrl('order_by', $(this).val());
            });
        }
    }
</script>
<template>
    <div :class="['agency-listing', 'grid-view', params.route_name == 'professional.list.frontend' || isGrid ? '' : 'grid-view-3-col']">
        <div class="row">
            <div class="agency-block item-wrap" :data-id="item.id" v-for="item, index in entities">
                <div class="media">
                    <!--<div class="media-left">-->
                        <!--<figure class="figure-block">-->
                            <!--&lt;!&ndash;<span class="label-featured label label-success">{{ trans('Featured') }}</span>&ndash;&gt;-->
                            <!--<a :href="route(item.type + '.view.frontend', {'slug': getCompanySlug(item)})">-->
                                <!--&lt;!&ndash;<div class="figure-image" :style="getBgImageStyle(item.photoImage.name)"></div>&ndash;&gt;-->
                                <!--<div class="figure-image"><img :src="getImageUrl(item.photoImage.name)"></div>-->
                            <!--</a>-->
                            <!--<ul class="actions user-actions">-->
                                <!--<li class="edit-btn" v-if="params.user_role=='administrator'">-->
                                <!--<span class="edit_entity" title="Edit User">-->
                                    <!--<a v-bind:href="route('user.edit.admin', {'id': item.id})" target="_blank">-->
                                        <!--<span><i class="fa fa-edit"></i></span>-->
                                    <!--</a>-->
                                <!--</span>-->
                                <!--</li>-->
                            <!--</ul>-->
                        <!--</figure>-->
                    <!--</div>-->
                    <div class="media-body">
                        <figure class="figure-block">
                            <a :href="route('user.view.frontend', {'slug': item.slug})">
                                <div :class="['figure-image', item.is_agency ? 'contain-image' : '']" :style="{ backgroundImage: `url('${item.img_logo}')` }"></div>
                            </a>
                            <ul class="actions user-actions">
                                <li class="edit-btn" v-if="params.user_role=='administrator'">
                                <span class="edit_entity" title="Edit User">
                                    <a v-bind:href="route('user.edit.admin', {'id': item.id})" target="_blank">
                                        <span><i class="fa fa-edit"></i></span>
                                    </a>
                                </span>
                                </li>
                            </ul>
                        </figure>
                        <div class="agency-body">
                        <!--<div class="agency-body-left">-->
                            <div class="agency-description">
                                <h3 v-if="item.is_agency">
                                    <a :href="route('user.view.frontend', {'slug': getCompanySlug(item)})">{{ getCompanyName(item) }}</a>
                                </h3>
                            <span v-else>
                                <h2 class="agent-title">
                                    <a :href="route('user.view.frontend', { 'slug': item.slug })">{{ item.first_name }} {{ item.last_name }}</a>
                                </h2>
                                <p class="position" v-if="item.agency && item.agency.id">
                                    <span v-if="item.position">{{ item.position }}</span>
                                    <span v-if="item.agency"> {{ trans('at') }} </span>
                                    <!--<span v-if="item.company_name">{{ item.company_name }}</span>-->
                                    <span v-if="item.agency">{{ getCompanyName(item.agency) }}</span>
                                </p>
                            </span>
                                <h4 class="address">{{ item.address ? item.address : item.map_address}}</h4>
                                <p v-if="item.description" class="description" v-html="item.description"></p>
                            </div>
                        <!--</div>-->
                        <!--<div class="agency-body-right">-->
                            <ul class="agency-social social-top">
                                <li v-if="item.facebook">
                                    <a class="btn-facebook" :href="item.facebook" target="_blank"><i class="fa fa-facebook-square"></i></a>
                                </li>
                                <li v-if="item.twitter">
                                    <a class="btn-twitter" :href="item.twitter" target="_blank"><i class="fa fa-twitter-square"></i></a>
                                </li>
                                <li v-if="item.linkedin">
                                    <a class="btn-linkedin" :href="item.linkedin" target="_blank"><i class="fa fa-linkedin-square"></i></a>
                                </li>
                                <li v-if="item.instagram">
                                    <a class="btn-instagram" :href="item.instagram" target="_blank"><i class="fa fa-instagram"></i></a>
                                </li>
                                <li v-if="item.google_plus">
                                    <a class="btn-google-plus" :href="item.google_plus" target="_blank"><i class="fa fa-google-plus-square"></i></a>
                                </li>
                                <li v-if="item.youtube">
                                    <a class="btn-youtube" :href="item.youtube" target="_blank"><i class="fa fa-youtube-square"></i></a>
                                </li>
                                <li v-if="item.pinterest">
                                    <a class="btn-pinterest" :href="item.pinterest" target="_blank"><i class="fa fa-pinterest-square"></i></a>
                                </li>
                                <li v-if="item.vimeo">
                                    <a class="btn-vimeo" :href="item.vimeo" target="_blank"><i class="fa fa-vimeo-square"></i></a>
                                </li>
                            </ul>
                            <user-contacts-list :user="item" :className="'agency-contact'"></user-contacts-list>
                        <!--</div>-->
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
                isGrid: false
            };
        },
        props: ['params', 'entities', 'is_grid'],
        mounted: function() {
            var self = this;

            this.isGrid = this.is_grid ? this.is_grid : this.isGrid;

            $('#sort-users').on('change', function() {
                window.location = self.modifyUrl('order_by', $(this).val());
            });
        }
    }
</script>

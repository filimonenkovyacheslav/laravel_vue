<template>
    <div class="agent-listing">
        <div class="profile-detail-block" :data-id="item.id" v-for="item, index in entities">
            <div class="media">
                <div class="media-left">
                    <figure class="figure-block">
                        <!--<span class="label-featured label label-success">{{ trans('Featured') }}</span>-->
                        <a :href="route((params.entity_type || item.type) + '.view.frontend', { 'slug': item.slug })">
                            <div class="figure-image" :style="getBgImageStyle(item.photoImage.name)"></div>
                        </a>
                    </figure>
                    <a v-if="params.entity_type!='professional'" :href="route((params.entity_type || item.type) + '.view.frontend', { 'slug': item.slug })" class="btn btn-primary btn-block d-none d-sm-block">{{ trans('View My Properties') }}</a>
                </div>
                <div class="media-body">
                    <div class="profile-description">
                        <h2 class="agent-title">
                            <a :href="route((params.entity_type || item.type) + '.view.frontend', { 'slug': item.slug })">{{ item.first_name }} {{ item.last_name }}</a>
                        </h2>
                        <p class="position">
                            <span>{{ item.position }}</span>
                            <span v-if="item.company_name"> {{ trans('at') }} </span>
                            <span>{{ item.company_name }}</span>
                        </p>
                        <div v-if="item.description" class="description" v-html="item.description"></div>
                        <user-contacts-list :user="item" :className="'agency-contact'"></user-contacts-list>
                        <ul class="profile-social">
                            <li v-if="item.phone"><a :href="item.phone.substr(-1) == '*' ? route('login') : 'tel:' + item.phone"><i class="fa fa-phone-square"></i></a></li>
                            <li v-if="item.facebook"><a class="btn-facebook" :href="item.facebook" target="_blank"><i class="fa fa-facebook-square"></i></a></li>
                            <li v-if="item.twitter"><a class="btn-twitter" :href="item.twitter" target="_blank"><i class="fa fa-twitter-square"></i></a></li>
                            <li v-if="item.instagram"><a class="btn-instagram" :href="item.instagram" target="_blank"><i class="fa fa-instagram"></i></a></li>
                        </ul>
                        <a v-if="params.entity_type!='professional'" :href="route((params.entity_type || item.type) + '.view.frontend', { 'slug': item.slug })" class="btn btn-primary btn-block d-block d-sm-none">{{ trans('View My Properties') }}</a>
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
        props: ['params', 'entities']
    }
</script>
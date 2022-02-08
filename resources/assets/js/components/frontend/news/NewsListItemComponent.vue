<template>
    <div class="row">
        <template v-for="prop, propIndex in entities">
            <div class="item-wrap infobox_trigger" :data-property-id="prop.id">
                <div class="property-item art-item table-list news-list">
                    <!-- <div class="table-cell">
                        <div class="figure-block">
                            <figure class="item-thumb">
                                <span class="label-featured label label-success" v-if="prop.featured_view">{{ trans('Featured') }}</span>
                                <news-view-frontend-labels :entityData="prop" :className="'label-right hide-on-list'"></news-view-frontend-labels>
                                <a class="hover-effect" :href="route('news.view.frontend', {'slug': prop.slug})">
                                    <div class="figure-image" :style="getBgImageStyle(getFeaturedImageName(prop.uploadsList))"></div>
                                </a>
                                <news-view-frontend-actions :entityData="prop" :userRole="params.user_role" :items="['favorite-btn', 'camera-btn', 'compare-btn']" ></news-view-frontend-actions>
                            </figure>
                        </div>
                    </div> -->
                    <div class="item-body table-cell">
                        <!-- <div class="body-left table-cell">
                            <div class="info-row">
                                <div class="label-wrap hide-on-grid"></div>
                                <h2 class="property-title">
                                    <a>{{ prop.title }}</a>
                                </h2>                               
                            </div>
                        </div>  -->                       
                        <section class="section-detail-content">
                            <div class="container">
                                <div class="row">
                                    <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12 container-contentbar">
                                        <div class="detail-bar">
                                            <news-view-frontend-description :params="params.entity_arr[propIndex]"></news-view-frontend-description>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
    /**
     * Get data via props in blade template <vue_template :props></vue_template>
     */
    export default {
        props: ['params', 'entities'],
    }
</script>

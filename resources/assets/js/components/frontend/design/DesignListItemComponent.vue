<template>
    <div class="row">
        <template v-for="prop, propIndex in entities">
            <div class="item-wrap infobox_trigger" :data-property-id="prop.id">
                <div class="property-item art-item table-list">
                    <div class="table-cell">
                        <div class="figure-block">
                            <figure class="item-thumb">
                                <span class="label-featured label label-success" v-if="prop.featured_view">{{ trans('Featured') }}</span>
                                <design-view-frontend-labels :entityData="prop" :className="'label-right hide-on-list'"></design-view-frontend-labels>
                                <a class="hover-effect" :href="route('design.view.frontend', {'slug': prop.slug})">
                                    <div class="figure-image" :style="getBgImageStyle(getFeaturedImageName(prop.uploadsList))"></div>
                                </a>
                                <design-view-frontend-actions :entityData="prop" :userRole="params.user_role" :items="['favorite-btn', 'camera-btn', 'compare-btn']" ></design-view-frontend-actions>
                            </figure>
                        </div>
                    </div>
                    <div class="item-body table-cell">
                        <div class="body-left table-cell">
                            <div class="info-row">
                                <div class="label-wrap hide-on-grid"></div>
                                <h2 class="property-title">
                                    <a :href="route('design.view.frontend', {'slug': prop.slug})">{{ prop.title }}</a>
                                </h2>
                                <div class="item-categories">
                                    <span class="item-category" v-for="category, index in prop.categories" v-if="params.design_categories[category] && params.design_categories_front[category]">{{ params.design_categories_front[category] }}</span>
                                </div>
                                <address class="property-address" v-if="prop.city">{{ prop.city }}<span v-if="prop.country && params.countries">, {{ params.countries[prop.country] }}</span></address>
                                <address class="property-address" v-else-if="prop.country && params.countries">{{ params.countries[prop.country] }}</address>
                                <address class="property-address" v-else>{{ prop.address ? prop.address : prop.map_address }}</address>
                            </div>
                            <div class="info-row date hide-on-grid" v-if="prop.user">
                                <p class="prop-user-agent">
                                    <i class="fa fa-user"></i>
                                    <a :href="route(prop.user.type+'.view.frontend', {'slug': getCompanySlug(prop.user)})">{{ getCompanyName(prop.user) }}</a>
                                </p>
                            </div>
                        </div>
                        <div class="body-right table-cell hidden-gird-cell">
                            <design-view-frontend-price :entityData="prop" :className="'info-row'"></design-view-frontend-price>
                            <div class="info-row phone text-right">
                                <a :href="route('design.view.frontend', {'slug': prop.slug})" class="btn btn-primary">Details <i class="fa fa-angle-right fa-right"></i></a>
                            </div>
                        </div>
                        <div class="item-foot date hide-on-list" v-if="prop.user">
                            <div class="item-foot-left">
                                <p class="prop-user-agent">
                                    <!--<i class="fa fa-user"></i>-->
                                    <a :href="route(prop.user.type+'.view.frontend', {'slug': getCompanySlug(prop.user)})">{{ getCompanyName(prop.user) }}</a>
                                </p>
                            </div>
                        </div>
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

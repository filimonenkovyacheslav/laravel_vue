<template>
    <div id="section-body" class="">
        <div class="container">
            <page-breadcrumbs :title="trans('All Wines') + setSearchLocation(trans('in'))"></page-breadcrumbs>
            <page-header :title="trans('All Wines') + setSearchLocation(trans('in'))" :entities="params.entities" :autorized="params.user_role" :switchView="true" defGrid="btn-grid"></page-header>
            <div class="row">
                <div class="col-xs-12 col-sm-12">
                    <template v-if="params.entities.current_page == 1">
                        <ads :params="params"></ads>
                    </template>
                    <div class="sort-tab table-cell" style="margin-top: -20px;">
                        <p v-if="params.entities.total">Total: {{ params.entities.total }} item(s)</p>
                    </div>
                    <div class="list-tabs table-list full-width">
                        <div class="tabs table-cell">
                            <ul>
                                <li><a class="active">{{ trans('All') }}</a></li>
                            </ul>
                        </div>
                        <div class="sort-tab table-cell text-right">
                            <span>{{ trans('Sort by') }}:</span>
                            <sort-order-selectbox :route_name="params.route_name"></sort-order-selectbox>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 container-sidebar">
                    <aside id="sidebar" class="sidebar-list-white">
                        <wine-filters-widget :params="params"></wine-filters-widget>
                    </aside>
                    <div class="sideAdsContent"></div>
                </div>
                <div class="col-lg-9 col-md-8 col-sm-6 col-xs-12 list-grid-area">
                    <div id="content-area">
                        <div class="property-listing grid-view">
                            <template v-if="params.entities && params.entities.data && params.entities.data.length">
                                <wine-list-frontend-list-item :entities="params.entities.data" :params="params" ></wine-list-frontend-list-item>
                                <pagination :pagination="params.entities"></pagination>
                            </template>
                            <template v-else>
                                <div style="padding: 20px 0;">{{ trans('No data for now') }}</div>
                            </template>
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
        props: ['params'],
        mounted: function() {
            this.entities_list = this.params.entities;
        }
    }
</script>

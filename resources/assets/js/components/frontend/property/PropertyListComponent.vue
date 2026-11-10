<template>
    <div id="section-body" class="">
        <div class="container">
            <div id="compare-controller" class="compare-panel">
                <div class="compare-panel-header">
                    <h4 class="title"> {{ trans('Compare Listings') }} <span class="panel-btn-close pull-right"><i class="fa fa-times"></i></span></h4>
                </div>
                <div id="compare-properties-basket"></div>
            </div>
            <page-breadcrumbs :title="trans('All Properties') + setSearchLocation(trans('in'))"></page-breadcrumbs>
            <page-header :title="trans('All Properties') + setSearchLocation(trans('in'))" :entities="params.entities" :autorized="params.user_role" :switchView="true"></page-header>
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
                                <li><a v-bind:href="modifyUrl('property_status', '')" :class="!inArray(urlGetParam('property_status'), [1,2]) ? 'active' : ''">{{ trans('All') }}</a></li>
                                <li><a v-bind:href="modifyUrl('property_status', 2)" :class="urlHasParam('property_status', 2) ? 'active' : ''">{{ trans('For Sale') }}</a></li>
                                <li><a v-bind:href="modifyUrl('property_status', 1)" :class="urlHasParam('property_status', 1) ? 'active' : ''">{{ trans('For Rent') }}</a></li>
                            </ul>
                        </div>
                        <div class="sort-tab table-cell text-right">
                            <span>{{ trans('Sort by') }}:</span>
                            <sort-order-selectbox></sort-order-selectbox>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 container-sidebar">
                    <aside id="sidebar" class="sidebar-list-white">
                        <property-filters-widget :params="params"></property-filters-widget>
                    </aside>
                    <div class="sideAdsContent"></div>
                </div>
                <div class="col-lg-9 col-md-8 col-sm-6 col-xs-12 list-grid-area">
                    <div id="content-area">
                        <div class="property-listing grid-view">
                            <template v-if="params.entities && params.entities.data && params.entities.data.length">
                                <property-list-frontend-list-item :entities="params.entities.data" :params="params" ></property-list-frontend-list-item>
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

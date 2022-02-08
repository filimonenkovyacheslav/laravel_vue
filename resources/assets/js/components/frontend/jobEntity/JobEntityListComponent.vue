<template>
<div id="section-body" class="">
<div class="container">
<div id="compare-controller" class="compare-panel">
    <div class="compare-panel-header">
        <h4 class="title"> {{ trans('Compare Listings') }} <span class="panel-btn-close pull-right"><i class="fa fa-times"></i></span></h4>
    </div>
    <div id="compare-jobs-basket"></div>
</div>
<page-breadcrumbs :title="trans('Jobs') + setSearchLocation(trans('in'))"></page-breadcrumbs>
<page-header :title="trans('Jobs') + setSearchLocation(trans('in'))" :entities="params.entities"  :autorized="params.user_role" :switchView="false"></page-header>
<div class="row">
<div class="col-lg-8 col-md-8 col-sm-12 col-xs-12 list-grid-area">
<div id="content-area">    
    <div class="list-tabs table-list full-width">
        <div class="tabs table-cell">

        </div>
        <div class="sort-tab table-cell text-right">
            <span>{{ trans('Sort by') }}:</span>
            <sort-order-selectbox :route_name="'jobEntity.list.frontend'"></sort-order-selectbox>
        </div>
    </div>
    <div class="jobEntity-listing grid-view grid-view-12-col">
        <template v-if="params.entities && params.entities.data && params.entities.data.length">
            <jobentity-list-frontend-list-item :entities="params.entities.data" :params="params" ></jobentity-list-frontend-list-item>
            <pagination :pagination="params.entities"></pagination>
            <!--<pagination :pagination="params.entities" @paginate="getNewPage()"></pagination>-->
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

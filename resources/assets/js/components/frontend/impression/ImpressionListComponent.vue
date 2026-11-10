<template>
<div id="section-body" class="">
<div class="container">
<page-breadcrumbs :title="trans('All Impressions')"></page-breadcrumbs>
<page-header :title="trans('All Impressions')" :entities="params.entities" :autorized="params.user_role" :switchView="true"></page-header>
<div class="row">
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 list-grid-area">
<div id="content-area">
    <div class="list-tabs table-list full-width">
        <!--<div class="tabs table-cell">
            <ul>
                <li><a v-bind:href="modifyUrl('impression_type', '')" :class="!inArray(urlGetParam('impression_type'), [1,2]) ? 'active' : ''">{{ trans('All') }}</a></li>
                <li><a v-bind:href="modifyUrl('impression_type', 1)" :class="urlHasParam('impression_type', 1) ? 'active' : ''">{{ trans('Images') }}</a></li>
                <li><a v-bind:href="modifyUrl('impression_type', 2)" :class="urlHasParam('impression_type', 2) ? 'active' : ''">{{ trans('Videos') }}</a></li>
            </ul>
        </div>-->
        <div class="sort-tab table-cell text-right">
            <span>Sort by:</span>
            <sort-order-selectbox :route_name="params.route_name"></sort-order-selectbox>
        </div>
    </div>
    <div class="property-listing grid-view grid-view-3-col">
        <template v-if="entities_list && entities_list.data && entities_list.data.length">
            <impression-list-frontend-list-item :entities="entities_list.data" :params="params" ></impression-list-frontend-list-item>
            <pagination :pagination="entities_list"></pagination>
            <!--<pagination :pagination="entities_list" @paginate="getNewPage()"></pagination>-->
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
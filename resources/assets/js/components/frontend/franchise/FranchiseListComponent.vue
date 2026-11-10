<template>
<div id="section-body" class="">
<div class="container">
<page-breadcrumbs :title="trans('All Franchise Opportunities') + setSearchLocation(trans('in'))"></page-breadcrumbs>
<page-header :title="trans('All Franchise Opportunities') + setSearchLocation(trans('in'))" :entities="params.entities" :autorized="params.user_role"></page-header>
<div class="row">
<div class="col-lg-8 col-md-8 col-sm-12 col-xs-12 list-grid-area container-contentbar">
<div id="content-area">
    <template v-if="entities_list && entities_list.data && entities_list.data.length">
        <div class="table-list" style="padding-bottom:20px">
            <div class="sort-tab table-cell">
                <p v-if="params.entities.total">Total: {{ params.entities.total }} item(s)</p>
            </div>
            <div class="sort-tab table-cell text-right">
                <span>{{ trans('Sort by') }}:</span>
                <select class="sort-select form-control" id="sort-users" :value="urlGetParam('order_by')" @>
                    <option value="">{{ trans('Default Order') }}</option>
                    <!--<option value="a_date">{{ trans('Date Old to New') }}</option>
                    <option value="d_date">{{ trans('Date New to Old') }}</option>-->
                    <option value="title">{{ trans('Name') }}</option>
                </select>
            </div>
         </div>
        <franchise-list-frontend-list-item :params="params" :entities="entities_list.data"></franchise-list-frontend-list-item>
        <pagination :pagination="entities_list" @paginate="getNewPage()"></pagination>
    </template>
    <template v-else>
        <div>{{ trans('No data for now') }}</div>
    </template>
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
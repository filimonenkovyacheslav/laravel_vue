<template>
<div id="section-body" class="" style="padding-top: 0px;">
<div class="container">
<div id="compare-controller" class="compare-panel">
    <div class="compare-panel-header">
        <h4 class="title"> {{ trans('Compare Listings') }} <span class="panel-btn-close pull-right"><i class="fa fa-times"></i></span></h4>
    </div>
    <div id="compare-properties-basket"></div>
</div>
<page-breadcrumbs :title="trans('All') + ' ' + params.entity_types[params.entity_type]"></page-breadcrumbs>
<page-header :title="trans('All') + ' ' + params.entity_types[params.entity_type]" :entities="params.entities" :autorized="params.user_role"></page-header>
<div class="row">
<div class="col-lg-8 col-md-8 col-sm-12 col-xs-12 list-grid-area container-contentbar">
<div id="content-area">
    <template v-if="params.entities.current_page == 1">
        <ads :params="params"></ads>
    </template>
    <template v-if="entities_list && entities_list.data && entities_list.data.length">
        <p v-if="params.entities.total">Total: {{ params.entities.total }} item(s)</p>
        <agent-list-frontend-list-item :params="params" :entities="entities_list.data"></agent-list-frontend-list-item>
        <pagination :pagination="entities_list" @paginate="getNewPage()"></pagination>
    </template>
    <template v-else>
        <div>{{ trans('No data for now') }}</div>
    </template>
</div>
</div>
<div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 col-md-offset-0 col-sm-offset-3 container-sidebar ">
    <aside id="sidebar" class="sidebar-white">
        <!--<professions-users-widget v-if="params.route_name == 'professional.list.frontend'" :professions="params.professions_users_count"></professions-users-widget>-->
    </aside>
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

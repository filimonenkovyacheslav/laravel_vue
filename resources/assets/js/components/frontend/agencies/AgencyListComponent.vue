<template>
<div id="section-body">
<div class="container">
<div id="compare-controller" class="compare-panel">
    <div class="compare-panel-header">
        <h4 class="title"> {{ trans('Compare Listings') }} <span class="panel-btn-close pull-right"><i class="fa fa-times"></i></span></h4>
    </div>
    <div id="compare-properties-basket"></div>
</div>
<page-breadcrumbs :title="trans('All') + ' ' + params.entity_types[params.entity_type] + (params.route_name == 'professional.list.frontend' ? setSearchProfessions() : '') + setSearchLocation(trans('in'))"></page-breadcrumbs>
<page-header :title="trans('All') + ' ' + params.entity_types[params.entity_type] + (params.route_name == 'professional.list.frontend' ? setSearchProfessions() : '') + setSearchLocation(trans('in'))" :entities="params.entities" :autorized="params.user_role"></page-header>
<div class="row">
<div :class="[params.route_name == 'professional.list.frontend' ? 'col-lg-8 col-md-8' : 'col-lg-12 offset-lg-0 col-md-8 offset-md-2', 'col-sm-12', 'col-xs-12', 'list-grid-area', 'container-contentbar']">
<div id="ad-user-area" v-if="params.ad_user.image && params.ad_user.image.name">
    <div class="row justify-content-center">
        <div class='ad-media-container'>
            <a :href="params.ad_user.url" target="_blank">
                <div class="figure-image"><img :src="'/uploads/'+params.ad_user.image.name"></div>
            </a>
        </div>
    </div>
</div>
<div id="content-area">
    <template v-if="params.entities.current_page == 1">
        <ads :params="params"></ads>
    </template>
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
                    <option value="name">{{ trans('Name') }}</option>
                </select>
            </div>
         </div>
        <agency-list-frontend-list-item :params="params" :entities="entities_list.data"></agency-list-frontend-list-item>
        <pagination :pagination="entities_list" @paginate="getNewPage()"></pagination>
    </template>
    <template v-else>
        <div>{{ trans('No data for now') }}</div>
    </template>
</div>
</div>
<div v-if="params.route_name == 'professional.list.frontend'" class="col-lg-4 col-md-4 col-sm-6 col-xs-12 col-md-offset-0 col-sm-offset-3 container-sidebar ">
    <aside id="sidebar" class="sidebar-white">
        <professions-users-widget :professions="params.professions_users_count"></professions-users-widget>
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
        },
        methods: {
            setSearchProfessions: function() {
                if(typeof(this.params.profession_name) != 'undefined') {
                    return this.params.profession_name;
                }
                var profession = this.urlGetParam('search_profession');
                if (profession == null) return '';

                var values = profession.split(','),
                    professions = this.params.professions,
                    res = '',
                    first = true;
                for(var i in professions) {
                    if(this.inArray(i, values)) {
                        res += (first ? ' - ' : ', ') + professions[i];
                        first = false;
                    }
                }
                return res;
            }
        }
    }
</script>

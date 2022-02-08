<template>
    <div class="user-dashboard-right dashboard-with-panel">
        <div class="board-header board-header-4">
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <div class="board-header-left">
                        <h3 class="board-title">{{ trans('Ads list') }}</h3>
                    </div>
                    <div class="board-header-right">
                        <ol class="breadcrumb">
                            <li itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
                                <a itemprop="url" v-bind:href="route('home')">
                                    <span itemprop="title">{{ trans('Home') }}</span>
                                </a>
                            </li>
                            <li class="active">{{ params.user.page_name }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="dashboard-content-area dashboard-fix">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div align="right">
                        <a :href="route('ads.edit.admin')" class="btn btn-primary">{{ trans('Add Ads') }}</a>
                    </div>
                    <div class="my-profile-search mt-3">
                        <form action="" method="get" autocomplete="off" id="search-entity-form">
                            <div class="profile-top-left">
                                <div class="row no-margin">
                                    <div class="form-group no-margin" style="width:200px; margin-right:15px !important;">
                                        <input class="form-control" name="keyword" type="text" :value="params.filter.keyword" :placeholder="trans('Enter keyword')">
                                        <input class="form-control mt-2" name="title" type="text" :value="params.filter.title" :placeholder="trans('Enter title')">
                                    </div>
                                    <div class="form-group no-margin" style="width:200px;">
                                        <select class="form-control" style="max-width:250px;" name="order_by" id=filterOrder>
                                            <option value="0">{{ trans('Set order') }}</option>
                                            <option v-for="item, index in params.order_by" :value="item.id">{{ item.label }}</option>
                                        </select>
                                        <div class="form-group no-margin" style="width:100px">
                                            <button type="submit" class="btn btn-secondary btn-block mt-2">{{ trans('Search') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="profile-top-right" v-if="inArray(params.user_role, ['administrator'])">
                                <select class="sort-select form-control" id="bulkActions">
                                    <option selected value="">{{ trans('Bulk Actions') }}</option>
                                    <option value="publish">{{ trans('Publish') }}</option>
                                    <option value="unpublish">{{ trans('Unpublish') }}</option>
                                    <option value="delete">{{ trans('Delete permanently') }}</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="sort-tab table-cell">
                        <p v-if="params.user.ads" style="margin-bottom: -20px;">{{ params.user.ads.total }} {{ trans('ads found') }}</p>
                    </div>
                    <table class="drt-entities-table">
                        <thead class="drt-entities-table-thead">
                        <tr>
                            <th v-if="inArray(params.user_role, ['administrator'])"><input type="checkbox" v-on:change="toggleBulkEditEntity"></th>
                            <th v-if="inArray(params.user_role, ['administrator'])">{{ trans('ID') }}</th>
                            <th v-if="inArray(params.user_role, ['administrator'])">{{ trans('Title') }}</th>
                            <th v-if="inArray(params.user_role, ['administrator'])">{{ trans('URL') }}</th>
                            <th v-if="inArray(params.user_role, ['administrator'])">{{ trans('Keywords') }}</th>
                            <th v-if="inArray(params.user_role, ['administrator'])">{{ trans('Order') }}</th>
                            <th v-if="inArray(params.user_role, ['administrator'])">{{ trans('Status') }}</th>
                            <th v-if="inArray(params.user_role, ['administrator'])">{{ trans('Actions') }}</th>
                        </tr>
                        </thead>
                        <tbody class="drt-entities-table-tbody">
                        <tr class="drt-post-entity-row" v-for="prop, index in entities_list.data">
                            <td v-if="inArray(params.user_role, ['administrator'])"><input type="checkbox" :value="prop.ads_id" class="bulkEditEntity"/></td>
                            <td class="drt-post-entity-row-title">{{ prop.ads_id }}</td>
                            <td class="drt-post-entity-row-title">{{ prop.title }}</td>
                            <td class="drt-post-entity-row-title">{{ prop.url }}</td>
                            <td class="drt-post-entity-row-title">{{ prop.keywords }}</td>
                            <td class="drt-post-entity-row-title">{{ prop.order }}</td>
                            <td class="drt-post-entity-row-title">
                                <div v-if="item.id == prop.status " v-for="item, index in params.status" value="item">
                                    {{ item.label  }}
                                </div>
                            </td>
                            <td>
                                <div v-if="inArray(params.user_role, ['administrator'])" class="my-actions">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ trans('Actions') }} <i class="fa fa-angle-down"></i></button>
                                        <ul class="dropdown-menu actions-dropdown">
                                            <li><a v-bind:href="route('ads.edit.admin', {'id': prop.ads_id})"><i class="fa fa-edit"></i> {{ trans('Edit') }}</a></li>
                                            <li v-if="inArray(prop.status, [2, 3, 4, 5, 6]) && inArray(params.user_role, ['administrator'])">
                                                <a v-bind:href="route('ads.status.admin', {'id': prop.ads_id, 'status': 1})">
                                                    <i class="fa fa-check"></i> {{ trans('Approve') }}
                                                </a>
                                            </li>
                                            <li v-if="prop.status!='6' && prop.status!='5' && inArray(params.user_role, ['administrator'])">
                                                <a v-bind:href="route('ads.unpublish.admin', {'id': prop.ads_id})" class="unpublish-property">
                                                    <i class="fa fa-minus"></i> {{ trans('Unpublish') }}
                                                </a>
                                            </li>
                                            <li v-if="prop.status!='5' || (prop.status=='5' && inArray(params.user_role, ['administrator']))">
                                                <a v-bind:href="route('ads.delete.admin', {'id': prop.ads_id})" class="delete-property">
                                                    <i class="fa fa-trash"></i> {{ trans('Delete') }}
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <pagination :pagination="entities_list"></pagination>
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
            var userRole = this.params.user_role;
            jQuery('input[name="user_id"]').val(this.urlGetParam('user_id'));
            jQuery('input[name="keyword"]').val(this.urlGetParam('keyword'));
            jQuery('input[name="title"]').val(this.urlGetParam('title'));
            jQuery('input[name="post_id"]').val(this.urlGetParam('post_id'));
            if(userRole == 'administrator') {
                this.getUserAutocomplete(jQuery('input[name="user_id"]'), {
                    'role': userRole == 'administrator' ? 'all' : 'all',
                    onSelect: function(item, event) {
                        //setTimeout("jQuery('body'.find('#user_id').val(item.id);", 1000);
                    }
                });
            }
            $(function() {
                /* For zebra striping */
                $("table tr:nth-child(odd)").addClass("odd-row");
                /* For cell text alignment */
                $("table td:first-child, table th:first-child").addClass("first");
                /* For removing the last border */
                $("table td:last-child, table th:last-child").addClass("last");
            });
            this.entities_list = this.params.user.ads;
            var searchForm = $('form[id="search-entity-form"]');
            
            searchForm.on('submit', function() {
                return true;
            });
            searchForm.find('select[name="order_by"]').on('change', function() {
                searchForm.submit();
            });
            if(typeof(this.params.filter.btype) != 'undefined') {
                var list = this.params.filter.btype,
                    elem = jQuery('#filterType');
                if(!jQuery.isArray(list)) list = [list];
                elem.find('option').prop('selected', false);
                jQuery.each(list, function(i,e){
                    elem.find('option[value="' + e + '"]').prop('selected', true);
                });
            }
            if(typeof(this.params.filter.order_by) != 'undefined') {
                var list = this.params.filter.order_by,
                    elem = jQuery('#filterOrder');
                if(!jQuery.isArray(list)) list = [list];
                elem.find('option').prop('selected', false);
                jQuery.each(list, function(i,e){
                    elem.find('option[value="' + e + '"]').prop('selected', true);
                });
            }
            jQuery('#bulkActions').on('change', function() {
                var action = jQuery(this).val();
                if(action == '') return;
                
                var editItems = new Array();
                jQuery('.bulkEditEntity').each(function(){
                    if (jQuery(this).is(':checked')) {
                        editItems.push(jQuery(this).val());
                    }
                });
                if(editItems.length == 0) {
                    alert('Select entities.');
                    return;
                }
                var formData = new FormData();
                formData.append("editItems", editItems);
                axios.post('/bulk-'+action+'-ads', formData).then(function(response) {
                    window.location.reload();
                }).catch(function(error) {
                    console.log(error);
                });
            });
        },
        updated: function() {
            jQuery('.merge-post').on('click', function() {
                var container = jQuery(this).parent(),
                    into = container.find('input').val();
                if(into == '') {
                    alert('Enter ID to merge into');
                    return;
                }
                
                var id = container.closest('tr').find('.bulkEditEntity').val(),
                    formData = new FormData();
                
                formData.append("into", into);
                formData.append("id", id);
                axios.post('/merge-post', formData).then(function(response) {
                    window.location.reload();
                }).catch(function(error) {
                    console.log(error);
                });
                return false;
            });
        },
        methods: {
            getItemIndex: function(index) {
                var curPage = this.params.entities.current_page;
                var index = (index + 1) * curPage;
                return index;
            },
        },
    }
</script>

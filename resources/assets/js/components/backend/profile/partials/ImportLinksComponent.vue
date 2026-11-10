<template>
    <div class="user-dashboard-right dashboard-with-panel">
        <div class="board-header board-header-4">
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <div class="board-header-left">
                        <h3 class="board-title">{{ trans('Import Links') }}</h3>
                    </div>
                    <div class="board-header-right">
                        <ol class="breadcrumb">
                            <li itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
                                <a itemprop="url" v-bind:href:href="route('home')">
                                    <span itemprop="title">{{ trans('Home') }}</span>
                                </a>
                            </li>
                            <li class="active">{{ trans('Import Links') }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="dashboard-content-area dashboard-fix">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="my-profile-search">
                        <form method="get" id="filter-import-links" action="">
                            <div class="profile-top-left">
                                <div class="row no-margin">
                                    <div class="form-group no-margin row-form" style="width:300px" v-if="params.user_role=='administrator'">
                                        <input type="text" id="author-name" :value="params.filter.author && params.filter.author.id ? getCompanyName(params.filter.author) + ' (ID '+params.filter.author.id+')' : ''"
                                        class="form-control" :placeholder="trans('Enter author name or ID')" />
                                        <input type="hidden" name="author_id" :value="params.filter.author_id ? params.filter.author_id : 0"/>
                                    </div>
                                    <div class="form-group no-margin row-form" style="width:150px">
                                        <select class="sort-select form-control" name="status" id="filter-status" :value="params.filter.status">
                                            <option value="">{{ trans('All Statuses') }}</option>
                                            <option v-for="name, status in params.import_statuses" :value="status"> {{ name }} </option>
                                        </select>
                                    </div>
                                    <div class="form-group no-margin row-form" style="width:100px">
                                        <button type="submit" class="btn btn-primary btn-block">{{ trans('Search') }}</button>
                                    </div>
                                </div>
                            </div>
                            <div class="profile-top-right">
                                <div class="sort-tab text-right">
                                    <select class="sort-select form-control" name="order_by" :value="params.filter.order_by">
                                        <option value="">{{ trans('Default Order') }}</option>
                                        <option value="a_date">{{ trans('Date Old to New') }}</option>
                                        <option value="d_date">{{ trans('Date New to Old') }}</option>
                                       <option value="link">{{ trans('Link') }}</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div>
                        <hr>
                        <div class="my-profile-search">
                            <div class="profile-top-left">
                                <form method="post" id="add-import-link" action="/add-import-link">
                                    <div class="row no-margin">
                                        <div class="form-group no-margin row-form" style="width:450px">
                                            <input type="text" name="link" id="new_link" class="form-control" :placeholder="trans('Enter new link')" />
                                        </div>
                                        <div class="form-group no-margin row-form" style="width:100px">
                                            <button type="submit" class="btn btn-secondary">{{ trans('Add Link') }}</button>
                                        </div>
                                    </div>
                                    <input type="hidden" name="_token" v-model="csrf">
                                </form>
                            </div>
                            <div class="profile-top-right" v-if="params.user_role=='administrator'">
                                <div class="sort-tab text-right">
                                    <a v-bind:href="route('run.import.admin')" class="btn btn-secondary" ><i class="fa fa-play"></i> {{ trans('Run imports') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="my-property-listing">
                        <div class="row grid-row" v-for="item, index in entities_list.data">
                            <div class="item-wrap one-user-row">
                                <div class="media my-property">
                                    <div class="media-body align-self-center">
                                        <div class="my-description">
                                            <h4>
                                                <strong><a class="blue" v-bind:href="route('user.import.runs', {'id': item.id})">{{ item.link }}</a></strong>
                                            </h4>
                                            <div class="status">
                                                <p v-if="params.user_role=='administrator'">
                                                    <span><strong>{{ trans('Author') }}:</strong>
                                                        <label>
                                                            <a :href="route(item.user.type + '.view.frontend', {'slug': getCompanySlug(item.user)})">
                                                                {{ getCompanyName(item.user) }}
                                                            </a>
                                                        </label> 
                                                    </span>
                                                    <span></span>
                                                </p>
                                                <p>
                                                    <span><strong>{{ trans('Status') }}:</strong>
                                                        <span>{{ item.status_label }}</span>
                                                    </span>
                                                    <span><strong>{{ trans('Last import') }}:</strong> 
                                                        <label v-if="item.run_status!=null">
                                                            <span v-if="item.ended"> {{ item.ended }} </span>
                                                            <span v-else> {{ trans('Now') }} </span>
                                                        </label>
                                                        <label v-else> - </label>
                                                    </span>
                                                    <span title="processed / errors"><strong>{{ trans('Results') }}:</strong>
                                                        <label v-if="item.cnt_props" style="color:green"> {{ item.cnt_props }} </label>
                                                        <label v-else> - </label>
                                                        <label>/</label>
                                                        <label v-if="item.cnt_errors" style="color:red"> {{ item.cnt_errors }} </label>
                                                        <label v-else> - </label>
                                                    </span>
                                                    <span></span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="my-actions">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ trans('Actions') }} <i class="fa fa-angle-down"></i></button>
                                                <ul class="dropdown-menu actions-dropdown">
                                                    <li v-if="item.run_id">
                                                        <a target="_blank" v-bind:href="route('user.import.log')+'?id='+item.run_id"><i class="fa fa-edit"></i> {{ trans('View Log') }}
                                                        </a>
                                                    </li>
                                                    <li v-if="item.status!='1' && inArray(params.user_role, ['administrator'])">
                                                        <a v-bind:href="route('user.import.status', {'id': item.id, 'status': 1})"><i class="fa fa-check"></i> {{ trans('Approve') }}</a>
                                                    </li>
                                                    <li v-if="item.status!='2' && inArray(params.user_role, ['administrator'])">
                                                        <a v-bind:href="route('user.import.status', {'id': item.id, 'status': 2})"><i class="fa fa-close"></i> {{ trans('Reject') }}</a>
                                                    </li>
                                                    <li v-if="item.status=='1' && inArray(params.user_role, ['administrator']) && (!item.run_date || item.ended)">
                                                        <a v-bind:href="route('run.import.admin', {'id': item.id})"><i class="fa fa-play"></i> {{ trans('Run') }}</a>
                                                    </li>
                                                    <li v-if="item.status!='3' && !inArray(params.user_role, ['administrator'])">
                                                        <a v-bind:href="route('user.import.status', {'id': item.id, 'status': 3})" class="delete-property">
                                                            <i class="fa fa-trash"></i> {{ trans('Delete') }}
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                    </div>
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
            this.entities_list = this.params.import_links;

            var self = this,
                searchForm = $('#filter-import-links'),
                authorId = searchForm.find('input[name="author_id"]'),
                authorName = searchForm.find('#author-name');
                
            if(this.params.user_role=='administrator') {
                this.getUserAutocomplete(authorName, {
                    role: 'all', 
                    onSelect: function(item, event) {
                        authorId.val(item.id);
                    }
                });
            }
            searchForm.find('select[name="order_by"]').on('change', function() {
                searchForm.submit();
            });
            searchForm.on('submit', function() {
                if(authorName.val().length == 0) {
                    authorId.val(0);
                }
            });
        },
    }
</script>
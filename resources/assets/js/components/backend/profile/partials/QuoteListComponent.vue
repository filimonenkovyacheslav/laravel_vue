<template>
    <div class="user-dashboard-right dashboard-with-panel">
        <div class="board-header board-header-4">
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <div class="board-header-left">
                        <h3 class="board-title">{{ trans('Recommendations List') }}</h3>
                    </div>
                    <div class="board-header-right">
                        <ol class="breadcrumb">
                            <li itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
                                <a itemprop="url" v-bind:href:href="route('home')">
                                    <span itemprop="title">{{ trans('Home') }}</span>
                                </a>
                            </li>
                            <li class="active">{{ trans('Recommendations List') }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="dashboard-content-area dashboard-fix">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="my-profile-search">
                        <form method="get" id="filter-quotes" action="">
                            <div class="profile-top-left">
                                <div class="row no-margin">
                                    <div class="form-group no-margin row-form" style="width:450px">
                                        <div class="single-input-search">
                                            <input class="form-control" name="phrase" :placeholder="trans('Search phrase')" type="text" :value="params.filter.phrase">
                                            <button type="submit"></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="profile-top-right">
                                <div class="sort-tab text-right">
                                    <select class="sort-select form-control" name="order_by" :value="params.filter.order_by">
                                        <option value="">{{ trans('Default Order') }}</option>
                                        <option value="a_date">{{ trans('Date Old to New') }}</option>
                                        <option value="d_date">{{ trans('Date New to Old') }}</option>
                                       <option value="phrase">{{ trans('Phrase') }}</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div>
                        <hr>
                        <div class="my-profile-search">
                            <div class="profile-top-left">
                                <form method="post" id="add-quote" action="/add-quote">
                                    <div class="row no-margin">
                                        <div class="form-group no-margin row-form" style="width:450px">
                                            <input type="text" name="phrase" id="new_link" class="form-control" :placeholder="trans('Enter new quote')" />
                                        </div>
                                        <div class="form-group no-margin row-form" style="width:100px">
                                            <button type="submit" class="btn btn-secondary">{{ trans('Add Quote') }}</button>
                                        </div>
                                    </div>
                                    <input type="hidden" name="_token" v-model="csrf">
                                </form>
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
                                                <strong>{{ item.phrase }}</strong>
                                            </h4>
                                        </div>
                                        <div class="my-actions">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ trans('Actions') }} <i class="fa fa-angle-down"></i></button>
                                                <ul class="dropdown-menu actions-dropdown">
                                                    <li>
                                                        <a v-bind:href="route('delete.quote.admin', {'id': item.id})" class="delete-property">
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
            this.entities_list = this.params.quotes;

            var self = this,
                searchForm = $('#filter-quotes');
                
            searchForm.find('select[name="order_by"]').on('change', function() {
                searchForm.submit();
            });
        },
    }
</script>
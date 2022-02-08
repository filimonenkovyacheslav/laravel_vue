<template>
    <div class="user-dashboard-right dashboard-with-panel">
        <div class="board-header board-header-4">
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <div class="board-header-left">
                        <h3 class="board-title">{{ trans('Professional Categories') }}</h3>
                    </div>
                    <div class="board-header-right">
                        <ol class="breadcrumb">
                            <li itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
                                <a itemprop="url" v-bind:href:href="route('home')">
                                    <span itemprop="title">{{ trans('Home') }}</span>
                                </a>
                            </li>
                            <li class="active">{{ trans('Professional Categories') }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="dashboard-content-area dashboard-fix">
            <div class="row" v-if="params.user_role=='administrator'">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-2" v-for="counter, counterName in params.counters">
                            <div class="form-group">
                                <b>{{ counter.title }}:</b>
                                <span>(<b>{{ trans('total') }}:</b> {{ counter.count }})</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="my-profile-search">
                        <div class="profile-top-left">
                            <form method="get" action="">
                                <input type="hidden" name="prop_status" value="">
                                <div class="single-input-search">
                                    <input class="form-control" name="name" :placeholder="trans('Search name')" type="text" :value="params.filter.name">
                                    <button type="submit"></button>
                                </div>
                            </form>
                        </div>
                        <div class="profile-top-right">
                            <div class="sort-tab text-right">
                                <a v-bind:href="route('artCategory.edit.admin')" class="btn btn-primary">{{ trans('Add Category') }}</a>
                            </div>
                        </div>
                    </div>
                    <div class="my-property-listing">
                        <div class="row grid-row" v-for="item, index in entities_list.data">
                            <div class="item-wrap">
                                <div class="media my-property">
                                    <div class="media-body align-self-center">
                                        <div class="my-description">
                                            <h4>{{ item.name }}</h4>
                                        </div>
                                        <div class="my-actions">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ trans('Actions') }} <i class="fa fa-angle-down"></i></button>
                                                <ul class="dropdown-menu actions-dropdown">
                                                    <li><a v-bind:href="route('artCategory.edit.admin', {'id': item.art_category_id})"><i class="fa fa-edit"></i> {{ trans('Edit') }}</a></li>
                                                    <li><a v-bind:href="route('artCategory.delete.admin', {'id': item.art_category_id})" class="delete-jobCategory"><i class="fa fa-close"></i> {{ trans('Delete') }}</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <template v-if="item.children">
                                <user-profile-art-category-child :entity_item="item"></user-profile-art-category-child>
                            </template>
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
            this.entities_list = this.params.artCategories;
        }
    }
</script>

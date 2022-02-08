<template>
    <div class="user-dashboard-right dashboard-with-panel">
        <div class="board-header board-header-4">
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <div class="board-header-left">
                        <h3 class="board-title">{{ trans('My Professions') }}</h3>
                    </div>
                    <div class="board-header-right">
                        <ol class="breadcrumb">
                            <li itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
                                <a itemprop="url" v-bind:href:href="route('home')">
                                    <span itemprop="title">{{ trans('Home') }}</span>
                                </a>
                            </li>
                            <li class="active">{{ trans('My Professions') }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="dashboard-content-area dashboard-fix">
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
                                <a v-bind:href="route('profession.edit.admin')" class="btn btn-primary">{{ trans('Add Profession') }}</a>
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
                                                    <li><a v-bind:href="route('profession.edit.admin', {'id': item.profession_id})"><i class="fa fa-edit"></i> {{ trans('Edit') }}</a></li>
                                                    <li><a v-bind:href="route('profession.delete.admin', {'id': item.profession_id})" class="delete-profession"><i class="fa fa-close"></i> {{ trans('Delete') }}</a></li>
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
            this.entities_list = this.params.professions;
        }
    }
</script>

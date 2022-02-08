<template>
    <div class="user-dashboard-right dashboard-with-panel">
        <div class="board-header board-header-4">
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <div class="board-header-left">
                        <h3 class="board-title">{{ params.user.page_name }}</h3>
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
                    <div class="my-profile-search">
                        <div class="profile-top-left">
                            <form method="get" action="">
                                <div class="single-input-search">
                                    <input class="form-control" name="keyword" :placeholder="trans('Enter keyword...')" type="text" :value="params.filter.keyword">
                                    <button type="submit"></button>
                                </div>
                            </form>
                        </div>
                        <div class="profile-top-right">
                            <div class="sort-tab text-right">
                                <a v-bind:href="route('franchise.edit.admin')" target="_blank" style="float:right;margin-left:3px" class="btn btn-primary">{{ trans('Add Franchise') }}</a>
                                <select class="sort-select form-control" :value="urlGetParam('order_by')">
                                    <option value="">{{ trans('Default Order') }}</option>
                                    <option value="title">{{ trans('Name') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="my-property-listing">
                        <div class="row grid-row" v-for="prop, index in entities_list.data">
                            <div class="item-wrap">
                                <div class="media my-property">
                                    <div class="media-left">
                                        <div class="figure-block">
                                            <figure class="item-thumb">
                                                <a v-bind:href="route('franchise.view.frontend', {'slug': prop.slug})">
                                                    <div class="figure-image" v-bind:style="getBgImageStyle(prop.logo.name)"></div>
                                                </a>
                                            </figure>
                                        </div>
                                    </div>
                                    <div class="media-body align-self-center">
                                        <div class="my-description">
                                            <h4 class="my-heading">
                                                <a v-bind:href="route('franchise.view.frontend', {'slug': prop.slug})">{{ prop.title }}</a>
                                            </h4>
                                            <div class="status">
                                                <p v-if="inArray(params.user_role, ['administrator']) && prop.user" class="prop-user-agent">
                                                    <i class="fa fa-user"></i>
                                                    {{ getCompanyName(prop.user) }}
                                                </p>
                                                <p>
                                                    <br><span>{{ prop.status_view.label }}</span>
                                                    <span class="label-wrap">
                                                        <span v-if="prop.label" :class="'label-default label label-'+prop.label_view.color"> {{ prop.label_view.label }}</span>
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                        <div v-if="prop.author==params.user.id || inArray(params.user_role, ['administrator'])" class="my-actions">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ trans('Actions') }} <i class="fa fa-angle-down"></i></button>
                                                <ul class="dropdown-menu actions-dropdown">
                                                    <li><a v-bind:href="route('franchise.edit.admin', {'id': prop.id})"><i class="fa fa-edit"></i> {{ trans('Edit') }}</a></li>
                                                    <li v-if="inArray(prop.status, [2, 5]) && inArray(params.user_role, ['administrator'])">
                                                        <a v-bind:href="route('franchise.status.admin', {'id': prop.id, 'status': 1})">
                                                            <i class="fa fa-check"></i> {{ trans('Approve') }}
                                                        </a>
                                                    </li>
                                                    <li v-if="prop.status!='5'">
                                                        <a v-bind:href="route('franchise.delete.admin', {'id': prop.id})" class="delete-property">
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
                        <pagination :pagination="entities_list"></pagination>
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
            var self = this;
            this.entities_list = this.params.user.franchises;

            $('.sort-select').on('change', function() {
                window.location = self.modifyUrl('order_by', $(this).val());
            });
        }
    }
</script>
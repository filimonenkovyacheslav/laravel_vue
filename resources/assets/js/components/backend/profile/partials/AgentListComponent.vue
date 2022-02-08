<template>
    <div class="user-dashboard-right dashboard-with-panel">
        <div class="board-header board-header-4">
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <div class="board-header-left">
                        <h3 class="board-title">{{ params.agency_agents[params.user_role].title }} {{ trans('List') }}</span></h3>
                    </div>
                    <div class="board-header-right">
                        <ol class="breadcrumb">
                            <li itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
                                <a itemprop="url" v-bind:href:href="route('home')">
                                    <span itemprop="title">{{ trans('Home') }}</span>
                                </a>
                            </li>
                            <li class="active">{{ params.agency_agents[params.user_role].title }} {{ trans('List') }}</li>
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
                                    <input class="form-control" name="name" :placeholder="trans('Search name')" type="text" :value="params.filter.name">
                                    <button type="submit"></button>
                                </div>
                            </form>
                        </div>
                        <div class="profile-top-right">
                            <div class="sort-tab text-right">
                                <a v-bind:href="route('register') + '?role=' + params.agency_agents[params.user_role].role" target="_blank" class="btn btn-primary">{{ trans('Add New') }}</a>
                            </div>
                        </div>
                    </div>
                    <div class="my-property-listing">
                        <div class="row grid-row" v-for="item, index in entities_list.data">
                            <div class="item-wrap">
                                <div class="media my-property">
                                    <div class="media-body align-self-center">
                                        <div class="my-description">
                                            <h4 class="my-heading">
                                                <a v-bind:href="route(params.agency_agents[params.user_role].role + '.view.frontend', {'slug': item.slug})">
                                                    {{ item.first_name }} {{ item.last_name }} ({{ item.name }})
                                                </a>
                                            </h4>
                                            <div class="status">
                                                <p>
                                                    <span><strong>Status:</strong> <span v-for="status, index in params.agent_statuses" v-if="index==item.agent_status">{{ status.label }}</span></span>
                                                    <span></span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="my-actions">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ trans('Actions') }} <i class="fa fa-angle-down"></i></button>
                                                <ul class="dropdown-menu actions-dropdown">
                                                    <li v-if="item.agent_status!='1'"><a v-bind:href="route('agent.status.admin', {'id': item.agent_id, 'status': 1})"><i class="fa fa-check"></i> {{ trans('Approve') }}</a></li>
                                                    <li v-if="item.agent_status!='2'"><a v-bind:href="route('agent.status.admin', {'id': item.agent_id, 'status': 2})"><i class="fa fa-close"></i> {{ trans('Reject') }}</a></li>
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
            this.entities_list = this.params.user.agents;
        }
    }
</script>
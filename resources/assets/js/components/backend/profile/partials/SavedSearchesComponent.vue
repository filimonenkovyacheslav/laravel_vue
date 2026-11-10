<template>
    <div class="user-dashboard-right dashboard-with-panel">
        <div class="board-header board-header-4">
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <div class="board-header-left">
                        <h3 class="board-title">{{ trans('Saved Searches') }}</h3>
                    </div>
                    <div class="board-header-right">
                        <ol class="breadcrumb">
                            <li itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
                                <a itemprop="url" v-bind:href="route('home')">
                                    <span itemprop="title">{{ trans('Home') }}</span>
                                </a>
                            </li>
                            <li class="active">{{ trans('Saved Searches') }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="dashboard-content-area dashboard-fix">
            <div class="row" v-if="params.user_role=='administrator'">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <span><b>{{ trans('Total') }}:</b> {{ params.counter }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="account-block">
                        <div class="saved-search-list">
                            <div class="saved-search-block" v-for="prop, index in params.user.saved_searches">
                                <p><strong>{{ prop.name }}</strong></p>
                                <p></p>
                                <a v-bind:href="route('saved_searches.delete.admin', {'id': prop.id})" class="btn btn-primary remove-search"><i class="fa fa-remove"></i></a>
                                <a v-bind:href="prop.path + prop.params" target="_blank" class="btn btn-primary">{{ trans('Search') }}</a>
                            </div>
                        </div>
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
        props: ['params']
    }
</script>
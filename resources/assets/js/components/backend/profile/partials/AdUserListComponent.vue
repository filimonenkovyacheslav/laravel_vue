<template>
    <div class="user-dashboard-right dashboard-with-panel">
        <div class="board-header board-header-4">
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <div class="board-header-left">
                        <h3 class="board-title">{{ trans('Users Ad') }}</h3>
                    </div>
                    <div class="board-header-right">
                        <ol class="breadcrumb">
                            <li itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
                                <a itemprop="url" v-bind:href:href="route('home')">
                                    <span itemprop="title">{{ trans('Home') }}</span>
                                </a>
                            </li>
                            <li class="active">{{ trans('Users Ad') }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="dashboard-content-area dashboard-fix">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="my-profile-search">
                        <div class="profile-top-left profile-filter-small">
                            <form method="get" action="">
                                <input type="hidden" name="role" :value="params.filter.role">
                                <div class="single-input-search">
                                    <input class="form-control" name="name" :placeholder="trans('Search name')" type="text" :value="params.filter.name">
                                    <button type="submit"></button>
                                </div>
                            </form>
                        </div>
                        <div class="profile-top-right text-right profile-filter-big">
                            <div class="sort-tab text-right">
                                <a v-bind:href="route('admin.edit.ad_user', {'id': 0})" style="float:right;margin-left:20px" class="btn btn-primary">{{ trans('Add Ad') }}</a>
                                <select class="sort-select form-control" id="filter_role" :value="params.filter.role">
                                    <option value="">{{ trans('All Roles') }}</option>
                                    <option v-for="title, name in params.ad_user_roles" :value="name"> {{ title }} </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="my-property-listing">
                        <div class="row grid-row" v-for="item, index in entities_list.data">
                            <div class="item-wrap">
                                <div class="media my-property">
                                    <div class="media-body align-self-center">
                                        <div class="my-description">
                                            <h4 class="my-heading">{{ item.name }}</h4>
                                            <div class="status">
                                                <p>
                                                    <span><strong>{{ trans('Role') }}:</strong> <span v-for="title, name in params.ad_user_roles" v-if="name==item.role_name">{{ title }}</span></span>
                                                    <span><strong>{{ trans('Address') }}:</strong> {{ item.map_address }} </span>
                                                    <span></span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="my-actions">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions <i class="fa fa-angle-down"></i></button>
                                                <ul class="dropdown-menu actions-dropdown">
                                                    <li><a v-bind:href="route('admin.edit.ad_user', {'id': item.id})"><i class="fa fa-edit"></i> {{ trans('Edit') }}</a></li>
                                                    <li><a v-bind:href="route('delete.ad_user.admin', {'id': item.id})"><i class="fa fa-remove"></i> {{ trans('Delete') }}</a></li>
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
            this.entities_list = this.params.ad_users;
            $('#filter_role').val(this.params.filter.role).on('change', function() {
                document.location.href = '?role=' + $(this).val() + '&name=' + $('.single-input-search input').val();
            });
        }
    }
</script>
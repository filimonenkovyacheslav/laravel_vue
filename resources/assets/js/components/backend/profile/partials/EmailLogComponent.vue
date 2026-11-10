<template>
    <div class="user-dashboard-right dashboard-with-panel">
        <div class="board-header board-header-4">
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <div class="board-header-left">
                        <h3 class="board-title">{{ trans('Email Log') }}</span></h3>
                    </div>
                    <div class="board-header-right">
                        <ol class="breadcrumb">
                            <li itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
                                <a itemprop="url" v-bind:href:href="route('home')">
                                    <span itemprop="title">{{ trans('Home') }}</span>
                                </a>
                            </li>
                            <li class="active">{{ trans('Email Log') }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="dashboard-content-area dashboard-fix">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="my-profile-search">
                        <form action="/admin/emails/log" method="get">
                            <div class="profile-top-left">
                                <div style="float:left;">
                                    <select class="form-control" name="filter_name" id="filter_name">
                                        <option value="">{{ trans('All Emails') }}</option>
                                        <option v-for="title, name in params.templates" :value="name"> {{ title }} </option>
                                    </select>
                                </div>
                                <div style="float:left;">
                                    <input type="date" name="filter_date" id="filter_date" :value="params.filter.filter_date" class="form-control">
                                </div>
                            </div>
                            <div class="profile-top-right">
                                <button class="btn btn-primary pull-right">{{ trans('Filter') }}</button>
                            </div>
                        </form>
                    </div>
                    <div class="my-property-listing">
                        <div class="row grid-row" v-for="item, index in entities_list.data">
                            <div class="item-wrap">
                                <div class="media my-property">
                                    <div class="media-body align-self-center">
                                        <div class="my-description">
                                            <div class="my-heading">
                                                <span v-for="title, name in params.templates" v-if="name==item.name"> {{ title }} </span>
                                                <strong>{{ item.created_at }}</strong>
                                            </div>
                                            <div class="col-md-12 col-sm-12 col-xs-12">
                                                <div class="row">
                                                    <div class="col-md-1 col-sm-2 col-xs-12 status">
                                                        <p><strong>{{ trans('From') }}:</strong></p>
                                                    </div>
                                                    <div class="col-md-11 col-sm-10 col-xs-12 status">
                                                        <p>{{ item.from }}</p>
                                                    </div>
                                                    <div class="col-md-1 col-sm-2 col-xs-12 status">
                                                       <p><strong>{{ trans('To') }}:</strong></p>
                                                    </div>
                                                    <div class="col-md-11 col-sm-10 col-xs-12 status">
                                                        <p>{{ item.to }}</p>
                                                    </div>
                                                    <div class="col-md-1 col-sm-2 col-xs-12 status">
                                                        <p><strong>{{ trans('Subject') }}:</strong></p>
                                                    </div>
                                                    <div class="col-md-11 col-sm-10 col-xs-12 status">
                                                        <p>{{ item.subject }}</p>
                                                    </div>
                                                    <div class="col-md-1 col-sm-2 col-xs-12 status">
                                                        <p><strong>{{ trans('Body') }}:</strong></p>
                                                    </div>
                                                    <div class="col-md-11 col-sm-10 col-xs-12">
                                                        <textarea disabled class="form-control" style="height:150px">{{ item.body }}</textarea>                                                        
                                                    </div>
                                                </div>
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
            this.entities_list = this.params.log;
            $('#filter_name').val(this.params.filter.filter_name);
        }
    }
</script>
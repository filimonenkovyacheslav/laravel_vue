<template>
    <div class="user-dashboard-right dashboard-with-panel">
        <div class="board-header board-header-4">
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <div class="board-header-left">
                        <h3 class="board-title">{{ trans('Parsers') }}</h3>
                    </div>
                    <div class="board-header-right">
                        <ol class="breadcrumb">
                            <li itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
                                <a itemprop="url" v-bind:href:href="route('home')">
                                    <span itemprop="title">{{ trans('Home') }}</span>
                                </a>
                            </li>
                            <li class="active">{{ trans('Parsers') }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="dashboard-content-area dashboard-fix">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="my-property-listing">
                        <div class="row grid-row" v-for="item, index in entities_list.data">
                            <div class="item-wrap">
                                <div class="media my-property">
                                    <div class="media-body align-self-center">
                                        <div class="my-description">
                                            <h4 class="my-heading"><a v-bind:href="item.url">{{ item.url }}</a>
                                                <span>
                                                    <!--<strong>{{ trans('Status') }}: </strong>-->
                                                    <strong v-for="status, index in params.parser_statuses" v-if="index==item.status">{{ status }}</strong>
                                                    <span v-if="item.message" class="parser-error"> {{ item.message }}</span>
                                                </span>
                                            </h4>
                                            <div class="status">
                                                <p>
                                                    <span><strong>{{ trans('Last Start') }}:</strong> {{ item.last_start }} </span>
                                                    <span><strong>{{ trans('Last Result') }}:</strong> {{ item.last_result }} </span>
                                                    <span><strong>{{ trans('Total Saved') }}:</strong> {{ item.all_results }} </span>
                                                    <span></span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="my-actions">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions <i class="fa fa-angle-down"></i></button>
                                                <ul class="dropdown-menu actions-dropdown">
                                                    <li v-if="inArray(item.status, [0, 4, 5])"><a v-bind:href="route('start.parser.admin', {'id': item.id})"><i class="fa fa-play"></i> {{ trans('Start') }}</a></li>
                                                    <li v-if="inArray(item.status, [1, 2, 3])"><a v-bind:href="route('stop.parser.admin', {'id': item.id})"><i class="fa fa-stop"></i> {{ trans('Stop') }}</a></li>
                                                    <li><a href="#"><i class="fa fa-edit"></i> {{ trans('Edit') }}</a></li>
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
            <div class="my-profile-search">
                <div>
                    <form action="/save-proxy-list" id="proxy-list" method="POST">
                        <h4 class="my-heading">{{ trans('Proxy List') }} </h4>
                        <div class="status">
                                <span><strong>{{ trans('All') }}:</strong> {{ params.proxies.all }} </span>
                                <span><strong>{{ trans('Alive') }}:</strong> {{ params.proxies.alive }} </span>
                                <input type="hidden" name="_token" v-model="csrf">
                            <p>
                            <textarea name="proxies" :value="params.proxies.list" style="height:200px; width:300px" class="form-control"></textarea>
                            </p>
                            <input type="hidden" name="delete_dead" value="0">
                            <button type="submit" class="btn btn-primary">{{ trans('Save') }}</button>
                            <button type="button" class="btn btn-primary" @click="deleteDead">{{ trans('Delete Dead') }}</button>
                        </div>
                    </form>
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
            this.entities_list = this.params.parsers;
        },
        methods: {
            deleteDead: function(e) {
                $('#proxy-list input[name="delete_dead"]').val(1);
                $('#proxy-list').submit();
            },
        }
    }
</script>
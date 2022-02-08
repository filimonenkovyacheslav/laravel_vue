<template>
    <div class="user-dashboard-right dashboard-with-panel">
        <div class="board-header board-header-4">
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <div class="board-header-left">
                        <h3 class="board-title">{{ trans('Import Log') }}</h3>
                    </div>
                    <div class="board-header-right">
                        <ol class="breadcrumb">
                            <li itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
                                <a itemprop="url" v-bind:href:href="route('home')">
                                    <span itemprop="title">{{ trans('Home') }}</span>
                                </a>
                            </li>
                            <li class="active">{{ trans('Import Log') }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="dashboard-content-area dashboard-fix">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="my-profile-search">
                        <form method="get" id="filter-import-runs" action="">
                            <div class="profile-top-left">
                                <div class="row no-margin">
                                    <div class="form-group no-margin row-form" style="width:100%">
                                        <select class="sort-select form-control" name="link_id" id="filter-link" :value="params.filter.link.id">
                                            <option v-for="item, i in params.import_links" :value="item.id"> {{ item.link }} </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div>
                        <hr>
                        <div class="my-profile-search my-property" v-if="params.filter.link">
                            <div class="status">
                                <p>
                                    <strong>{{ trans('Author') }}</strong>
                                    <span>
                                        <a :href="route(params.filter.link.user.type + '.view.frontend', {'slug': getCompanySlug(params.filter.link.user)})">
                                            {{ getCompanyName(params.filter.link.user) }}
                                        </a>
                                    </span> 
                                </p>
                                <p>
                                    <strong>{{ trans('Status') }}:</strong>
                                    <span v-for="status, index in params.import_statuses" v-if="index==params.filter.link.status">{{ status }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="my-property-listing">
                        <table class="table-striped table-runs">
                        <thead>
                            <tr>
                                <th>Start</th>
                                <th>Stop</th>
                                <th>Status</th>
                                <th>Properties<br>inserted</th>
                                <th>Properties<br>updated</th>
                                <th>Properties<br>deleted</th>
                                <th>Files<br>added</th>
                                <th>Files<br>removed</th>
                                <th>Errors</th>
                            </tr>
                        </thead>
                        <tbody>
                        <tr v-for="item, index in entities_list.data">
                            <td>{{ item.run_date }} {{ item.run_time }}</td>
                            <td>{{ item.ended }}</td>
                            <td><a class="blue" v-bind:href="route('user.import.log')+'?id='+item.id">{{ item.status_label }}</a></td>
                            <td><a class="blue" v-bind:href="route('user.import.log')+'?id='+item.id+'&type=11'">{{ item.cnt_inserted }}</a></td>
                            <td><a class="blue" v-bind:href="route('user.import.log')+'?id='+item.id+'&type=12'">{{ item.cnt_updated }}</a></td>
                            <td><a class="blue" v-bind:href="route('user.import.log')+'?id='+item.id+'&type=13'">{{ item.cnt_deleted }}</a></td>
                            <td><a class="blue" v-bind:href="route('user.import.log')+'?id='+item.id+'&type=21'">{{ item.files_added }}</a></td>
                            <td><a class="blue" v-bind:href="route('user.import.log')+'?id='+item.id+'&type=23'">{{ item.files_deleted }}</a></td>
                            <td><a class="blue" v-bind:href="route('user.import.log')+'?id='+item.id+'&type=0'">{{ item.cnt_errors }}</a></td>
                        </tr>
                        </tbody>
                        </table>
                    </div>
                    <pagination :pagination="entities_list"></pagination>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['params'],
        mounted: function() {
            this.entities_list = this.params.import_runs;
            var self = this,
                searchForm = $('#filter-import-runs');

            searchForm.find('select[name="link_id"]').on('change', function() {
                document.location.href = self.route('user.import.runs', {'id': $(this).val()});
            });
        },
    }
</script>
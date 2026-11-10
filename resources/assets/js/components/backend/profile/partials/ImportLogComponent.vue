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
                        <form method="get" id="filter-import-log" action="">
                        <div class="profile-top-left">
                            <div class="my-profile-search my-property" v-if="params.filter.link">
                                <div class="status">
                                    <p>
                                        <strong>{{ trans('Link') }}</strong>
                                        <span>{{ params.filter.link.link }}</span> 
                                    </p>
                                    <p>
                                        <strong>{{ trans('Author') }}</strong>
                                        <span>
                                            <a :href="route(params.filter.link.user.type + '.view.frontend', {'slug': getCompanySlug(params.filter.link.user)})">
                                                {{ getCompanyName(params.filter.link.user) }}
                                            </a>
                                        </span> 
                                    </p>
                                    <p>
                                        <strong>{{ trans('Run ID') }}:</strong>
                                        <span>{{ params.filter.run.id }}</span>
                                        <strong>{{ trans('Status') }}:</strong>
                                        <span>{{ params.filter.run.status_label }}</span>
                                        <strong>{{ trans('Start') }}:</strong>
                                        <span>{{ params.filter.run.run_date }} {{params.filter.run.run_time }}</span>
                                        <strong>{{ trans('Stop') }}:</strong>
                                        <span>{{ params.filter.run.ended }}</span>
                                        <strong>{{ trans('Results') }}:</strong>
                                        <span>{{ params.filter.run.cnt_inserted }} / {{ params.filter.run.cnt_updated }} / {{ params.filter.run.cnt_deleted }} / {{ params.filter.run.files_added }} / {{ params.filter.run.files_deleted }} / <span style="color:red">{{ params.filter.run.cnt_errors }}</span> </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                            <div class="profile-top-right">
                                <div class="sort-tab text-right">
                                    <select class="sort-select form-control" name="type" :value="params.filter.type">
                                        <option value="">{{ trans('All records') }}</option>
                                        <option value="0">{{ trans('Error') }}</option>
                                        <option value="11">{{ trans('Property Inserted') }}</option>
                                        <option value="12">{{ trans('Property Updated') }}</option>
                                        <option value="13">{{ trans('Property Deleted') }}</option>
                                        <option value="21">{{ trans('File Added') }}</option>
                                        <option value="23">{{ trans('File Deleted') }}</option>
                                    </select>
                                    <input type="hidden" name="id" :value="params.filter.run.id">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="my-property-listing">
                        <table class="table-striped table-log">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Entity Type</th>
                                <th v-if="params.user_role=='administrator'">Entity ID</th>
                                <th>Result</th>
                                <th>Message</th>
                            </tr>
                        </thead>
                        <tbody>
                        <tr v-for="item, index in entities_list.data">
                            <td class="text-center">{{ item.import_id }}</span></td>
                            <td>{{ item.type_label }}</td>
                            <td class="text-center" v-if="params.user_role=='administrator'">
                                <a class="blue" target="_blank" v-if="item.property_slug" v-bind:href="route('property.view.frontend', {'slug': item.property_slug})">{{ item.entity_id }}</a>
                                <span v-else>{{ item.entity_id }}</span>
                            </td>
                            <td>{{ item.result_label }}</td>
                            <td>{{ item.message }}</td>
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
            this.entities_list = this.params.import_log;
            var searchForm = $('#filter-import-log');

            searchForm.find('select[name="type"]').on('change', function() {
                searchForm.submit();
            });
        },
    }
</script>
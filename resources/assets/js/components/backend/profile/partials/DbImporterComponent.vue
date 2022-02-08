<template>
    <div class="user-dashboard-right dashboard-with-panel">
        <div class="board-header board-header-4">
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <div class="board-header-left">
                        <h3 class="board-title">{{ trans('Database Updater') }}</span></h3>
                    </div>
                    <div class="board-header-right">
                        <ol class="breadcrumb">
                            <li itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
                                <a itemprop="url" :href="route('home')">
                                    <span itemprop="title">{{ trans('Home') }}</span>
                                </a>
                            </li>
                            <li class="active">{{ trans('Database Updater') }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="dashboard-content-area dashboard-fix dashboard-db-updater">
            <message-bar :message="importedItemsMessageData" :errors="importedItemsErrorsList" :isError="importedItemsErrorsExist" :isProcessing="importedItemsIsProcessing"></message-bar>
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="profile-content-area">
                        <div class="row">
                            <div class="col-lg-2 col-md-4 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label for="importType">{{ trans('Import') }}</label>
                                    <select name="importType" id="importType" v-model="importType" class="form-control">
                                        <option v-for="item, index in importTypesList" :value="index">{{ item }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label for="limit">{{ trans('Limit') }}</label>
                                    <input type="text" name="limit" id="limit" v-model.number="importedItemsLimit" class="form-control"/>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label for="offset">{{ trans('Offset') }}</label>
                                    <input type="text" name="offset" id="offset" v-model.number="importedItemsOffset" class="form-control"/>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label for="count">{{ trans('Count') }}</label>
                                    <input type="text" name="count" id="count" v-model.number="importedItemsCount" class="form-control" disabled="disabled"/>
                                </div>
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group btnsGroup">
                                    <button class="btn btn-primary importBtn" @click="importDb">{{ trans('Import Data From Old Database') }}</button>
                                    <button class="btn btn-primary watermarkBtn" @click="addWatermaks">{{ trans('Add Watermarks to all Images') }}</button>
                                </div>
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
        data: function() {
            return {
                importType: 'agency_agents',
                importTypesList: {
                    properties: 'Properties',
                    users: 'Users',
                    properties_users: 'Properties Users',
                    agency_agents: 'Agency Agents',
                    properties_attachment: 'Properties Attachments',
                    users_attachment: 'Users Attachment',
                    regenerate_emails: 'Regenerate Emails'
                },
                importedItemsCount: null,
                importedItemsLimit: 200,
                importedItemsOffset: 0,
                importedItemsIsProcessing: false,
                importedItemsMessageData: '',
                importedItemsErrorsExist: false,
                importedItemsErrorsList: []
            }
        },
        props: ['params'],
        methods: {
            getImportType: function() {
                return this.importType;
            },
            importDb: function() {
                var self = this;

                $('.watermarkBtn').attr('disabled', 'disabled');

                self.importType = self.getImportType();

                if(self.importType) {
                    if(!self.importedItemsIsProcessing) {
                        self.importedItemsIsProcessing = true;
                        self.importedItemsMessageData = 'Import in progress';
                    }
                    axios.post('/db-import', {
                        type: self.importType,
                        count: self.importedItemsCount,
                        limit: self.importedItemsLimit,
                        offset: self.importedItemsOffset,
                        _token: $('[name="_token"]').val()
                    }).then(function(response) {
                        if(!response.data.errors_exist) {
                            self.importedItemsCount = response.data.count;
                            self.importedItemsLimit = response.data.limit;
                            self.importedItemsOffset = response.data.offset;

                            self.importedItemsErrorsExist = false;
                            self.importedItemsErrorsList = [];

                            if(self.importedItemsOffset < self.importedItemsCount) {
                                self.importedItemsOffset += self.importedItemsLimit;
                                self.importDb();
                            } else {
                                self.importedItemsIsProcessing = false;
                                self.importedItemsMessageData = response.data.message;
                                $('.watermarkBtn').removeAttr('disabled');
                            }
                        } else {
                            self.importedItemsErrorsExist = true;
                            self.importedItemsErrorsList = response.data.errors;
                            self.importedItemsMessageData = response.data.message;
                        }
                    }).catch(function(error) {
                        self.importedItemsIsProcessing = false;
                        self.importedItemsErrorsExist = true;
                        self.importedItemsErrorsList = [];
                        self.importedItemsMessageData = 'Request returns an error!';
                    });
                }
            },
            addWatermaks: function() {
                var self = this;

                $('.importBtn').attr('disabled', 'disabled');
                self.importType = 'add_watermarks';

                axios.post('/add-watermarks', {
                    type: 'add_watermarks',
                    count: self.importedItemsCount,
                    limit: self.importedItemsLimit,
                    offset: self.importedItemsOffset,
                    _token: $('[name="_token"]').val()
                }).then(function(response) {
                    if(!response.data.errors_exist) {
                        self.importedItemsCount = response.data.count;
                        self.importedItemsLimit = response.data.limit;
                        self.importedItemsOffset = response.data.offset;

                        self.importedItemsErrorsExist = false;
                        self.importedItemsErrorsList = [];

                        if(self.importedItemsOffset < self.importedItemsCount) {
                            self.importedItemsOffset += self.importedItemsLimit;
                            setTimeout(function() {
                                self.addWatermaks();
                            }, 500);
                        } else {
                            self.importedItemsIsProcessing = false;
                            self.importedItemsMessageData = response.data.message;
                            $('.importBtn').removeAttr('disabled');
                        }
                    } else {
                        self.importedItemsErrorsExist = true;
                        self.importedItemsErrorsList = response.data.errors;
                        self.importedItemsMessageData = response.data.message;
                    }
                }).catch(function(error) {
                    console.log(error);
                });
            }
        }
    }
</script>
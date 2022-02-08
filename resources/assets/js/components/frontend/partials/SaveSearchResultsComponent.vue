<template>
    <div class="save-search-results">
        <button type="button" class="btn btn-black btn-block save-search-results-btn" v-on:click="autorized ? showDialog('save-search-results') : window.location.href = route('login')">{{ trans('Save Search Results') }}</button>
        <modal name="save-search-results" :width="600" :height="250">
            <div class="modal-content-shell">
                <h1 class="title">{{ trans('Save Search Results') }}</h1>
                <form action="/save-search-result" method="post" class="form-horizontal" v-on:submit.prevent="onSubmit">
                    <div class="row">
                        <div class="col-sm-12 col-xs-12">
                            <div class="form-group">
                                <input type="text" name="name" :value="getSearchResultsName()" class="form-control" :placeholder="trans('Search Name')">
                            </div>
                        </div>
                        <div class="col-sm-4 offset-4 col-xs-12">
                            <div class="form-group">
                                <button class="btn btn-light btn-block">{{ trans('Save') }}</button>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="_token" v-model="csrf" />
                    <input type="hidden" name="path" :value="window.location.pathname" class="form-control" />
                    <input type="hidden" name="params" :value="window.location.search" class="form-control" />
                    <input type="hidden" name="results" :value="getSearchresultsIds()" class="form-control" :placeholder="trans('Search Name')" />
                </form>
            </div>
        </modal>
    </div>
</template>

<script>
    export default {
        props: ['entities', 'autorized'],
        methods: {
            getSearchResultsName: function() {
                var type = window.location.pathname.split('/'),
                    address = this.urlGetParam('search_location'),
                    result = this.ucFirst(type[type.length - 1]) + (address ? ' - ' + address : '');
                
                return result;
            },
            getSearchresultsIds: function() {
                var ids = [];
                for(var i = 0; i < this.entities.length; i++) {
                    ids.push(this.entities[i].property_id);
                }
                return ids;
            },
            onSubmit: function(e) {
                var self = this,
                    formData = $(e.target).serializeArray(),
                    data = { _token: $('[name="_token"]').val() };

                for(var i = 0; i < formData.length; i++) {
                    data[formData[i].name] = formData[i].value;
                }
                $.post({
                    url: '/save-search-result',
                    data: data,
                    dataType: 'json',
                    success: function (data) {
                        self.$modal.hide('save-search-results');
                    }
                });
            }
        }
    }
</script>
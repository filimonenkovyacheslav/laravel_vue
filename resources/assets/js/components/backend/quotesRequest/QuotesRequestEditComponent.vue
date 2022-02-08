<template v-if="params.user_role=='administrator'">
    <div class="dashboard-content-area dashboard-fix">
        <message-bar :message="messageData" :errors="errorsList" :isError="errorsExist"></message-bar>
        <div class="container">
            <div class="row dashboard-inner-main">
                <div class="col-lg-10 col-md-9 col-sm-12 dashboard-inner-left">
                    <form id="save-quotesRequest-form" name="saveQuotesRequestForm" action="/save-quotesRequest" method="post" enctype="multipart/form-data" class="add-frontend-quotesRequest">
                        <div class="submit-form-wrap">
                            <div class="account-block form-step active">
                                <div class="add-title-tab">
                                    <h3>{{ trans('Quotes Request') }}</h3>
                                    <div class="add-expand"></div>
                                </div>
                                <div class="add-tab-content">
                                    <div class="add-tab-row push-padding-bottom">
                                        <div class="row">
                                            <div class="col-sm-12" v-if="params.user_role=='administrator'" >
                                                <div class="form-group">
                                                    <label for="author">{{ trans('Quotes Request for User ID') }}</label>
                                                    <input class="form-control" type="text" name="author" id="author"/>
                                                </div>
                                            </div>
											<div class="col-sm-12" >
                                                <div class="form-group">
                                                    <label for="title">{{ trans('Quotes Request ID') }}</label>
                                                    <input readonly class="form-control" type="text" name="id" id="id" v-model="entity.id"/>
                                                </div>
                                            </div>
											<div class="col-sm-12">
                                                <div class="form-group">
                                                    <label>{{ trans('Info') }}</label>
                                                    <tinymce name="info" id="info" :value="entity.info" :content="entity.info"></tinymce>
                                                </div>
                                            </div>
											<div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="quotes_id">{{ trans('Quotes Request Category') }}</label>
                                                    <select name="quotes_id" id="quotes_id" v-model="entity.quotes_id" class="form-control" @change="">
														<option value="" :selected="true" :disabled="true">{{ trans('Choose Quotes Request Category') }}</option>
                                                        <option v-for="item, index in params.categories" :value="item">{{ index }}</option>
                                                    </select>
                                                </div>
                                            </div>
											<div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="status">{{ trans('Quotes Request Status') }}</label>
                                                    <select name="status" id="status" v-model="entity.status" class="form-control" @change="">
														<option value="" :selected="true" :disabled="true">{{ trans('Choose Quotes Request Status') }}</option>
                                                        <option v-for="item, index in params.statuses" :value="index">{{ item }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
								</div>
							</div>

                            <input type="hidden" name="lang_id" v-model="entity.lang_id">
                            <input type="hidden" name="user_id" v-model="entity.user_id">
                            <input type="hidden" name="_token" v-model="csrf">
                        </div>
                    </form>

                </div>
                <div class="col-lg-2 col-md-3 col-sm-12 dashboard-inner-right">
                    <div class="dashboard-sidebar">
                        <div class="dashboard-sidebar-inner">
                            <button type="submit" class="btn btn-default btn-block" @click.stop="onSubmit()">{{ trans('Save') }}</button>
                            <!--<button id="save_as_draft" class="btn btn-default btn-block">Save as draft</button>-->
                            <a v-if="entity && entity.slug" :href="route('jobEntity.view.frontend', {'slug': entity.slug})" class="btn btn-default btn-block">{{ trans('View') }}</a>
                            <a :href="route('user.profile.quotesRequests')" class="btn btn-default btn-block">{{ trans('Quotes Requests Lists') }}</a>
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
                propOptsDependencies: null,
            }
        },
        props: ['params', 'entityType'],
        created: function() {
            var self = this,
                id = this.params.entity.id ? '/' + this.params.entity.id : '';
            axios.post('/api/quotesRequest' + id, { _token: this.csrf }).then(function(response) {
                self.entity = response.data.entity;
				self.categories = response.data.categories;
				self.statuses = response.data.statuses;
                self.$eventHub.$emit('entityLoaded');
            }).catch(function(error) {
                console.log(error);
            });
        },
        mounted: function() {
            var self = this,
                form = $('#save-quotesRequest-form'),
                userRole = this.params.user_role;

            this.$eventHub.$on('entityLoaded', function() {
                //self.propOptsDependencies = self.params.job_type_links;
                self._prerareValuesForSelects();
            });
            //console.log(this.params);

            if(userRole == 'administrator') {
                this.getUserAutocomplete(form.find('input[name="author"]'), {
                    'role': userRole == 'administrator' ? 'all' : '',
                    onSelect: function(item, event) {
                        form.find('input[name="user_id"]').val(item.id);
                        self.entity.user_id = item.id;
                        self.entity.author = item.label;
                    }
                });
            }
        },
        methods: {
            _prerareValuesForSelects: function() {
				$('#author').val(this.entity.user.name + ' (ID ' +this.entity.user.id + ')');
            },
            onSubmit: function() {
                tinyMCE.triggerSave();

                var self = this,
					oldForm = document.forms.saveQuotesRequestForm,
					formData = new FormData(oldForm);

                axios.post('/save-quotesRequest', formData).then(function(response) {
                    self.messageData = response.data.message ? response.data.message : '';
                    self.errorsExist = response.data.errors_exist ? response.data.errors_exist : false;
                    self.errorsList = response.data.errors ? response.data.errors : [];
                    self.entity = response.data.entity ? response.data.entity : self.entity;

                    self._prerareValuesForSelects();

                    if(response.data.redirect) {
                        setTimeout(function() {
                            window.location.href = response.data.redirect;
                        }, 500);
                    }
                }).catch(function(error) {
                    console.log(error);
                });
            }
        }
    }
</script>

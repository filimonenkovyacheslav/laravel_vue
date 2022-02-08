<template>
    <div class="user-dashboard-right dashboard-with-panel">
        <div class="board-header board-header-4">
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <div class="board-header-left">
                        <h3 class="board-title">{{ trans('Users List') }} <span v-if="params.filter.status!=null">({{ params.filter.status }})</span></h3>
                    </div>
                    <div class="board-header-right">
                        <ol class="breadcrumb">
                            <li itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
                                <a itemprop="url" v-bind:href:href="route('home')">
                                    <span itemprop="title">{{ trans('Home') }}</span>
                                </a>
                            </li>
                            <li class="active">{{ trans('Users List') }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="dashboard-content-area dashboard-fix">
        	<div class="row" v-if="params.user_role=='administrator'">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-2" v-for="counter, counterName in params.counters">
                            <div class="form-group">
                                <b>{{ counter.title }}:</b>
                                <br>
                                <span>(<b>{{ trans('total') }}:</b> {{ counter.count }})</span>
                                <span>(<b>{{ trans('published') }}:</b> {{ counter.published }})</span>
                            </div>
                        </div>
                    </div>
                </div>
	    		<div class="col-md-12">
	    			<div class="form-group">
		                <b>{{ trans('Bulk Actions') }}:</b>
		    				<select id="bulkActions" class="form-control bulk-edit-user-statuses" style="display: inline-block; max-width:200px;">
		    					<option selected disabled value="">{{ trans('Statuses') }}</option>
		    					<option v-for="item, index in params.user_statuses" :value="item.id">{{ item.label }}</option>
		    				</select>
		                <button class="btn btn-primary" style="margin-top:-2px" @click.stop="bulkEdit()">{{ trans('Apply') }}</button>
		                <button class="btn btn-secondary" style="margin-top:-2px" @click.stop="bulkFeatured()">{{ trans('Featured') }}</button>
		                <button class="btn btn-danger" style="margin-top:-2px" @click.stop="bulkRemoveLabel()">{{ trans('Remove labels') }}</button>
		                <button class="btn btn-danger" style="margin-top:-2px" @click.stop="bulkDelete()">{{ trans('Delete') }}</button>
	             	</div>
	    		</div>
    		</div>
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="my-profile-search">
                        <div class="profile-top-left profile-filter-small">
                            <form method="get" action="">
                                <input type="hidden" name="prop_status" value="">
                                <div class="single-input-search">
                                    <input class="form-control" name="name" :placeholder="trans('Enter name')+' or ID:num'" type="text" :value="params.filter.name">
                                    <button type="submit"></button>
                                </div>
                            </form>
                        </div>
                        <div class="profile-top-right text-right profile-filter-big">
                            <div class="sort-tab text-right">
                                <a v-bind:href="route('register')+'?role=all'" target="_blank" style="float:right;margin-left:3px" class="btn btn-primary">{{ trans('Add User') }}</a>
								<select class="sort-select form-control" id="profession_id" style="display:none">
                                    <option value="">{{ trans('All Professions') }}</option>
                                    <option v-for="item, index in params.profession_categories" :value="item.profession_id"> {{ item.name }} </option>
                                </select>
								<select class="sort-select form-control" id="filter_role">
                                    <option value="">{{ trans('All Roles') }}</option>
                                    <option v-for="item, index in params.user_roles" :value="item.name"> {{ item.title }} </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="my-property-listing">
                        <div class="row grid-row">
                            <div class="item-wrap">
                                <div class="media my-property">
                                    <input id="toggleBulkButton" type="checkbox" v-on:change="toggleBulkEditItems" style="margin-right:10px;">
                                    <div class="media-left">
                                        <div class="figure-block">
                                            <figure class="item-thumb" style="line-height:1.1;">
                                                <label for="toggleBulkButton" style="line-height:1;margin:0;">{{ trans('Select all') }}</label>
                                            </figure>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row grid-row" v-for="item, index in entities_list.data">
                            <div class="item-wrap one-user-row">
                                <div class="media my-property">
									<input type="checkbox" :value="item.id" class="bulkEditItems" id="bulkEditItems" />
                                    <div class="media-left">
                                        <div class="figure-block">
                                            <figure class="item-thumb">
                                                <a :href="route(item.type + '.view.frontend', {'slug': getCompanySlug(item)})">
                                                    <div class="figure-image"><img class="user-preview" :src="getImageUrl(item.photoImage.name)"></div>
                                                </a>
                                                <!--<div class="figure-image"><img class="user-preview" :src="getImageUrl(item.photoImage.name)"></div>-->
                                            </figure>
                                        </div>
                                    </div>
                                    <div class="media-body align-self-center">
                                        <div class="my-description">
                                            <h4 class="my-heading">
                                                <a :href="route(item.type + '.view.frontend', {'slug': getCompanySlug(item)})">
                                                    {{ item.first_name }} {{ item.last_name }} <span v-if="item.name!=''">({{ item.name }})</span>
                                                </a>
                                            </h4>
                                            <div class="status">
                                                <p>
                                                    <span><strong>{{ trans('Status') }}:</strong> <span v-for="status, index in params.user_statuses" v-if="index==item.status">{{ status.label }}</span></span>
                                                    <span><strong>{{ trans('ID') }}:</strong> {{ item.id }} </span>
                                                    <span></span>
                                                    <span><strong>{{ trans('Role') }}:</strong> {{ item.role_title }} </span>
                                                    <span v-if="item.agency">
                                                        <strong>{{ trans('Agency') }}:</strong>
                                                        <label v-if="item.agency.id">
                                                            <a :href="route(item.agency.type + '.view.frontend', {'slug': getCompanySlug(item.agency)})">
                                                                {{ getCompanyName(item.agency) }}
                                                            </a>
                                                        </label>
                                                    </span>
                                                    <span></span>
                                                    <span v-if="item.label" class="label-wrap">
                                                        <span v-for="lbl, name in params.user_labels" v-if="item.label && item.label==lbl.id" :class="'label-default label label-'+lbl.color"> {{ lbl.label }}</span>
                                                    </span>
                                                </p>
                                            </div>
                                            <div class="status">
                                                <p>
                                                    <span><strong>{{ trans('Date') }}:</strong> {{ item.created_format }} </span>
                                                    <span></span>
                                                    <span>
                                                        <strong>Location suggestion: </strong>
                                                        <span v-if="item.keywords && item.keywords.length">Set</span>
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="my-actions">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ trans('Actions') }} <i class="fa fa-angle-down"></i></button>
                                                <ul class="dropdown-menu actions-dropdown">
                                                    <li><a v-bind:href="route('user.edit.admin', {'id': item.id})"><i class="fa fa-edit"></i> {{ trans('Edit') }}</a></li>
                                                    <li v-if="item.status!='1'"><a v-bind:href="route('user.status.admin', {'id': item.id, 'status': 1})"><i class="fa fa-check"></i> {{ trans('Approve') }}</a></li>
                                                    <li v-if="item.status!='2'"><a v-bind:href="route('user.status.admin', {'id': item.id, 'status': 2})"><i class="fa fa-close"></i> {{ trans('Reject') }}</a></li>
                                                    <li v-if="item.status!='4'"><a v-bind:href="route('user.status.admin', {'id': item.id, 'status': 4})"><i class="fa fa-close"></i> {{ trans('Unpublish') }}</a></li>
                                                    <li v-for="lbl, name in params.user_labels" v-if="item.status==1 && item.label!=lbl.id">
                                                        <a v-bind:href="route('user.label.admin', {'id': item.id, 'label': name})" class="label-property">
                                                            <i class="fa fa-tags" :style="'color:'+item.color"></i> {{ trans('Set') }} {{ lbl.label }}
                                                        </a>
                                                    </li>
                                                    <li v-if="item.status==1 && item.label">
                                                        <a v-bind:href="route('user.label.admin', {'id': item.id, 'label': 0})" class="label-property">
                                                            <i class="fa fa-remove"></i> {{ trans('Remove label') }}
                                                        </a>
                                                    </li>
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
            this.entities_list = this.params.users;
			if (this.params.filter.role === 'professional') {
				$('#profession_id').show();
			}
            $('#filter_role').val(this.params.filter.role).on('change', function() {
                document.location.href = '?role=' + $(this).val();
            });
			$('#profession_id').val(this.params.filter.profession_id).on('change', function() {
                document.location.href = '?role=professional&profession_id=' + $(this).val();
            });
        },
		methods: {
			bulkEdit: function() {
				var editItems = new Array();
				var status = jQuery('.bulk-edit-user-statuses').val();
				jQuery('.bulkEditItems').each(function(){
					if (jQuery(this).is(":checked")) {
						editItems.push(jQuery(this).val());
					}
				});
				var self = this,
				formData = new FormData();
				formData.append("editItems", editItems);
				formData.append("status", status);
				axios.post('/bulk-edit-users', formData).then(function(response) {
                    self.messageData = response.data.message ? response.data.message : '';
                    self.errorsExist = response.data.errors_exist ? response.data.errors_exist : false;
                    self.errorsList = response.data.errors ? response.data.errors : [];
                    self.entity = response.data.entity ? response.data.entity : self.entity;
                    if(response.data.message === 'Done') {
                        setTimeout(function() {
                            window.location.href = window.location.href;
                        }, 500);
                    }
                }).catch(function(error) {
                    console.log(error);
                });
			},
			bulkDelete: function() {
				var editItems = new Array();
				jQuery('.bulkEditItems').each(function(){
					if (jQuery(this).is(":checked")) {
						editItems.push(jQuery(this).val());
					}
				});
				var self = this,
				formData = new FormData();
				formData.append("editItems", editItems);
				axios.post('/bulk-delete-users', formData).then(function(response) {
                    self.messageData = response.data.message ? response.data.message : '';
                    self.errorsExist = response.data.errors_exist ? response.data.errors_exist : false;
                    self.errorsList = response.data.errors ? response.data.errors : [];
                    self.entity = response.data.entity ? response.data.entity : self.entity;
                    if(response.data.message === 'Done') {
                        setTimeout(function() {
                            window.location.href = window.location.href;
                        }, 500);
                    }
                }).catch(function(error) {
                    console.log(error);
                });
			},
      bulkFeatured: function() {
				var editItems = new Array();
				jQuery('.bulkEditItems').each(function(){
					if (jQuery(this).is(":checked")) {
						editItems.push(jQuery(this).val());
					}
				});
				var self = this,
				formData = new FormData();
				formData.append("editItems", editItems);
        formData.append("label", 'featured');
				axios.post('/bulk-label-users', formData).then(function(response) {
                    self.messageData = response.data.message ? response.data.message : '';
                    self.errorsExist = response.data.errors_exist ? response.data.errors_exist : false;
                    self.errorsList = response.data.errors ? response.data.errors : [];
                    self.entity = response.data.entity ? response.data.entity : self.entity;
                    if(response.data.message === 'Done') {
                        setTimeout(function() {
                            window.location.href = window.location.href;
                        }, 500);
                    }
                }).catch(function(error) {
                    console.log(error);
                });
			},
      bulkRemoveLabel: function() {
				var editItems = new Array();
				jQuery('.bulkEditItems').each(function(){
					if (jQuery(this).is(":checked")) {
						editItems.push(jQuery(this).val());
					}
				});
				var self = this,
				formData = new FormData();
				formData.append("editItems", editItems);
        formData.append("label", 'remove');
				axios.post('/bulk-label-users', formData).then(function(response) {
                    self.messageData = response.data.message ? response.data.message : '';
                    self.errorsExist = response.data.errors_exist ? response.data.errors_exist : false;
                    self.errorsList = response.data.errors ? response.data.errors : [];
                    self.entity = response.data.entity ? response.data.entity : self.entity;
                    if(response.data.message === 'Done') {
                        setTimeout(function() {
                            window.location.href = window.location.href;
                        }, 500);
                    }
                }).catch(function(error) {
                    console.log(error);
                });
			}
		}
    }
</script>

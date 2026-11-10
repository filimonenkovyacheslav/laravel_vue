<template>
    <div class="user-dashboard-right dashboard-with-panel">
        <div class="board-header board-header-4">
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <div class="board-header-left">
                        <h3 class="board-title">{{ params.user.page_name }}</h3>
                    </div>
                    <div class="board-header-right">
                        <ol class="breadcrumb">
                            <li itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
                                <a itemprop="url" v-bind:href="route('home')">
                                    <span itemprop="title">{{ trans('Home') }}</span>
                                </a>
                            </li>
                            <li class="active">{{ params.user.page_name }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="dashboard-content-area dashboard-fix">
			<div class="row" v-if="params.user_role=='administrator'">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12" v-for="counter, counterName in params.counters">
                            <div class="form-group">
                                <b>{{ counter.title }}:</b>
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
							<option v-for="item, index in params.wine_status" :value="item.id">{{ item.label }}</option>
						</select>
                        <button class="btn btn-primary" style="margin-top:-2px" @click.stop="bulkEdit()">{{ trans('Apply') }}</button>
                        <button v-for="item, name in params.wine_labels" class="btn btn-secondary ml-1" style="margin-top:-2px" @click.stop="bulkLabel(name)">{{ item.label }}</button> 
                        <button class="btn btn-danger" style="margin-top:-2px" @click.stop="bulkRemoveLabel()">{{ trans('Remove labels') }}</button>
                        <button class="btn btn-danger" style="margin-top:-2px" @click.stop="bulkDelete()">{{ trans('Delete') }}</button>
                    </div>
				</div>
			</div>
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="my-profile-search">
                        <form action="" method="get" autocomplete="off" id="search-entity-form">
                            <div class="profile-top-left">
                                <div class="row no-margin">
                                    <div class="form-group no-margin" style="width:200px">
                                        <input class="form-control" name="keyword" :placeholder="trans('Enter keyword')+' or ID:num'" type="text" :value="params.filter.keyword">
                                    </div>
                                    <div class="form-group no-margin" style="width:200px">
                                        <select name="wine_labels" class="form-control bulk-edit-user-labels" style="display: inline-block; max-width:200px;" :value="params.filter.wine_labels">
                                            <option selected disabled value="">{{ trans('Labels') }}</option>
                                            <option v-for="item, index in params.wine_labels" :value="item.id">{{ item.label }}</option>
                                        </select>
                                    </div>
                                    <div class="location-field" style="width:300px">
                                        <div class="form-group no-margin">
                                            <div class="search-location">
                                                <i class="location-trigger fa fa-dot-circle-o" style="line-height: 38px;"></i>
                                                <input type="text" name="search_location" id="search_location" :value="urlGetParam('search_location')" class="form-control" :placeholder="trans('Location')">
                                                <input type="hidden" name="key" :value="urlGetParam('key')">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group no-margin" style="width:100px">
                                        <button type="submit" class="btn btn-secondary btn-block">{{ trans('Search') }}</button>
                                    </div>
                                </div>
                            </div>
                            <div class="profile-top-right">
                                <div class="sort-tab text-right">
                                    <select class="sort-select form-control" name="order_by" :value="urlGetParam('order_by')">
                                        <option value="">{{ trans('Default Order') }}</option>
                                        <option value="a_price">{{ trans('Price Low to High') }}</option>
                                        <option value="d_price">{{ trans('Price High to Low') }}</option>
                                        <option value="a_date">{{ trans('Date Old to New') }}</option>
                                        <option value="d_date">{{ trans('Date New to Old') }}</option>
                                       <option value="title">{{ trans('Title') }}</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                        <message-bar :message="messageData" :errors="errorsList" :isError="errorsExist"></message-bar>
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
                        <div class="row grid-row" v-for="prop, index in entities_list.data">
                            <div class="item-wrap" v-if="prop.status!='5' || ( prop.status=='5' && inArray(params.user_role, ['administrator']) )">
                                <div class="media my-property">
									<input type="checkbox" :value="prop.id" class="bulkEditItems" id="bulkEditItems" style="margin-top:47px"/>
                                    <div class="media-left">
                                        <div class="figure-block">
                                            <figure class="item-thumb">
                                                <a v-bind:href="route('wine.view.frontend', {'slug': prop.slug})">
                                                    <div class="figure-image" v-bind:style="getBgImageStyle(getFeaturedImageName(prop.uploadsList))"></div>
                                                </a>
                                            </figure>
                                        </div>
                                    </div>
                                    <div class="media-body align-self-center">
                                        <div class="my-description">
                                            <h4 class="my-heading">
                                                <a v-bind:href="route('wine.view.frontend', {'slug': prop.slug})">{{ prop.title }}</a>
                                            </h4>
                                            <div class="item-categories" v-if="prop.categories.length">
                                                <strong>Categories:</strong> <span class="item-category" v-for="category, index in prop.categories" v-if="params.wine_categories_front[category]">{{ params.wine_categories_front[category] }}</span>
                                            </div>
                                            <div class="status">
                                                <p>
                                                    <span>
                                                        <strong>Price:</strong>
                                                        <span class="item-price" v-if="prop.price_view">{{ prop.price_view.local.price }} / {{ prop.price_view.default.price }}</span>
                                                    </span>
                                                    <span><strong>Status:</strong> {{ prop.status_view.label }}</span>
                                                    <span class="label-wrap">
                                                        <span v-if="prop.label" :class="'label-default label label-'+prop.label_view.color"> {{ prop.label_view.label }}</span>
                                                    </span>
                                                    <span></span>
                                                </p>
                                            </div>
                                            <div class="status">
                                                <div v-if="inArray(params.user_role, ['administrator']) && prop.user && prop.user.id" class="prop-user-agent">
                                                    <p class="prop-user-agent">
                                                        <i class="fa fa-user"></i>
                                                        <a :href="route('user.edit.admin', {'id': prop.user.id})" target="_blank">{{ prop.user.first_name+' '+prop.user.last_name }}</a>
                                                    </p>
                                                </div>
                                                <p>
                                                    <span><strong>Created:</strong> {{ prop.created_at }}</span>
                                                    <span>
                                                        <strong>Location suggestion: </strong>
                                                        <span v-if="prop.keywords && prop.keywords.length">Set</span>
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                        <div v-if="prop.author==params.user.id || inArray(params.user_role, ['administrator'])" class="my-actions">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ trans('Actions') }} <i class="fa fa-angle-down"></i></button>
                                                <ul class="dropdown-menu actions-dropdown">
                                                    <li><a v-bind:href="route('wine.edit.admin', {'id': prop.id})"><i class="fa fa-edit"></i> {{ trans('Edit') }}</a></li>
                                                    <li v-if="inArray(prop.status, [2, 5, 6]) && inArray(params.user_role, ['administrator'])">
                                                        <a v-bind:href="route('wine.status.admin', {'id': prop.id, 'status': 1})">
                                                            <i class="fa fa-check"></i> {{ trans('Approve') }}
                                                        </a>
                                                    </li>
                                                    <li v-for="item, name in params.wine_labels" v-if="prop.status==1 && prop.label!=item.id && inArray(params.user_role, ['administrator'])">
                                                        <a v-bind:href="route('wine.label.admin', {'id': prop.id, 'label': name})" class="label-property">
                                                            <i class="fa fa-tags" :style="'color:'+item.color"></i> {{ trans('Set') }} {{ item.label }}
                                                        </a>
                                                    </li>
                                                    <li v-if="prop.status==1 && prop.label && inArray(params.user_role, ['administrator'])">
                                                        <a v-bind:href="route('wine.label.admin', {'id': prop.id, 'label': 0})" class="label-property">
                                                            <i class="fa fa-remove"></i> {{ trans('Remove label') }}
                                                        </a>
                                                    </li>
													<li v-if="prop.status!='6' && prop.status!='5'">
                                                        <a v-bind:href="route('wine.unpublish.admin', {'id': prop.id})" class="unpublish-property">
                                                            <i class="fa fa-minus"></i> {{ trans('Unpublish') }}
                                                        </a>
                                                    </li>
                                                    <li v-if="prop.status!='5' || (prop.status=='5' && inArray(params.user_role, ['administrator']))">
                                                        <a v-bind:href="route('wine.delete.admin', {'id': prop.id})" class="delete-property">
                                                            <i class="fa fa-trash"></i> {{ trans('Delete') }}
                                                        </a>
                                                    </li>
                                                    <li v-if="prop.status!='5'">
                                                        <a v-bind:href="route('wine.clone.admin', {'id': prop.id})" class="clone-property">
                                                            <i class="fa fa-edit"></i> {{ trans('Duplicate') }}
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <pagination :pagination="entities_list"></pagination>
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
        props: ['params'],
        mounted: function() {
            this.entities_list = this.params.user.wines;
            var self = this,
                locationField = $('.location-field'),
                locationShell = $('.search-location'),
                locationInput = locationShell.find('input[name="search_location"]'),
                searchForm = $('form[id="search-entity-form"]');

            self.getAddressKeywordsAutocomplete(locationInput, {type: 'wine'},
                function(item, event) {
                    locationShell.find('input[name="key"]').val(item.id);
                }
            );

            searchForm.on('submit', function() {
                if (locationInput.val() == '') {
                    locationShell.find('input[name="key"]').val('');
                } else if (locationShell.find('input[name="key"]').val() == '') {
                    self.messageData = 'Be sure to choose a location hint';
                    self.errorsExist = true;
                    return false;
                }
                return true;
            });
            searchForm.find('select[name="order_by"]').on('change', function() {
                searchForm.submit();
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
				axios.post('/bulk-edit-wines', formData).then(function(response) {
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
				axios.post('/bulk-delete-wines', formData).then(function(response) {
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
        bulkLabel: function(label) {
                var editItems = new Array();
                jQuery('.bulkEditItems').each(function(){
                    if (jQuery(this).is(":checked")) {
                        editItems.push(jQuery(this).val());
                    }
                });
                var self = this,
                formData = new FormData();
                formData.append("editItems", editItems);
                formData.append("label", label);
                axios.post('/bulk-label-wines', formData).then(function(response) {
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
				axios.post('/bulk-label-wines', formData).then(function(response) {
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
		},
    }
</script>

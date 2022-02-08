<template>
    <div class="user-dashboard-right dashboard-with-panel">
        <div class="board-header board-header-4">
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <div class="board-header-left">
                        <h3 class="board-title">{{ trans('Jobs List') }}</h3>
                    </div>
                    <div class="board-header-right">
                        <ol class="breadcrumb">
                            <li itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
                                <a itemprop="url" v-bind:href="route('home')">
                                    <span itemprop="title">{{ trans('Home') }}</span>
                                </a>
                            </li>
                            <li class="active">{{ trans('Jobs List') }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="dashboard-content-area dashboard-fix">
			<form v-if="params.user_role=='administrator'" id="save-jobEntity-settings" name="saveJobEntitySettingsForm" action="/save-jobEntity-settings" method="post" class="add-frontend-jobEntity">
				<div class="row">
						<div class="col-sm-2">
							<div class="form-group">
								<input type="checkbox" name="job_user_add" id="job_user_add" v-model="jobUserCanCreatePrepare" @change="switchCheckboxIcon" style="display: none;">
								<div class="btn-group">
									<label for="job_user_add" class="btn btn-default">
										<span :class="'checkbox-icon fa' + (jobUserCanCreatePrepare == 'on' ? ' fa-check' : '')"></span>
									</label>
									<label for="job_user_add" class="btn btn-default active" >{{ trans('Can a users create a job') }}</label>
								</div>
							</div>
						</div>
						<div class="col-sm-2">
							<div class="form-group">
								<div class="btn-group">
									<label for="job_limit" class="btn btn-default">
										{{ trans('User job posts limit') }}
									</label>
									<input style="border-radius:0px 4px 4px 0px; border-left:0px;" :value="jobLimit" type="number" min="0" name="job_limit" id="job_limit" class="form-control"/>
								</div>
							</div>
						</div>
						<div class="col-sm-1">
							<div class="form-group">
								<input type="hidden" name="_token" v-model="csrf">
								<button type="submit" class="btn btn-primary">{{ trans('Save') }}</button>
							</div>
						</div>
            	</div>
			</form>
            <div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<div v-if="jobUserCanCreate && inArray(this.params.user_role, ['professional', 'franchise']) && !inArray(this.params.user_role, ['administrator'])" class="alert alert-info">{{ trans('You have added') }} {{ this.params.user.job_entities_count }} {{ trans('of') }} {{ jobLimit }} {{  trans('available job ads.')}}</div>
				</div>
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="my-profile-search">
                        <form action="" method="get" autocomplete="off" id="search-entity-form">
                            <div class="profile-top-left">
                                <div class="row no-margin">
                                    <div class="form-group no-margin" style="width:200px;">
                                        <input class="form-control" name="keyword" :placeholder="trans('Enter keyword...')" type="text" :value="params.filter.keyword">
                                    </div>
                                    <div class="location-field" style="width:300px; margin:0px 10px;">
                                        <div class="form-group no-margin">
                                            <div class="search-location">
                                                <i class="location-trigger fa fa-dot-circle-o" style="line-height: 38px;"></i>
                                                <input type="text" name="search_location" id="search_location" :value="urlGetParam('search_location')" class="form-control" :placeholder="trans('Location')">
                                                <input type="hidden" name="lat" :value="urlGetParam('lat')">
                                                <input type="hidden" name="lng" :value="urlGetParam('lng')">
                                                <input type="hidden" name="ao" :value="urlGetParam('ao')">
                                                <input type="hidden" name="ar" :value="urlGetParam('ar')">
                                                <input type="hidden" name="ac" :value="urlGetParam('ac')">
                                                <input type="hidden" name="as" :value="urlGetParam('as')">
                                                <input type="hidden" name="ai" :value="urlGetParam('ai')">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group no-margin" style="width:100px;">
                                        <input type="text" name="radius" :value="urlGetParam('radius')" class="form-control" :placeholder="trans('No Radius')">
                                    </div>
                                    <div class="form-group" style="width:100px; margin-left:10px;">
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
                        <input type="hidden" id="location_address" :value="urlGetParam('search_location')">
                    </div>
                    <div class="my-jobEntity-listing">
                        <div class="row grid-row" v-for="prop, index in entities_list.data">
                            <div class="item-wrap">
                                <div class="media my-jobEntity">
                                    <div class="media-left">
                                        <div class="figure-block">
                                            <figure class="item-thumb">
                                                <a v-bind:href="route('jobEntity.view.frontend', {'slug': prop.slug})">
                                                    <div class="figure-image" v-bind:style="getBgImageStyle(getFeaturedImageName(prop.uploadsList))"></div>
                                                </a>
                                            </figure>
                                        </div>
                                    </div>
                                    <div class="media-body align-self-center">
                                        <div class="my-description">
                                            <h4 class="my-heading">
                                                <a v-bind:href="route('jobEntity.view.frontend', {'slug': prop.slug})">{{ prop.title }}</a>
                                            </h4>
                                            <div class="status">
                                                <div v-if="inArray(params.user_role, ['administrator']) && prop.user && prop.user.id" class="prop-user-agent">
                                                    <p v-if="prop.user.agency && prop.user.agency.id" class="prop-user-agent">
                                                        <i class="fa fa-home"></i>
                                                        <a :href="route('user.edit.admin', {'id': prop.user.agency.id})" target="_blank">{{ getCompanyName(prop.user.agency) }}</a>
                                                    </p>
                                                    <p v-if="prop.user.is_agency" class="prop-user-agent">
                                                        <i class="fa fa-home"></i>
                                                        <a :href="route('user.edit.admin', {'id': prop.user.id})" target="_blank">{{ getCompanyName(prop.user) }}</a>
                                                    </p>
                                                    <p v-else class="prop-user-agent">
                                                        <i class="fa fa-user"></i>
                                                        <a :href="route('user.edit.admin', {'id': prop.user.id})" target="_blank">{{ prop.user.first_name+' '+prop.user.last_name }}</a>
                                                    </p>
                                                </div>
                                                <p>
                                                    <span>{{ prop.status_view.label }}</span>
                                                    <span class="label-wrap">
                                                        <span v-if="prop.label" :class="'label-default label label-'+prop.label_view.color"> {{ prop.label_view.label }}</span>
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                        <div v-if="prop.author==params.user.id || inArray(params.user_role, ['administrator'])" class="my-actions">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ trans('Actions') }} <i class="fa fa-angle-down"></i></button>
                                                <ul class="dropdown-menu actions-dropdown">
                                                    <li>
														<a v-bind:href="route('jobEntity.edit.admin', {'id': prop.id})" class="label-jobEntity">
															<i class="fa fa-edit"></i> {{ trans('Edit') }}
														</a>
													</li>
                                                    <li v-if="inArray(prop.status, [2, 5]) && inArray(params.user_role, ['administrator'])">
                                                        <a v-bind:href="route('jobEntity.status.admin', {'id': prop.id, 'status': 1})" class="label-jobEntity">
                                                            <i class="fa fa-check"></i> {{ trans('Approve') }}
                                                        </a>
                                                    </li>
                                                    <li v-for="item, name in params.jobEntity_labels" v-if="prop.status==1 && prop.label!=item.id && inArray(params.user_role, ['administrator'])">
                                                        <a v-bind:href="route('jobEntity.label.admin', {'id': prop.id, 'label': name})" class="label-jobEntity">
                                                            <i class="fa fa-tags" :style="'color:'+item.color"></i> {{ trans('Set') }} {{ item.label }}
                                                        </a>
                                                    </li>
													<li class="favorite-btn" v-on:click="toggleFavoriteJobEntity(prop.id)">
														<a href="#" class="label-jobEntity">
                                                            <span style="color:red" class="add_fav" title="Add to Favorite">
																<i v-bind:class="'fa ' + (prop.is_favorite ? 'fa-heart' : 'fa-heart-o')"></i> {{ trans('Favorite') }}
															</span>
                                                        </a>
											        </li>
                                                    <li v-if="prop.status==1 && prop.label && inArray(params.user_role, ['administrator'])">
                                                        <a v-bind:href="route('jobEntity.label.admin', {'id': prop.id, 'label': 0})" class="label-jobEntity">
                                                            <i class="fa fa-remove"></i> {{ trans('Remove label') }}
                                                        </a>
                                                    </li>
                                                    <li v-if="prop.status!='5'">
                                                        <a v-bind:href="route('jobEntity.delete.admin', {'id': prop.id})" class="label-jobEntity delete-jobEntity">
                                                            <i class="fa fa-trash"></i> {{ trans('Delete') }}
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
		data: function() {
            return {
				jobLimitTotal: 0,
				jobLimitUser: 0,
				jobLimit: 0,
				jobUserCanCreatePrepare: '',
				jobUserCanCreate: '',
            }
        },
        mounted: function() {
            this.entities_list = this.params.user.jobEntities;

			this.jobUserCanCreatePrepare = (this.params.filter && this.params.filter.settings && this.params.filter.settings.job_entity && this.params.filter.settings.job_entity.job_user_add) ? 'on' : '';
			this.jobUserCanCreate = this.jobUserCanCreatePrepare || this.inArray(this.params.user_role, ['administrator']) ?  'on' : '';
			this.jobLimitTotal = (this.params.filter && this.params.filter.settings && this.params.filter.settings.job_entity && this.params.filter.settings.job_entity.job_limit) ? this.params.filter.settings.job_entity.job_limit : 0;
			this.jobLimitUser = (this.params.user.job_limit) ? this.params.user.job_limit : 0;
			this.jobLimit =  (this.jobLimitUser > 0) ? this.jobLimitUser : this.jobLimitTotal;

            var locationField = $('.location-field'),
                locationShell = $('.search-location'),
                locationAddress = $('#location_address'),
                locationInput = locationShell.find('input[name="search_location"]'),
                searchForm = $('form[id="search-entity-form"]');

            this.geoSearchAutocompleate(document.getElementById('search_location'), {
                setMarker: false,
                onSelect: function(item, event) {
                    locationShell.find('input[name="lat"]').val(item.lat);
                    locationShell.find('input[name="lng"]').val(item.lng);
                    locationShell.find('input[name="ao"]').val(item.other);
                    locationShell.find('input[name="ar"]').val(item.street);
                    locationShell.find('input[name="ac"]').val(item.city);
                    locationShell.find('input[name="as"]').val(item.state);
                    locationShell.find('input[name="ai"]').val(item.iso2);
                    locationAddress.val(item.label);
                }
            });

            searchForm.on('submit', function() {
                if(locationAddress.val() != locationInput.val()) {
                    locationShell.find('input[name="lat"]').val('');
                    locationShell.find('input[name="lng"]').val('');
                    locationShell.find('input[name="ao"]').val('');
                    locationShell.find('input[name="ar"]').val('');
                    locationShell.find('input[name="ac"]').val('');
                    locationShell.find('input[name="as"]').val('');
                    locationShell.find('input[name="ai"]').val('');
                }
                return true;
            });
            searchForm.find('select[name="order_by"]').on('change', function() {
                searchForm.submit();
            });
        },
    }
</script>

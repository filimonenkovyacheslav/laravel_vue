<template>
    <div class="user-dashboard-right dashboard-with-panel">
        <div class="board-header board-header-4">
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <div class="board-header-left">
                        <h3 class="board-title">{{ trans('Recommendations Requests') }}</h3>
                    </div>
                    <div class="board-header-right">
                        <ol class="breadcrumb">
                            <li itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
                                <a itemprop="url" v-bind:href:href="route('home')">
                                    <span itemprop="title">{{ trans('Home') }}</span>
                                </a>
                            </li>
                            <li class="active">{{ trans('Recommendations Requests') }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="dashboard-content-area dashboard-fix">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="my-profile-search">
                        <div class="profile-top-left" style="width:100%;">
                            <form action="" method="get" autocomplete="off" id="search-entity-form">
                                <input type="hidden" name="prop_status" value="">
								<div class="profile-top-right">
	                                <div class="sort-tab text-right">
	                                    <select class="sort-select form-control" name="order_by" :value="urlGetParam('order_by')">
	                                        <option value="">{{ trans('Default Order') }}</option>
	                                        <option value="a_id">{{ trans('ID ASC') }}</option>
											<option value="d_id">{{ trans('ID DESC') }}</option>
											<option v-if="inArray(params.user_role, ['administrator'])" value="a_user_id">{{ trans('User ASC') }}</option>
											<option v-if="inArray(params.user_role, ['administrator'])" value="d_user_id">{{ trans('User DESC') }}</option>
											<option value="a_date">{{ trans('Date added ASC') }}</option>
											<option value="d_date">{{ trans('Date added DESC') }}</option>
											<option value="a_quotes_id">{{ trans('Recommendations Category ASC') }}</option>
	                                        <option value="d_quotes_id">{{ trans('Recommendations Category DESC') }}</option>
	                                        <option value="a_status">{{ trans('Status ASC') }}</option>
	                                        <option value="d_status">{{ trans('Status DESC') }}</option>
	                                    </select>
	                                </div>
	                            </div>
                            </form>
                        </div>
                        <div class="profile-top-right">
                            <div class="sort-tab text-right">
                                <!-- <a v-bind:href="route('profession.edit.admin')" class="btn btn-primary">{{ trans('Add Profession') }}</a> -->
                            </div>
                        </div>
                    </div>
                    <div class="my-property-listing">
                        <div class="row grid-row" v-for="item, index in entities_list.data">
                            <div class="item-wrap">
                                <div class="media quotes-request-item my-property">
                                    <div class="media-body align-self-center">
                                        <div class="my-description">
											<div class="row">
												<div class="col-md-2">
													<div class="quotes-request-item-title">{{ trans('Recommendations Request') }} {{ item.id }} <span v-if="inArray(params.user_role, ['administrator'])"> {{ trans('for') }} <span class="quotes-request-item-title-author">{{ item.user.name }}</span></span></div>
													<div class="quotes-request-item-category">{{ trans('Quote category:') }} {{ item.quotes.quotes_title }}</div>
													<div class="quotes-request-item-date">{{ trans('Date added:') }} {{ item.created_at }}</div>
													<div class="quotes-request-item-status">{{ trans('Status:') }} {{ item.status_label }}</div>
													<div v-if="inArray(params.user_role, ['administrator'])" class="my-actions quotes-request-item-actions">
			                                            <div class="btn-group">
			                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ trans('Actions') }} <i class="fa fa-angle-down"></i></button>
			                                                <ul class="dropdown-menu actions-dropdown">
			                                                    <li><a v-bind:href="route('quotesRequest.edit.admin', {'id': item.id})"><i class="fa fa-edit"></i> {{ trans('Edit') }}</a></li>
			                                                    <li><a v-bind:href="route('quotesRequest.delete.admin', {'id': item.id})" class="delete-profession"><i class="fa fa-close"></i> {{ trans('Delete') }}</a></li>
			                                                </ul>
			                                            </div>
			                                        </div>
												</div>
												<div class="col-md-10">
													<div class="quotes-request-item-description" v-html="item.info"></div>
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
				this.entities_list = this.params.user.quotesRequests;
				var searchForm = $('form[id="search-entity-form"]');
				searchForm.find('select[name="order_by"]').on('change', function() {
	                searchForm.submit();
	            });
        },
    }
</script>

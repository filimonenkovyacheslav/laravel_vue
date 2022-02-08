<template>
    <div id="section-body" class="landing-page">
        <div id="compare-controller" class="compare-panel">
            <div class="compare-panel-header">
                <h4 class="title"> {{ trans('Compare Listings') }} <span class="panel-btn-close pull-right"><i class="fa fa-times"></i></span></h4>
            </div>
            <div id="compare-properties-basket"></div>
        </div>
        <div class="user-dashboard-left">
            <div class="dashboard-bar fave-screen-fix">
                <ul class="board-panel-menu">
                    <li :class="[params.route_name == 'user.profile.profile' ? 'active' : '']">
                        <a :href="route('user.profile.profile')"><i class="fa fa-user"></i>{{ trans('My profile') }}</a>
                    </li>
                    <li v-if="params.agency_agents[params.user_role]">
                        <a :href="route('user.profile.agents')">
                            <i class="fa fa-users"></i>{{ trans('My') }} {{ params.agency_agents[params.user_role].title }}
                            <sup v-if="params.user.new_agents" style="color:red">{{ params.user.new_agents }}</sup>
                        </a>
                    </li>

                    <!-- <li v-if="inArray(params.user_role, ['administrator'])"><a :href="route('user.profile.designCategories')"><i class="fa fa-tags"></i>{{ trans('Architecture & Design Categories') }}</a></li>
                    <template v-if="inArray(params.user_role, ['administrator', 'architect_firm', 'building_company', 'architect', 'building_company_agent'])">
                        <li :class="[params.route_name == 'user.profile.designs' ? 'active' : '']">
                            <a :href="route('user.profile.designs')"><i class="fa fa-building"></i>
                                <span v-if="inArray(params.user_role, ['administrator'])">{{ trans('Architecture & Design List') }}</span>
                                <span v-else>{{ trans('My Architecture & Design') }}</span>
                            </a>
                            <ul class="sub-menu">
                                <li><a :href="route('user.profile.designs')">{{ trans('All') }}</a></li>
                                <li><a :href="route('user.profile.designs', {'params': 'publish'})">{{ trans('Published') }}</a></li>
                                <li><a :href="route('user.profile.designs', {'params': 'pending'})">{{ trans('Pending') }}</a></li>
                                <li><a :href="route('user.profile.designs', {'params': 'expired'})">{{ trans('Expired') }}</a></li>
                                <li><a :href="route('user.profile.designs', {'params': 'draft'})">{{ trans('Draft') }}</a></li>
                                <li v-if="inArray(params.user_role, ['administrator'])"><a :href="route('user.profile.designs', {'params': 'deleted'})">{{ trans('Deleted') }}</a></li>
                                <li><a :href="route('user.profile.designs', {'params': 'favorite'})">{{ trans('Favourite') }}</a></li>
                            </ul>
                        </li>
                        <li><a :href="route('design.edit.admin')"><i class="fa fa-plus-circle"></i>{{ trans('Add new architecture or design') }}</a></li>
                    </template> -->

                    <li v-if="inArray(params.user_role, ['administrator'])"><a :href="route('user.profile.goodCategories')"><i class="fa fa-tags"></i>{{ trans('Goods Categories') }}</a></li>
                    <template v-if="inArray(params.user_role, ['brand', 'administrator'])">
                        <li :class="[params.route_name == 'user.profile.goods' ? 'active' : '']">
                            <a :href="route('user.profile.goods')"><i class="fa fa-building"></i>
                                <span v-if="inArray(params.user_role, ['administrator'])">{{ trans('Goods List') }}</span>
                                <span v-else>{{ trans('My Goods') }}</span>
                            </a>
                            <ul class="sub-menu">
                                <li><a :href="route('user.profile.goods')">{{ trans('All') }}</a></li>
                                <li><a :href="route('user.profile.goods', {'params': 'publish'})">{{ trans('Published') }}</a></li>
                                <li><a :href="route('user.profile.goods', {'params': 'pending'})">{{ trans('Pending') }}</a></li>
                                <li><a :href="route('user.profile.goods', {'params': 'expired'})">{{ trans('Expired') }}</a></li>
                                <li><a :href="route('user.profile.goods', {'params': 'draft'})">{{ trans('Draft') }}</a></li>
                                <li v-if="inArray(params.user_role, ['administrator'])"><a :href="route('user.profile.goods', {'params': 'deleted'})">{{ trans('Deleted') }}</a></li>
                                <li><a :href="route('user.profile.goods', {'params': 'favorite'})">{{ trans('Favourite') }}</a></li>
                            </ul>
                        </li>
                        <li><a :href="route('good.edit.admin')"><i class="fa fa-plus-circle"></i>{{ trans('Add new good') }}</a></li>
                    </template>

                    <li v-if="inArray(params.user_role, ['administrator'])"><a :href="route('user.profile.productCategories')"><i class="fa fa-tags"></i>{{ trans('Product Categories') }}</a></li>
                    <template v-if="inArray(params.user_role, ['seller', 'administrator'])">
                        <li :class="[params.route_name == 'user.profile.products' ? 'active' : '']">
                            <a :href="route('user.profile.products')"><i class="fa fa-building"></i>
                                <span v-if="inArray(params.user_role, ['administrator'])">{{ trans('Product List') }}</span>
                                <span v-else>{{ trans('My Products') }}</span>
                            </a>
                            <ul class="sub-menu">
                                <li><a :href="route('user.profile.products')">{{ trans('All') }}</a></li>
                                <li><a :href="route('user.profile.products', {'params': 'publish'})">{{ trans('Published') }}</a></li>
                                <li><a :href="route('user.profile.products', {'params': 'pending'})">{{ trans('Pending') }}</a></li>
                                <li><a :href="route('user.profile.products', {'params': 'expired'})">{{ trans('Expired') }}</a></li>
                                <li><a :href="route('user.profile.products', {'params': 'draft'})">{{ trans('Draft') }}</a></li>
                                <li v-if="inArray(params.user_role, ['administrator'])"><a :href="route('user.profile.products', {'params': 'deleted'})">{{ trans('Deleted') }}</a></li>
                                <li><a :href="route('user.profile.products', {'params': 'favorite'})">{{ trans('Favourite') }}</a></li>
                            </ul>
                        </li>
                        <li><a :href="route('product.edit.admin')"><i class="fa fa-plus-circle"></i>{{ trans('Add new product') }}</a></li>
                    </template>

                    <template>
                        <li :class="[params.route_name == 'user.profile.news' ? 'active' : '']">
                            <a :href="route('user.profile.news')"><i class="fa fa-building"></i>
                                <span v-if="inArray(params.user_role, ['administrator'])">{{ trans('News List') }}</span>
                                <span v-else>{{ trans('My News') }}</span>
                            </a>
                            <ul class="sub-menu">
                                <li><a :href="route('user.profile.news')">{{ trans('All') }}</a></li>
                                <li><a :href="route('user.profile.news', {'params': 'publish'})">{{ trans('Published') }}</a></li>
                                <li><a :href="route('user.profile.news', {'params': 'pending'})">{{ trans('Pending') }}</a></li>
                                <li><a :href="route('user.profile.news', {'params': 'expired'})">{{ trans('Expired') }}</a></li>
                                <li><a :href="route('user.profile.news', {'params': 'draft'})">{{ trans('Draft') }}</a></li>
                                <li v-if="inArray(params.user_role, ['administrator'])"><a :href="route('user.profile.news', {'params': 'deleted'})">{{ trans('Deleted') }}</a></li>
                                <li><a :href="route('user.profile.news', {'params': 'favorite'})">{{ trans('Favourite') }}</a></li>
                            </ul>
                        </li>
                        <li><a :href="route('news.edit.admin')"><i class="fa fa-plus-circle"></i>{{ trans('Add new News') }}</a></li>
                    </template>

                    <!-- <li v-if="inArray(params.user_role, ['administrator'])"><a :href="route('user.profile.wineCategories')"><i class="fa fa-tags"></i>{{ trans('Wine Categories') }}</a></li>
                    <template v-if="inArray(params.user_role, ['wineseller', 'administrator'])">
                        <li :class="[params.route_name == 'user.profile.wines' ? 'active' : '']">
                            <a :href="route('user.profile.wines')"><i class="fa fa-building"></i>
                                <span v-if="inArray(params.user_role, ['administrator'])">{{ trans('Wine List') }}</span>
                                <span v-else>{{ trans('My Wines') }}</span>
                            </a>
                            <ul class="sub-menu">
                                <li><a :href="route('user.profile.wines')">{{ trans('All') }}</a></li>
                                <li><a :href="route('user.profile.wines', {'params': 'publish'})">{{ trans('Published') }}</a></li>
                                <li><a :href="route('user.profile.wines', {'params': 'pending'})">{{ trans('Pending') }}</a></li>
                                <li><a :href="route('user.profile.wines', {'params': 'expired'})">{{ trans('Expired') }}</a></li>
                                <li><a :href="route('user.profile.wines', {'params': 'draft'})">{{ trans('Draft') }}</a></li>
                                <li v-if="inArray(params.user_role, ['administrator'])"><a :href="route('user.profile.wines', {'params': 'deleted'})">{{ trans('Deleted') }}</a></li>
                                <li><a :href="route('user.profile.wines', {'params': 'favorite'})">{{ trans('Favourite') }}</a></li>
                            </ul>
                        </li>
                        <li><a :href="route('wine.edit.admin')"><i class="fa fa-plus-circle"></i>{{ trans('Add new wine') }}</a></li>
                    </template> -->

                    <!-- <li v-if="inArray(params.user_role, ['administrator'])"><a :href="route('user.profile.furnitureCategories')"><i class="fa fa-tags"></i>{{ trans('Furniture Categories') }}</a></li>
                    <template v-if="inArray(params.user_role, ['furnitureseller', 'administrator'])">
                        <li :class="[params.route_name == 'user.profile.furnitures' ? 'active' : '']">
                            <a :href="route('user.profile.furnitures')"><i class="fa fa-building"></i>
                                <span v-if="inArray(params.user_role, ['administrator'])">{{ trans('Furniture List') }}</span>
                                <span v-else>{{ trans('My Furniture') }}</span>
                            </a>
                            <ul class="sub-menu">
                                <li><a :href="route('user.profile.furnitures')">{{ trans('All') }}</a></li>
                                <li><a :href="route('user.profile.furnitures', {'params': 'publish'})">{{ trans('Published') }}</a></li>
                                <li><a :href="route('user.profile.furnitures', {'params': 'pending'})">{{ trans('Pending') }}</a></li>
                                <li><a :href="route('user.profile.furnitures', {'params': 'expired'})">{{ trans('Expired') }}</a></li>
                                <li><a :href="route('user.profile.furnitures', {'params': 'draft'})">{{ trans('Draft') }}</a></li>
                                <li v-if="inArray(params.user_role, ['administrator'])"><a :href="route('user.profile.furnitures', {'params': 'deleted'})">{{ trans('Deleted') }}</a></li>
                                <li><a :href="route('user.profile.furnitures', {'params': 'favorite'})">{{ trans('Favourite') }}</a></li>
                            </ul>
                        </li>
                        <li><a :href="route('furniture.edit.admin')"><i class="fa fa-plus-circle"></i>{{ trans('Add new furniture') }}</a></li>
                    </template> -->
                    
                    <!-- <li v-if="inArray(params.user_role, ['administrator'])"><a :href="route('user.profile.propertyCategories')"><i class="fa fa-tags"></i>{{ trans('Property Categories') }}</a></li>
                    <template v-if="!inArray(params.user_role, ['professional'])">
                        <li :class="[params.route_name == 'user.profile.properties' ? 'active' : '']">
                            <a :href="route('user.profile.properties')"><i class="fa fa-building"></i>
                                <span v-if="inArray(params.user_role, ['administrator'])">{{ trans('Properties List') }}</span>
                                <span v-else>{{ trans('My Properties') }}</span>
                            </a>
                            <ul class="sub-menu">
                                <li><a :href="route('user.profile.properties')">{{ trans('All') }}</a></li>
                                <li><a :href="route('user.profile.properties', {'params': 'publish'})">{{ trans('Published') }}</a></li>
                                <li><a :href="route('user.profile.properties', {'params': 'pending'})">{{ trans('Pending') }}</a></li>
                                <li><a :href="route('user.profile.properties', {'params': 'expired'})">{{ trans('Expired') }}</a></li>
                                <li><a :href="route('user.profile.properties', {'params': 'draft'})">{{ trans('Draft') }}</a></li>
                                <li v-if="inArray(params.user_role, ['administrator'])"><a :href="route('user.profile.properties', {'params': 'deleted'})">{{ trans('Deleted') }}</a></li>
                            </ul>
                        </li>
                        <li><a :href="route('property.edit.admin')"><i class="fa fa-plus-circle"></i>{{ trans('Add new property') }}</a></li>
                        <li><a :href="route('user.profile.properties', {'params': 'favorite'})"><i class="fa fa-heart"></i>{{ trans('Favourite properties') }}</a></li>
                    </template> -->
                     
                    <li><a :href="route('user.profile.saved_searches')"><i class="fa fa-search-plus"></i>{{ trans('Saved Searches') }}</a></li>
                    
                    <!-- <li v-if="inArray(params.user_role, ['administrator'])">
                        <a :href="route('user.profile.features')"><i class="fa fa-tags"></i>{{ trans('Features List') }}</a>
                    </li> -->
                    
                    <li :class="[params.route_name == 'user.profile.users' ? 'active' : '']" v-if="inArray(params.user_role, ['administrator'])">
                        <a :href="route('user.profile.users')"><i class="fa fa-users"></i>{{ trans('Users List') }}</a>
                        <ul class="sub-menu">
                            <li><a :href="route('user.profile.users')">{{ trans('All') }}</a></li>
                            <li v-for="status, index in params.user_statuses"><a :href="route('user.profile.users', {'params': status.name})">{{ status.label }}</a></li>
                        </ul>
                        <li><a :href="route('user.profile.professions')"><i class="fa fa-search-plus"></i>{{ trans('Professions') }}</a></li>
                    </li>
                    <li v-if="inArray(params.user_role, ['administrator'])">
                        <a :class="[inArray(params.route_name, ['admin.ad.partners', 'admin.edit.partner']) ? 'active' : '']" :href="route('admin.ad.partners')"><i class="fa fa-handshake-o"></i>{{ trans('Partners') }}</a>
                    </li>
                    <!--<li v-if="inArray(params.user_role, ['administrator'])">
                        <a :class="[inArray(params.route_name, ['admin.ad.users', 'admin.edit.ad_user']) ? 'active' : '']" :href="route('admin.ad.users')"><i class="fa fa-buysellads"></i>{{ trans('Users Ad') }}</a>
                    </li>-->
                    <li v-if="inArray(params.user_role, ['administrator'])">
                        <a :class="[inArray(params.route_name, ['admin.profile.pages', 'admin.edit.page']) ? 'active' : '']" :href="route('admin.profile.pages')"><i class="fa fa-language"></i>{{ trans('Pages') }}</a>
                    </li>
                    <li v-if="inArray(params.user_role, ['administrator'])">
                        <a :class="[params.route_name == 'admin.profile.quotes' ? 'active' : '']" :href="route('admin.profile.quotes')"><i class="fa fa-quote-left"></i>{{ trans('Recommendations') }}</a>
                    </li>
                    <li v-if="inArray(params.user_role, ['administrator'])">
                        <a :href="route('user.profile.quotesRequests')"><i class="fa fa-quote-left"></i>{{ trans('Recommendations Requests') }}</a>
                    </li>

                    <li v-if="inArray(params.user_role, ['administrator'])">
                        <a :href="route('user.profile.ads')"><i class="fa fa-picture-o"></i>{{ trans('Image Video Ad') }}</a>
                    </li>
                    <!--<li v-if="inArray(params.user_role, ['administrator'])">
                        <a :href="route('admin.profile.parsers')"><i class="fa fa-download"></i>{{ trans('Parsers') }}</a>
                    </li>-->
                    <li :class="[inArray(params.route_name, ['admin.emails.settings', 'admin.emails.log', 'admin.emails.template']) ? 'active' : '']" v-if="inArray(params.user_role, ['administrator'])">
                        <a :href="route('admin.emails.settings')"><i class="fa fa-envelope"></i>{{ trans('Emails') }}</a>
                        <ul class="sub-menu">
                            <li><a :href="route('admin.emails.settings')">{{ trans('Settings') }}</a></li>
                            <li><a :href="route('admin.emails.log')">{{ trans('Log') }}</a></li>
                        </ul>
                    </li>
                    <!--<li v-if="inArray(params.user_role, ['administrator'])">
                        <a :href="route('user.profile.db_importer')"><i class="fa fa-database"></i>{{ trans('Db Updater') }}</a>
                    </li>-->
                    <!--<li :class="[inArray(params.route_name, ['user.import.links', 'user.import.runs', 'user.import.log']) ? 'active' : '']">
                        <a :href="route('user.import.links')"><i class="fa fa-download"></i>{{ trans('Import') }}</a>
                        <ul class="sub-menu">
                            <li><a :href="route('user.import.links')">{{ trans('Links') }}</a></li>
                            <li><a :href="route('user.import.runs')">{{ trans('Log') }}</a></li>
                        </ul>
                    </li>-->
                    <li>
                        <a :href="route('logout')" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fa fa-unlock"></i>{{ trans('Logout') }}</a>
                    </li>
                </ul>
            </div>
        </div>
        <user-profile-profile :params="params" v-if="params.route_name == 'user.profile.profile'"></user-profile-profile>
        <user-profile-ads :params="params" v-if="params.route_name == 'user.profile.ads'"></user-profile-ads>
        <user-profile-agents :params="params" v-if="params.route_name == 'user.profile.agents'"></user-profile-agents>
        <user-profile-properties :params="params" v-if="params.route_name == 'user.profile.properties'"></user-profile-properties>
        <user-profile-arts :params="params" v-if="params.route_name == 'user.profile.arts'"></user-profile-arts>
        <user-profile-features :params="params" v-if="params.route_name == 'user.profile.features'"></user-profile-features>
        <user-profile-professions :params="params" v-if="params.route_name == 'user.profile.professions'"></user-profile-professions>
        <user-profile-users :params="params" v-if="params.route_name == 'user.profile.users'"></user-profile-users>
        <user-profile-profile :params="params" v-if="params.route_name == 'user.edit.admin'"></user-profile-profile>
        <user-profile-saved-searches :params="params" v-if="params.route_name == 'user.profile.saved_searches'"></user-profile-saved-searches>
        <admin-emails-settings :params="params" v-if="params.route_name == 'admin.emails.settings'"></admin-emails-settings>
        <admin-emails-template :params="params" v-if="params.route_name == 'admin.emails.template'"></admin-emails-template>
        <admin-emails-log :params="params" v-if="params.route_name == 'admin.emails.log'"></admin-emails-log>
        <!--<admin-db-importer :params="params" v-if="params.route_name == 'user.profile.db_importer'"></admin-db-importer>-->
        <admin-profile-parsers :params="params" v-if="params.route_name == 'admin.profile.parsers'"></admin-profile-parsers>
        <admin-profile-pages :params="params" v-if="params.route_name == 'admin.profile.pages'"></admin-profile-pages>
        <admin-page-edit :params="params" v-if="params.route_name == 'admin.edit.page'"></admin-page-edit>
        <admin-home-edit :params="params" v-if="params.route_name == 'admin.edit.home'"></admin-home-edit>
        <admin-footer-edit :params="params" v-if="params.route_name == 'admin.edit.footer'"></admin-footer-edit>
        <admin-ad-partners :params="params" v-if="params.route_name == 'admin.ad.partners'"></admin-ad-partners>
        <admin-partner-edit :params="params" v-if="params.route_name == 'admin.edit.partner'"></admin-partner-edit>
        <admin-ad-users :params="params" v-if="params.route_name == 'admin.ad.users'"></admin-ad-users>
        <admin-ad-user-edit :params="params" v-if="params.route_name == 'admin.edit.ad_user'"></admin-ad-user-edit>
        <!--<user-import-links :params="params" v-if="params.route_name == 'user.import.links'"></user-import-links>
        <user-import-runs :params="params" v-if="params.route_name == 'user.import.runs'"></user-import-runs>
        <user-import-log :params="params" v-if="params.route_name == 'user.import.log'"></user-import-log>-->
        <user-profile-quotesRequests :params="params" v-if="params.route_name == 'user.profile.quotesRequests'"></user-profile-quotesRequests>
        <admin-profile-quotes :params="params" v-if="params.route_name == 'admin.profile.quotes'"></admin-profile-quotes>
        <user-profile-products :params="params" v-if="params.route_name == 'user.profile.products'"></user-profile-products>
        <user-profile-productCategories :params="params" v-if="params.route_name == 'user.profile.productCategories'"></user-profile-productCategories>
        <user-profile-wines :params="params" v-if="params.route_name == 'user.profile.wines'"></user-profile-wines>
        <user-profile-wineCategories :params="params" v-if="params.route_name == 'user.profile.wineCategories'"></user-profile-wineCategories>
        <user-profile-news :params="params" v-if="params.route_name == 'user.profile.news'"></user-profile-news>
        <user-profile-furnitures :params="params" v-if="params.route_name == 'user.profile.furnitures'"></user-profile-furnitures>
        <user-profile-furnitureCategories :params="params" v-if="params.route_name == 'user.profile.furnitureCategories'"></user-profile-furnitureCategories>
        <user-profile-goods :params="params" v-if="params.route_name == 'user.profile.goods'"></user-profile-goods>
        <user-profile-goodCategories :params="params" v-if="params.route_name == 'user.profile.goodCategories'"></user-profile-goodCategories>
        <user-profile-designs :params="params" v-if="params.route_name == 'user.profile.designs'"></user-profile-designs>
        <user-profile-designCategories :params="params" v-if="params.route_name == 'user.profile.designCategories'"></user-profile-designCategories>
        <user-profile-propertyCategories :params="params" v-if="params.route_name == 'user.profile.propertyCategories'"></user-profile-propertyCategories>

        <template v-if="inArray(params.user_role, ['administrator'])">
            <user-profile-artCategories :params="params" v-if="params.route_name == 'user.profile.artCategories'"></user-profile-artCategories>
        </template>
        <div style="clear: both;"></div>
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
				jobLimit: 10,
				jobUserCanCreate: '',
            }
        },
		mounted: function() {
			var self = this;

			this.jobUserCanCreatePrepare = (this.params.filter && this.params.filter.settings && this.params.filter.settings.job_entity && this.params.filter.settings.job_entity.job_user_add) ? 'on' : '';
			this.jobUserCanCreate = this.jobUserCanCreatePrepare || this.inArray(this.params.user_role, ['administrator']) ? true : false;
			this.jobLimit = (this.params.filter && this.params.filter.settings && this.params.filter.settings.job_entity && this.params.filter.settings.job_entity.job_limit) ? this.params.filter.settings.job_entity.job_limit : '10';

			setTimeout(function() {
                self.setProfileAreaHeight();
            }, 500);
			$(window).on('resize', function() {
				self.setProfileAreaHeight();
			});
        },
		methods: {
			setProfileAreaHeight: function() {
				var wndHeight = $(window).height(),
					headerHeight = $('#header-section').outerHeight(),
					boardHeight = $('.board-header').outerHeight(),
					height = parseInt(wndHeight - headerHeight - boardHeight) + 1;
				$('.dashboard-content-area').outerHeight(height);
			}
		}
    }

</script>

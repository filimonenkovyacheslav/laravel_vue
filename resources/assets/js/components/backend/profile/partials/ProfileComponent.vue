<template>
    <div class="user-dashboard-right dashboard-with-panel">
        <div class="board-header board-header-4">
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <div class="board-header-left">
                        <h3 class="board-title" v-if="params.not_me">{{ trans('Edit User Profile') }}</h3>
                        <h3 class="board-title" v-else>{{ trans('My Profile') }}</h3>
                    </div>
                    <div class="board-header-right">
                        <ol class="breadcrumb">
                            <li itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
                                <a :href:href="route('home')" itemprop="url"><span itemprop="title">{{ trans('Home') }}</span></a>
                            </li>
                            <li class="active">Edit <span v-if="params.not_me">User</span> <span v-else>My</span> {{ trans('Profile') }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="dashboard-content-area dashboard-fix">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="profile-content-area">
                        <div id="profile_message" class="houzez_messages message"></div>
                        <form action="/save-profile" method="post" enctype="multipart/form-data">
                            <div class="account-block account-profile-block">
                                <div class="row">
									<div class="profile-images col-md-3 col-sm-12 col-xs-12">
                                        <div class="profile-image upload-container figure-block">
											<!--<div class="profile-image rounded-circle" :style="getBgImageStyle(params.user.photoImage.name, 'avatar')" alt="User Image"></div>-->
											<div class="figure-image">
												<img :src="getImageUrl(params.user.photoImage.name)" alt="User Image">
											</div>
                                            <button type="button" class="btn btn-primary btn-block" @click="uploadImage">{{ trans('Update Profile Picture') }}</button>
                                            <p class="profile-img-info">* minimum 270px x 270px</p>
                                            <input type="file" name="photo_id" class="upload-input" @change="onImageChange" accept="image/jpeg,.jpg,.jpeg,image/gif,.gif,image/png,.png" style="display: none;" />
                                            <input type="hidden" name="photo" :value="getValue(params.fields.partials.photo.value)" />
                                        </div>
										<div class="header-media-image upload-container figure-block">
											<div class="figure-image">
                                                <img class="background-image" :src="getImageUrl(params.user.headerMedia.name)" alt="Profile Header Media" :style="!isImageBg(params.user.headerMedia.name) ? 'display:none;' : ''">
                                                <video class="background-video" :style="isImageBg(params.user.headerMedia.name) ? 'display:none;' : ''" loop="loop" autoplay="" muted="">
                                                    <source :src="params.user.img_background" :type="getVideoType(params.user.headerMedia.name)"/>
                                                </video>
											</div>
                                            <button type="button" class="btn btn-primary btn-block btn-bg-uploader" @click="uploadImage">{{ trans('Update Header Media') }}</button>
                                            <input type="file" name="header_media_id" class="upload-input" @change="onImageChange" accept="image/jpeg,.jpg,.jpeg,image/gif,.gif,image/png,.png,upload/mp4,.mp4,.m4a,.mov,upload/ogg,.ogg,.oga,.ogv,.ogx,upload/wmv,.wmv,.wma,uploads/webm,.webm,uploads/flv,.flv,uploads/avi,.avi" style="display: none;" />
                                            <input type="hidden" name="header_media" :value="getValue(params.fields.partials.header_media.value)" />
                                        </div>
                                    </div>
                                    <div class="col-md-9 col-sm-12 col-xs-12">
                                        <h4>Information</h4>
                                        <div class="row">
                                            <div class="col-sm-6 col-xs-12" v-for="field, index in params.fields.user">
                                                <html-element :field="field" :index="index" :key="index" :params="params"></html-element>
                                            </div>
                                            <div class="col-sm-6 col-xs-12" v-if="params.fields.relation && params.fields.relation.name">
                                                <html-element :field="params.fields.relation.name" :index="params.fields.relation.name.index" :params="params"></html-element>
                                            </div>
                                            <div class="col-sm-6 col-xs-12" v-if="params.fields.relation && params.fields.relation.position">
                                                <html-element :field="params.fields.relation.position" :index="params.fields.relation.position.index" :params="params"></html-element>
                                            </div>
                                            <div class="col-sm-6 col-xs-12" v-if="params.fields.relation && params.fields.relation.license">
                                                <html-element :field="params.fields.relation.license" :index="params.fields.relation.license.index" :params="params"></html-element>
                                            </div>
                                            <div class="col-sm-6 col-xs-12" v-for="field, index in params.fields.contacts">
                                                <html-element :field="field" :index="index" :key="index" :params="params"></html-element>
                                            </div>
                                            <div :class="[params.fields.relation && params.fields.relation.company_name ? 'col-sm-6' : 'col-sm-12'] + ' col-xs-12'">
                                                <html-element :field="params.fields.partials.language" :index="params.fields.partials.language.index" :params="params"></html-element>
                                            </div>
                                            <div class="col-sm-6 col-xs-12" v-if="params.fields.relation && params.fields.relation.company_name">
                                                <html-element :field="params.fields.relation.company_name" :index="params.fields.relation.company_name.index" :params="params"></html-element>
                                            </div>
                                            <div class="col-sm-12 col-xs-12" v-if="params.user.type == 'professional' && params.fields.partials && params.fields.partials.professions">
                                                <html-element :field="params.fields.partials.professions" :index="params.fields.partials.professions.index" :params="params"></html-element>
                                            </div>
                                            <div class="col-sm-12 col-xs-12" v-if="params.user.agency_agents == 'agent'">
                                                <!--<html-element :field="params.fields.partials.agencies" :index="params.fields.partials.agencies.index" :params="params"></html-element>-->
                                                <div class="form-group">
                                                    <label>{{ params.fields.agency.label }}</label>
                                                    <input type="text" name="agency_name" :value="params.user.agency && params.user.agency.id ? getCompanyName(params.user.agency) + ' (ID '+params.user.agency.id+')' : ''" class="form-control" :placeholder="trans('Enter company name or ID')" />
                                                    <input type="hidden" name="agency_id" :value="params.user.agency ? params.user.agency.id : 0"/>
                                                </div>
                                            </div>
											<div class="col-sm-12 col-xs-12" v-if="params.fields.relation && params.fields.relation.opening_hours">
                                                <html-element :field="params.fields.relation.opening_hours" :index="params.fields.relation.opening_hours.index" :params="params"></html-element>
                                            </div>
                                            <div class="col-sm-3 col-xs-6" v-if="params.fields.relation && params.fields.relation.monday">
                                                <html-element :field="params.fields.relation.monday" :index="params.fields.relation.monday.index" :params="params"></html-element>
                                            </div>
                                            <div class="col-sm-3 col-xs-6" v-if="params.fields.relation && params.fields.relation.tuesday">
                                                <html-element :field="params.fields.relation.tuesday" :index="params.fields.relation.tuesday.index" :params="params"></html-element>
                                            </div>
                                            <div class="col-sm-3 col-xs-6" v-if="params.fields.relation && params.fields.relation.wednesday">
                                                <html-element :field="params.fields.relation.wednesday" :index="params.fields.relation.wednesday.index" :params="params"></html-element>
                                            </div>
                                            <div class="col-sm-3 col-xs-6" v-if="params.fields.relation && params.fields.relation.thursday">
                                                <html-element :field="params.fields.relation.thursday" :index="params.fields.relation.thursday.index" :params="params"></html-element>
                                            </div>
                                            <div class="col-sm-3 col-xs-6" v-if="params.fields.relation && params.fields.relation.friday">
                                                <html-element :field="params.fields.relation.friday" :index="params.fields.relation.friday.index" :params="params"></html-element>
                                            </div>
                                            <div class="col-sm-3 col-xs-6" v-if="params.fields.relation && params.fields.relation.saturday">
                                                <html-element :field="params.fields.relation.saturday" :index="params.fields.relation.saturday.index" :params="params"></html-element>
                                            </div>
                                            <div class="col-sm-3 col-xs-6" v-if="params.fields.relation && params.fields.relation.sunday">
                                                <html-element :field="params.fields.relation.sunday" :index="params.fields.relation.sunday.index" :params="params"></html-element>
                                            </div>
                                            <div class="col-sm-3 col-xs-6" v-if="params.fields.relation && params.fields.relation.holiday">
                                                <html-element :field="params.fields.relation.holiday" :index="params.fields.relation.holiday.index" :params="params"></html-element>
                                            </div>
                                            <div class="col-sm-12 col-xs-12" v-if="params.fields.relation && params.fields.relation.description">
                                                <html-element :field="params.fields.relation.description" :index="params.fields.relation.description.index" :params="params"></html-element>
                                            </div>
                                            <div class="col-sm-12 col-xs-12" v-if="params.fields.relation && params.fields.relation.type">
                                                <html-element :field="params.fields.relation.type" :index="params.fields.relation.type.index" :params="params"></html-element>
                                            </div>
											<div class="col-sm-12 col-xs-12" v-if="params.fields.relation && params.fields.relation.services">
                                                <html-element :field="params.fields.relation.services" :index="params.fields.relation.services.index" :params="params"></html-element>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="country">{{ trans('Country') }}</label>
                                                    <select name="country_id" id="country_id" v-model="params.user.country_id" class="form-control">
                                                        <option v-for="v, k in params.countries" :value="k">{{ v }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="state">{{ trans('State') }}</label>
                                                    <input type="text" name="state" id="state" v-model="params.user.state" class="form-control" />
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="region">{{ trans('Greater Region') }}</label>
                                                    <input type="text" name="region" id="region" v-model="params.user.region" class="form-control" />
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="city">{{ trans('City') }}</label>
                                                    <input type="text" name="city" id="city" v-model="params.user.city" class="form-control"/>
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="form-group">
                                                    <label for="postal_code">{{ trans('Postcode') }}</label>
                                                    <input type="text" name="postal_code" id="postal_code" v-model="params.user.postal_code" class="form-control"/>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="suburb">{{ trans('Suburb') }}</label>
                                                    <input type="text" name="suburb" id="suburb" v-model="params.user.suburb" class="form-control"/>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="street">{{ trans('Street') }}</label>
                                                    <input type="text" name="street" id="street" v-model="params.user.street" class="form-control"/>
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="form-group">
                                                    <label for="house">{{ trans('House') }}</label>
                                                    <input type="text" name="house" id="house" v-model="params.user.house" class="form-control"/>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-xs-12" v-for="field, index in params.fields.address">
                                                <html-element :field="field" :index="index" :key="index" :params="params"></html-element>
                                            </div>
                                            <div class="col-sm-12 col-xs-12">
                                                <div class="map_canvas" id="map"></div>
                                                <!--<button id="find" class="btn btn-primary">Place the pin the address above</button>-->
                                                <button type="button" id="reset-marker-position" :data-map="getValue(params.fields.address.map_address.value.map)" :data-lat="getValue(params.fields.address.map_address.value.lat)" :data-lng="getValue(params.fields.address.map_address.value.lng)" class="btn btn-primary" style="display:none;" @click="resetMarkerPosition('reset-marker-position')">{{ trans('Reset Marker') }}</button>
                                            </div>
                                            <div class="col-sm-12 col-xs-12 text-right">
                                                <input type="hidden" name="_token" v-model="csrf">
                                                <a :href="route(params.user.type + '.view.frontend', {'slug': getCompanySlug(params.user)})" target="_blank" class="btn btn-primary btn-trans">{{ trans('View Public Profile') }}</a>
                                                <!--<input type="hidden" name="id" :value="params.user.id">
                                                <button class="btn btn-primary">{{ trans('Update Profile') }}</button>-->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <!--</form>
                        <form action="/save-profile" method="POST">-->
                            <div class="account-block form-step active" v-if="params.user_role=='administrator'">
                                <div class="add-title-tab">
                                    <h3>{{ trans('Address Keywords') }} <a href="#addAddressKeyword" class="btn btn-outline-primary btn-sm" @click.stop="showAddressKeywordForm()">{{ trans('Add new') }}</a>
                                    <a href="#overrideForAllEntries" class="btn btn-outline-danger btn-sm float-right" @click.stop="overrideForAllEntries()">{{ trans('Override for all entries') }}</a>
                                    </h3>
                                </div>
                                <div class="add-tab-content">
                                    <div class="add-tab-row">
                                        <div class="row user-keywords">
                                            <div class="col-sm-12 col-xs-12">
                                                <div class="form-group">
                                                    <input type="text" name="keyword" id="keyword-autocomplete" class="form-control" :placeholder="trans('Search Keyword')" />
                                                </div>
                                                <div class="form-group">
                                                    <ul class="address-keyword-list">
                                                        <li v-for="item, index in params.user.keywords">
                                                            <i class="fa fa-times"></i> <label>{{ item.keyword }}</label>
                                                            <input type="hidden" name="keywords[]" :value="item.key_id"/>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <message-bar :message="override.messageData" :errors="override.errorsList" :isError="override.errorsExist"></message-bar>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <add-address-keyword></add-address-keyword>
                            </div>
                            <div v-if="params.user_role=='administrator' && params.user.agency_agents=='agency'" class="account-block account-block-social account-profile-block">
                                <div class="row">
                                    <div class="col-md-3 col-sm-12 col-xs-12">
                                        <h4>{{ params.fields.agents.label }}</h4>
                                    </div>
                                    <div class="col-md-9 col-sm-12 col-xs-12">
                                        <div class="form-group">
                                            <input type="text" name="agent_name" class="form-control" :placeholder="params.fields.agents.search+'...'" />
                                            <input type="hidden" name="agency_id" :value="params.user.agency ? params.user.agency.id : 0" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <ul id="agents-list">
                                                <li v-for="agent, index in params.user.agents" :id="'agent'+agent.id">
                                                    <i class="fa fa-trash" @click="deleteAgent(agent.id)"></i> <label>{{ agent.first_name }} {{ agent.last_name }} (ID {{ agent.id }})</label>
                                                    <input type="hidden" name="agents[]" :value="agent.id"/>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="account-block form-step active">
                                <div class="add-title-tab">
                                    <h3>{{ trans('User media') }}</h3>
                                    <div class="add-expand"></div>
                                </div>
                                <div class="add-tab-content">
                                    <div class="add-tab-row">
                                        <template v-if="params.user_role=='artist' || params.user.type == 'artist'">
                                            <file-uploader :fieldData="{'index': 'photos[]'}" :entityData="params.user" :entityType="'user'" :isArtist="'1'"></file-uploader>
                                        </template>
                                        <template v-else>
                                            <file-uploader :fieldData="{'index': 'photos[]'}" :entityData="params.user" :entityType="'user'"></file-uploader>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <div class="account-block account-block-social account-profile-block" v-if="params.fields.social">
                                <div class="row">
                                    <div class="col-md-3 col-sm-12 col-xs-12">
                                        <h4>{{ trans('Social Media') }}</h4>
                                    </div>
                                    <div class="col-md-9 col-sm-12 col-xs-12">
                                        <div class="row">
                                            <div class="col-sm-6 col-xs-12" v-for="field, index in params.fields.social">
                                                <div class="form-group">
                                                    <label :for="index">{{ field.label }}</label>
                                                    <div class="input-group">
                                                        <span class="input-group-addon"><i :class="field.icon"></i></span>
                                                        <input type="text" :name="index" :id="index" :value="getValue(field.value)" class="form-control" >
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="_token" v-model="csrf">
                                        <input type="hidden" name="id" :value="params.user.id">
                                        <!--<input type="hidden" name="city" :value="params.user.city">-->
                                        <input type="hidden" name="country" :value="params.user.country">
                                        <button class="btn btn-primary pull-right">{{ trans('Update Profile') }}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
						<div class="account-block account-profile-block user-projects">
							<h4>{{ trans('User Projects') }}</h4>
							<div class="row">
								<div class="col-12">
									<div class="projects-list">
										<ul v-for="item, order in projects" :key="item.id" class="project-item list-three-col">
											<li><span class="sort-project"><i class="fa fa-navicon"></i></span></li>
											<li>
												<div v-if="item.title">
													<strong>{{ trans('Title') }}:</strong>
													<span> {{ item.title }}</span>
												</div>
												<div v-if="item.description">
													<strong>{{ trans('Description') }}:</strong>
													<span v-html="item.description"></span>
												</div>
											</li>
											<li><span @click="editProject(order)" :data-order="order" class="edit-project"><i class="fa fa-edit"></i></span></li>
											<li><span @click="deleteProject(order)" :data-order="order" class="remove-project"><i class="fa fa-remove"></i></span></li>
										</ul>
									</div>
								</div>
								<div class="col-12">
									<message-bar :message="messageData" :errors="errorsList" :isError="errorsExist"></message-bar>
								</div>
								<div class="col-12">
									<div class="form-group">
										<label for="project_title">{{ trans('Title') }}</label>
										<input type="text" name="project_title" v-model="projectData.title" id="project_title" class="form-control" />
										<input type="hidden" name="project_order" v-model="projectOrder" class="form-control" />
									</div>
								</div>
								<div class="col-12">
									<div class="form-group">
										<label for="project_description">{{ trans('Description') }}</label>
										<tinymce name="project_description" :value="projectData.description" id="project_description" :content="projectData.description"></tinymce>
									</div>
								</div>
								<div class="col-12">
									<div class="form-group">
										<file-uploader :idSuffixVal="'projects'" :fieldData="{'index': 'project_uploads[]'}" :entityData="projectData" :entityType="'user'"></file-uploader>
									</div>
								</div>
								<div class="col-12">
									<div class="form-group">
										<button href="#" class="btn btn-primary btn-center" style="min-width: 200px;" @click.stop.prevent="saveProjects"><i class="fa fa-save"></i> {{ trans('Save Project') }}</button>
									</div>
								</div>
							</div>
						</div>
                        <form method="POST" action="/reset-password" id="reset-password-form" v-if="!params.not_me || params.user_role == 'administrator'" v-on:submit.prevent="onSubmit">
                            <div class="account-block account-profile-block">
                                <div class="row">
                                    <div class="col-md-3 col-sm-12 col-xs-12">
                                        <h4>{{ trans('Change password') }}</h4>
                                    </div>
                                    <div class="col-md-9 col-sm-12 col-xs-12">
                                        <div class="row">
                                            <div class="col-sm-4 col-xs-12">
                                                <div class="form-group">
                                                    <label for="current_password">{{ trans('Old Password') }}</label>
                                                    <input type="password" name="current_password" id="current_password" value="" class="form-control" />
                                                </div>
                                            </div>
                                            <div class="col-sm-4 col-xs-12">
                                                <div class="form-group">
                                                    <label for="password">{{ trans('New Password') }} </label>
                                                    <input type="password" name="password" id="password" value="" class="form-control" />
                                                </div>
                                            </div>
                                            <div class="col-sm-4 col-xs-12">
                                                <div class="form-group">
                                                    <label for="password_confirmation">{{ trans('Confirm New Password') }}</label>
                                                    <input type="password" name="password_confirmation" id="password_confirmation" value="" class="form-control" />
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="_token" v-model="csrf">
                                        <input type="hidden" name="id" :value="params.user.id">
                                        <button class="btn btn-primary pull-right">{{ trans('Update Password') }}</button>
                                        <message-bar :message="messageData" :errors="errorsList" :isError="errorsExist"></message-bar>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <form action="/delete-profile" method="POST" v-if="params.user_role == 'administrator'">
                            <div class="account-block account-profile-block">
                                <div class="row">
                                    <div class="col-md-3 col-sm-12 col-xs-12">
                                        <h4 class="account-action-title"> {{ trans('Delete account') }}</h4>
                                    </div>
                                    <div class="col-md-9 col-sm-12 col-xs-12">
                                        <input type="hidden" name="_token" v-model="csrf">
                                        <input type="hidden" name="id" :value="params.user.id">
                                        <button class="btn btn-danger pull-right" id="houzez_delete_account">
                                            {{ trans('Delete') }} <span v-if="params.not_me">User</span> <span v-else>{{ trans('My') }}</span> {{ trans('Account') }} </button>
                                    </div>
                                </div>
                            </div>
                        </form>
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
				projects: [],
				projectData: {},
                projectDataDef: {
                    title: '',
                    description: '',
					uploads: [],
					uploadsData: []
                },
                projectOrder: -1,
                sortedItem: -1,
                override: {messageData: '', errorsExist: false, errorsList: []}
            }
        },
        props: ['params'],
        mounted: function() {
            var self = this,
                parent = $('.account-profile-block'),
                mapAddress = this.params.fields.address.map_address.value,
                countries = self.params.countries_codes,
                countriesNames = self.params.countries_names;

				self.projects = self.params.user.projects.slice();
				self.projectData = Object.assign({}, self.projectDataDef);

            self.geoLocationiqAutocompleate(document.getElementById('map_address'), {
                setMarker: true,
                onSelect: function(item, event) {
                    var address = parent.find('input[name="address"]');
                    if(address.val() == '') {
                        address.val(item.label);
                    }
                    parent.find('input[name="lat"]').val(item.lat);
                    parent.find('input[name="lng"]').val(item.lng).trigger('change');
                    parent.find('input[name="city"]').val(item.city);
                    parent.find('input[name="street"]').val(item.street);
                    parent.find('input[name="house"]').val(item.house);
                    parent.find('input[name="country"]').val('');
                    parent.find('select[name="country_id"]').val(item.iso2 && item.iso2 in countries ? countries[item.iso2] : (item.country in countriesNames ? countriesNames[item.country] : '')).trigger('change');
                }
            });

			$('.projects-list').sortable({
				start: function(e, ui) {
					self.sortedItem = ui.item.index();
				},
				stop: function(e, ui) {
					if(self.projects[self.sortedItem]) {
						var newIndex = ui.item.index(),
							movedItem = self.projects.splice(self.sortedItem, 1);
						self.projects.splice(newIndex, 0, movedItem[0]);
					}
				}
			});

            this.getAddressKeywordsAutocomplete($('#keyword-autocomplete'), {type: ''},
                function(item, event, ui) {
                    self.addKeyword(item);
                }
            );

            $('.address-keyword-list').on('click', 'li i', function(){
                self.deleteKeyword(this);
            });

            $('#country_id').on('change', function(){
                parent.find('input[name="country"]').val($(this).find('option:selected').text());
            });

            this.initMap('map', { 'lat': this.getValue(mapAddress.lat), 'lng': this.getValue(mapAddress.lng) }, {'draggable': true});
            parent.find('input[name="lat"], input[name="lng"]').on('change', function() {
                var lat = parent.find('input[name="lat"]').val(),
                    lng = parent.find('input[name="lng"]').val();
                self.updateMapAddressData(document.getElementById('map'), lat, lng, {'draggable': true});
                self.updateMarkerPosition(lat, lng);
            });

            if(this.params.user.agency_agents == 'agent') {
                this.getUserAutocomplete(parent.find('input[name="agency_name"]'), {'inputId': parent.find('input[name="agency_id"]'), 'role': this.params.user.agency_type});
            } else if(this.params.user.agency_agents == 'agency' && this.params.user_role=='administrator') {
                this.getUserAutocomplete(parent.find('input[name="agent_name"]'), {
                    role: this.params.user.agent_type,
                    user: this.params.user.id,
                    onSelect: function(item, event, ui) {
                        var id = item.id;

                        if(parent.find('li#agent'+id).length == 0) {
                            $('#agents-list').append('<li id="agent'+id+'"><i class="fa fa-trash" onClick="deleteAgent(this)"></i> <label>'+item.label+'</label><input type="hidden" name="agents[]" value="'+id+'"/></li>');
                        }
                        return false;
                    }
                });
            }
        },
        methods: {
            showAddressKeywordForm: function() {
                var searchInput = $('#keyword-autocomplete'),
                    form = $('.add-address-keyword-form');
                form.toggleClass('opened');
                form.find('input[name="keyword"]').val(searchInput.val());
                searchInput.val('');
            },
            addKeyword: function(item) {
                $('<li><i class="fa fa-times"></i> <label>' + item.value + '</label><input type="hidden" name="keywords[]" value="' + item.id + '"></li>').appendTo($('.address-keyword-list'));
                return false;
            },
            deleteKeyword: function(item) {
                $(item).closest('li').remove();
                return false;
            },
            deleteAgent: function(id) {
                $('li#agent'+id).remove();
                return false;
            },
            onImageChange: function(e) {
                var files = e.target.files || e.dataTransfer.files;
                if (!files.length) return false;
    
                $('.btn-bg-uploader').addClass('btn-loading');
    
                let file = files[0];
                if (file.type.indexOf('image/') !== 0) {
                    this.createBackgroundVideo(file, e.target);
                } else {
                    this.createImage(file, e.target);
                }
            },
			createImage: function(file, target) {
                var self = this,
                    reader = new FileReader();

                reader.onload = function(e) {
                    //$(self.$el).find('.profile-image').css('backgroundImage', 'url(' + e.target.result + ')');
                    $(target).parents('.upload-container').find('.figure-image img').attr('src', e.target.result).show();
                    $(target).parents('.upload-container').find('.background-video').hide();
                    $('.btn-bg-uploader').removeClass('btn-loading');
                };
                reader.readAsDataURL(file);
            },
            createBackgroundVideo: function(file, target) {
                var self = this,
                    reader = new FileReader();
        
                reader.onload = function(e) {
                    var video = $(target).parents('.upload-container').find('.background-video');
                    video.find('source').attr({
                        'src': e.target.result,
                        'type': file.type
                    });
                    video.show();
                    video[0].load();
                    $(target).parents('.upload-container').find('.background-image').hide();
                    $('.btn-bg-uploader').removeClass('btn-loading');
                };
                reader.readAsDataURL(file);
            },
            uploadImage: function(e) {
				var parent = $(e.target).parents('.upload-container');
                parent.find('.upload-input').click();
            },
            onSubmit: function() {
                var self = this,
                    formData = $('#reset-password-form').serialize();

                axios.post('/reset-password', formData).then(function(response) {
                    self.messageData = response.data.message ? response.data.message : '';
                    self.errorsExist = response.data.errors_exist ? response.data.errors_exist : false;
                    self.errorsList = response.data.errors ? response.data.errors : [];
                }).catch(function(error) {
                    console.log(error);
                });
            },
            overrideForAllEntries: function() {
                var self = this;
                axios.post('/override-keywords', {'user_id': this.params.user.id, _token: this.csrf }).then(function(response) {
                    self.override.messageData = response.data.message ? response.data.message : '';
                    self.override.errorsExist = response.data.errors_exist ? response.data.errors_exist : false;
                    self.override.errorsList = response.data.errors ? response.data.errors : [];
                }).catch(function(error) {
                    console.log(error);
                });
            },
			saveProjects: function() {
				var self = this,
					projectPhotosList = $('.user-projects .upload-inputs-shell input[name="project_uploads[]"]'),
					projectPhotos = [];

				tinyMCE.triggerSave();
				this.projectData.description = $('[name="project_description"]').val()

                for(var i = 0; i < projectPhotosList.length; i++) {
                    var photoId = parseInt($(projectPhotosList[i]).val());
                    if(!this.inArray(photoId, projectPhotos)) {
                        projectPhotos.push(photoId);
                    }
					console.log(projectPhotos);
                }
				if(this.projectOrder != -1) {
					this.projects[this.projectOrder] = Object.assign({}, this.projectData);
					this.projects[this.projectOrder].uploads = projectPhotos;
				} else if(this.projectData.title.length) {
					this.projects.push(Object.assign({}, this.projectData));
					this.projects[this.projects.length - 1].uploads = projectPhotos;
				}
				axios.post('/save-user-projects', {'user_id': this.params.user.id, 'projects': this.projects, _token: this.csrf }).then(function(response) {
					self.messageData = response.data.message ? response.data.message : '';
                    self.errorsExist = response.data.errors_exist ? response.data.errors_exist : false;
                    self.errorsList = response.data.errors ? response.data.errors : [];
					self.projects = response.data.saved ? response.data.saved : this.projects;
					self.cleanProjectFormData();
                }).catch(function(error) {
                    console.log(error);
                });
            },
            editProject: function(order) {
                if(this.projects[order]) {
                    this.projectData = this.projects[order];
                    this.projectOrder = order;
                }
            },
            deleteProject: function(order) {
                if(this.projects[order]) {
                    if(confirm(this.trans('Are you sure you want to delete project plan '+ this.projects[order]['title']+ '?'))) {
                        this.projects.splice(order, 1);
                        if(this.projectOrder == order) {
                            this.cleanProjectFormData();
                        }
                    }
                }
            },
            cleanProjectFormData: function() {
                this.projectData = Object.assign({}, this.projectDataDef);
                this.projectOrder = -1;
				Dropzone.forElement("#uploader-dropzone-projects").removeAllFiles();
				var dz = $("#uploader-dropzone-projects").parent(),
					inputs = dz.find('.upload-inputs-shell input');

				if(inputs.length) {
					inputs.each(function() {
						$(this).remove();
					});
				}
            },
            isImageBg: function(backgroundImage){
                if (typeof backgroundImage === 'undefined') {
                    return true;
                }
                
                if (backgroundImage.indexOf('.png') < 0 &&
                    backgroundImage.indexOf('.jpg') < 0 &&
                    backgroundImage.indexOf('.jpeg') < 0 &&
                    backgroundImage.indexOf('.gif') < 0
                ) {
                    return false;
                } else {
                    return true;
                }
            }
        }
    }
</script>

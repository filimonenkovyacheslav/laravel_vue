<template>
    <div class="user-dashboard-right dashboard-with-panel">
        <div class="board-header board-header-4">
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <div class="board-header-left">
                        <h3 class="board-title">{{ trans('Edit Home Image') }}</span></h3>
                    </div>
                    <div class="board-header-right">
                        <ol class="breadcrumb">
                            <li itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
                                <a itemprop="url" v-bind:href:href="route('home')">
                                    <span itemprop="title">{{ trans('Home') }}</span>
                                </a>
                            </li>
                            <li class="active">{{ trans('Home Image') }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="dashboard-content-area dashboard-fix">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="profile-content-area">
                        <form action="/save-home" id="form-home" method="post" enctype="multipart/form-data">
                            <div class="account-block account-profile-block">
                                <div class="row">
                                    <div class="col-md-3 col-sm-12 col-xs-12">
                                        <h4>Default Image</h4>
                                        <div class="my-avatar">
                                            <div class="houzez-home-mainimage">
                                                <div class="houzez-thumb">
                                                    <div class="figure-image" :style="params.homepage.default.main_image.type==2 ? 'display:none;' : ''">
                                                        <img class="home-image" :src="getImageUrl(params.homepage.default.main_image.name)" alt="Main Image">
                                                    </div>
                                                    <div :style="params.homepage.default.main_image.type==2 ? '' : 'display:none;'">
                                                        <video preload="auto" autoplay="true" loop="loop" muted="muted" volume="0" style="max-height: 100%; max-width: 100%;">
                                                            <source class="home-video" :src="getImageUrl(params.homepage.default.main_image.name)">
                                                            {{ trans('Your browser does not support the video tag.') }}
                                                        </video>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="profile-img-controls">
                                                <div class="houzez-upload-errors"></div>
                                                <div class="houzez-upload-container"></div>
                                            </div>
                                            <div class="logo-upload-container" style="position: relative;">
                                                <button type="button" class="btn btn-primary btn-block" @click="uploadImage">{{ trans('Update Main Image') }}</button>
                                                <input type="file" name="main_id" class="logo-upload-input" @change="onImageChange" accept="image/jpeg,.jpg,.jpeg,image/gif,.gif,image/png,.png,video/*" style="display: none;" />
                                                <input type="hidden" name="main" :value="params.homepage.default.main" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-7 col-sm-12 col-xs-12">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label>{{ trans('Title') }}</label>
                                                    <input type="text" name="title" :value="params.homepage.default.title" class="form-control"/>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label>{{ trans('Url') }}</label>
                                                    <input type="text" name="url" :value="params.homepage.default.url" class="form-control"/>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label>{{ trans('Domain') }}</label>
                                                    <select id="domain-list" class="form-control">
                                                        <option value="All">{{ trans('All domains') }}</option>
                                                        <option v-for="d, i in params.domains" :value="d.locale">{{ d.country_name }} (.{{ d.locale }})</option>
                                                    </select>
                                                </div>
                                                <button type="button" class="btn btn-primary btn-trans" @click="addDomain">{{ trans('Add Domain') }}</button>
                                                <input type="hidden" name="_token" v-model="csrf">
                                                <button class="btn btn-primary pull-right">{{ trans('Update Home Page') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-12 col-xs-12">
                                        <label>Logo</label><i class="fa fa-trash" @click="deleteLogo" style="float: right; cursor: pointer;"></i>
                                        <div class="my-avatar">
                                            <div class="houzez-home-logo">
                                                <div class="houzez-thumb">
                                                    <div class="figure-image"><img class="home-logo" :src="getImageUrl(params.homepage.default.logo_image.name)" alt="Logo"></div>
                                                </div>
                                            </div>
                                            <div class="profile-img-controls">
                                                <div class="houzez-upload-errors"></div>
                                                <div class="houzez-upload-container"></div>
                                            </div>
                                            <div class="logo-upload-container" style="position: relative;">
                                                <button type="button" class="btn btn-primary btn-block" @click="uploadImage">{{ trans('Update Logo') }}</button>
                                                <input type="file" name="logo_id" class="logo-upload-input" @change="onImageChange" accept="image/jpeg,.jpg,.jpeg,image/gif,.gif,image/png,.png" style="display: none;" />
                                                <input type="hidden" class="logo-id-input" name="logo" :value="params.homepage.default.logo" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-for="data, domain in params.homepage" v-if="domain!='default'" class="account-block account-profile-block" :id="'domain-' + domain +'-container'">
                                <div class="row">
                                    <div class="col-md-3 col-sm-12 col-xs-12">
                                        <h4 v-if="data.list">Domains:
                                            <v-select multiple :id="'domain_' + domain + '_select'" :value="getValueForSelect(data.list, params.domains, true, 'locale', 'locale')" :options="getOptions(params.domains, 'locale', 'locale')" @change="updateCurrentMultiselect('domain_' + domain + '_select')"></v-select>
                                            <input type="hidden" :name="'domain_' + domain + '_list'" :value="data.list" class="form-control" />
                                        </h4>
                                        <h4 v-else>Domain: All</h4>
                                        <div class="my-avatar">
                                            <div class="houzez-home-mainimage">
                                                <div class="houzez-thumb">
                                                    <div class="figure-image" :style="data.main_image.type==2 ? 'display:none;' : ''">
                                                        <img class="home-image" :src="getImageUrl(data.main_image.name)" alt="Main Image">
                                                    </div>
                                                    <div :style="data.main_image.type==2 ? '' : 'display:none;'">
                                                        <video preload="auto" autoplay="true" loop="loop" muted="muted" volume="0" style="max-height: 100%; max-width: 100%;">
                                                            <source class="home-video" :src="getImageUrl(data.main_image.name)">
                                                            {{ trans('Your browser does not support the video tag.') }}
                                                        </video>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="profile-img-controls">
                                                <div class="houzez-upload-errors"></div>
                                                <div class="houzez-upload-container"></div>
                                            </div>
                                            <div class="logo-upload-container" style="position: relative;">
                                                <button type="button" class="btn btn-primary btn-block button-upload" @click="uploadImage">{{ trans('Update Main Image') }}</button>
                                                <input type="file" :name="'domain_' + domain + '_main_id'" class="logo-upload-input" @change="onImageChange" accept="image/jpeg,.jpg,.jpeg,image/gif,.gif,image/png,.png,video/*" style="display: none;" />
                                                <input type="hidden" :name="'domain_' + domain + '_main'" :value="data.main" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-7 col-sm-12 col-xs-12">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label>{{ trans('Title') }}</label>
                                                    <input type="text" :name="'domain_' + domain + '_title'" :value="data.title" class="form-control"/>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label>{{ trans('Url') }}</label>
                                                    <input type="text" :name="'domain_' + domain + '_url'" :value="data.url" class="form-control"/>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <button type="button" class="btn btn-primary btn-trans pull-right" style="margin-top:30px" @click="deleteDomain">{{ trans('Delete Domain') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-12 col-xs-12">
                                        <label>Logo</label><i class="fa fa-trash" @click="deleteLogo" style="float: right; cursor: pointer;"></i>
                                        <div class="my-avatar">
                                            <div class="houzez-home-logo">
                                                <div class="houzez-thumb">
                                                    <div class="figure-image"><img class="home-logo" :src="getImageUrl(data.logo_image.name)" alt="Logo"></div>
                                                </div>
                                            </div>
                                            <div class="profile-img-controls">
                                                <div class="houzez-upload-errors"></div>
                                                <div class="houzez-upload-container"></div>
                                            </div>
                                            <div class="logo-upload-container" style="position: relative;">
                                                <button type="button" class="btn btn-primary btn-block button-upload" @click="uploadImage">{{ trans('Update Logo') }}</button>
                                                <input type="file" :name="'domain_' + domain + '_logo_id'" class="logo-upload-input" @change="onImageChange" accept="image/jpeg,.jpg,.jpeg,image/gif,.gif,image/png,.png" style="display: none;" />
                                                <input type="hidden" class="logo-id-input" :name="'domain_' + domain + '_logo'" :value="data.logo" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="account-block account-profile-block" id="default-domain" style="display: none;">
                                <div class="row">
                                    <div class="col-md-3 col-sm-12 col-xs-12">
                                        <h4>Domain</h4>
                                        <input type="hidden" :id="'default-list'" value="" class="form-control" />
                                        <div class="my-avatar">
                                            <div class="houzez-home-mainimage">
                                                <div class="houzez-thumb">
                                                    <div class="figure-image" :style="params.homepage.default.main_image.type==2 ? 'display:none;' : ''">
                                                        <img class="home-image" :src="getImageUrl(params.homepage.default.main_image.name)" alt="Main Image">
                                                    </div>
                                                    <div :style="params.homepage.default.main_image.type==2 ? '' : 'display:none;'">
                                                        <video preload="auto" autoplay="true" loop="loop" muted="muted" volume="0" style="max-height: 100%; max-width: 100%;">
                                                            <source class="home-video" :src="getImageUrl(params.homepage.default.main_image.name)">
                                                            {{ trans('Your browser does not support the video tag.') }}
                                                        </video>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="profile-img-controls">
                                                <div class="houzez-upload-errors"></div>
                                                <div class="houzez-upload-container"></div>
                                            </div>
                                            <div class="logo-upload-container" style="position: relative;">
                                                <button type="button" class="btn btn-primary btn-block button-upload">{{ trans('Update Main Image') }}</button>
                                                <input type="file" id="default-main_id" class="logo-upload-input" accept="image/jpeg,.jpg,.jpeg,image/gif,.gif,image/png,.png,video/*" style="display: none;" />
                                                <input type="hidden" id="default-main" :value="params.homepage.default.main" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-7 col-sm-12 col-xs-12">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label>{{ trans('Title') }}</label>
                                                    <input type="text" id="default-title" :value="params.homepage.default.title" class="form-control"/>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label>{{ trans('Url') }}</label>
                                                    <input type="text" id="default-url" :value="params.homepage.default.url" class="form-control"/>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <button type="button" id="default-delete" class="btn btn-primary btn-trans pull-right" style="margin-top:30px">{{ trans('Delete Domain') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-12 col-xs-12">
                                        <label>Logo</label><i class="fa fa-trash" style="float: right; cursor: pointer;"></i>
                                        <div class="my-avatar">
                                            <div class="houzez-home-logo">
                                                <div class="houzez-thumb">
                                                    <div class="figure-image"><img class="home-logo" :src="getImageUrl(params.homepage.default.logo_image.name)" alt="Logo"></div>
                                                </div>
                                            </div>
                                            <div class="profile-img-controls">
                                                <div class="houzez-upload-errors"></div>
                                                <div class="houzez-upload-container"></div>
                                            </div>
                                            <div class="logo-upload-container" style="position: relative;">
                                                <button type="button" class="btn btn-primary btn-block button-upload">{{ trans('Update Logo') }}</button>
                                                <input type="file" id="default-logo_id" class="logo-upload-input" accept="image/jpeg,.jpg,.jpeg,image/gif,.gif,image/png,.png" style="display: none;" />
                                                <input type="hidden" class="logo-id-input" id="default-logo" :value="params.homepage.default.logo" />
                                            </div>
                                        </div>
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
    export default {
        props: ['params'],
        mounted: function() {
            var self = this,
                maxGroup = 0;

            for(var gr in self.params.homepage) {
                if (gr !== 'default' && gr !== 'All' && gr > maxGroup) maxGroup = gr;
            }
            self.maxGroup = maxGroup;
        },
        methods: {
            onImageChange: function(e) {
                var files = e.target.files || e.dataTransfer.files;
                if (!files.length) return false;
                this.createImage(files[0], $(e.target).closest('div.my-avatar').find(files[0].type.indexOf('video') == -1 ? 'img.home-image, img.home-logo' : '.home-video'));
            },
            createImage: function(file, img) {
                var self = this,
                    reader = new FileReader();

                reader.onload = function(e) {
                    img.closest('.houzez-thumb').find('div').css('display', 'none');
                    img.attr('src', e.target.result);
                    img.closest('div').css('display', 'block');
                    img.closest('video').attr('src', e.target.result);
                };
                reader.readAsDataURL(file);
            },
            uploadImage: function(e) {
                $(e.target).closest('div.logo-upload-container').find('.logo-upload-input').click();
            },
            deleteDomain: function(e) {
                $(e.target).closest('div.account-block').remove();
            },
            deleteLogo: function(e) {
                var container = $(e.target).closest('div');
                container.find('.logo-upload-input').val('');
                container.find('.logo-id-input').val('');
                container.find('img.home-logo').attr('src', '/images/logo-profilepic.jpg');
            },
            addDomain: function(e) {
                var domain = $('#domain-list').val(),
                    defContainer = $('#default-domain'),
                    group = domain,
                    isAll = domain == 'All',
                    self = this,
                    newContainer;

                if (!isAll) {
                    self.maxGroup++;
                    group = self.maxGroup;
                }

                var prefix = 'domain_'+group+'_',
                    newId = 'domain-'+group+'-container';

                if($('#'+newId).length > 0) {
                    var num = 2,
                        step = 0,
                        maxCount = 20,
                        id = '';
                    do {
                        step++;
                        id = 'domain-'+domain+num+'-container';
                        if($('#'+id).length == 0) {
                            newId = id;
                            prefix = 'domain_'+domain+num+'_';
                            break;
                        }
                        num++;
                    } while(step < 20);
                }

                newContainer = defContainer.clone(true);
                newContainer.css('display', 'block');
                if (isAll) newContainer.find('h4').text('Domain: All');
                else {
                    newContainer.find('h4').text('Domain: ' + domain);
                    newContainer.find('#default-list').attr('id', prefix+'list').attr('name', prefix+'list').val(domain);
                }
                newContainer.attr('id', newId);
                newContainer.find('#default-title').attr('id', prefix+'title').attr('name', prefix+'title');
                newContainer.find('#default-url').attr('id', prefix+'url').attr('name', prefix+'url');
                newContainer.find('#default-main_id').attr('id', prefix+'main_id').attr('name', prefix+'main_id');
                newContainer.find('#default-main').attr('id', prefix+'main').attr('name', prefix+'main');
                newContainer.find('#default-logo_id').attr('id', prefix+'logo_id').attr('name', prefix+'logo_id');
                newContainer.find('#default-logo').attr('id', prefix+'logo').attr('name', prefix+'logo');
                newContainer.find('.fa-trash').on('click', function(e) {
                    self.deleteLogo(e);
                });
                newContainer.find('#default-delete').attr('id', prefix+'delete').on('click', function(e) {
                    self.deleteDomain(e);
                });
                newContainer.find('.button-upload').on('click', function(e) {
                    self.uploadImage(e);
                });
                newContainer.find('.logo-upload-input').on('change', function(e) {
                    self.onImageChange(e);
                });
                $('#form-home').append(newContainer);

                return false;
            }
        }
    }
</script>

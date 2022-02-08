<template>
    <div class="user-dashboard-right dashboard-with-panel">
        <div class="board-header board-header-4">
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <div class="board-header-left">
                        <h3 class="board-title">{{ trans('Edit Footer Image') }}</span></h3>
                    </div>
                    <div class="board-header-right">
                        <ol class="breadcrumb">
                            <li itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
                                <a itemprop="url" v-bind:href:href="route('home')">
                                    <span itemprop="title">{{ trans('Footer') }}</span>
                                </a>
                            </li>
                            <li class="active">{{ trans('Footer Image') }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="dashboard-content-area dashboard-fix">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="profile-content-area">
                        <form action="/save-footer" id="form-footer" method="post" enctype="multipart/form-data">
                            <div class="account-block account-profile-block">
                                <div class="row">
                                    <div class="col-md-3 col-sm-12 col-xs-12">
                                        <h4>Default Image</h4><i class="fa fa-trash ads-delete-image"></i>
                                        <div class="my-avatar">
                                            <div class="houzez-home-mainimage">
                                                <div class="houzez-thumb">
                                                    <div class="figure-image" :style="params.footer.default.main_image.type==2 ? 'display:none;' : ''">
                                                        <img class="home-image" :src="getImageUrl(params.footer.default.main_image.name)" alt="Footer Image">
                                                    </div>
                                                    <div :style="params.footer.default.main_image.type==2 ? '' : 'display:none;'">
                                                        <video preload="auto" autoplay="true" loop="loop" muted="muted" volume="0" style="max-height: 100%; max-width: 100%;">
                                                            <source class="home-video" :src="getImageUrl(params.footer.default.main_image.name)">
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
                                                <button type="button" class="btn btn-primary btn-block" @click="uploadImage">{{ trans('Update Footer Image') }}</button>
                                                <input type="file" name="main_id" class="logo-upload-input" @change="onImageChange" accept="image/jpeg,.jpg,.jpeg,image/gif,.gif,image/png,.png,video/*" style="display: none;" />
                                                <input type="hidden" class="input-file" name="main" :value="params.footer.default.main" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-9 col-sm-12 col-xs-12">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label>{{ trans('Url') }}</label>
                                                    <input type="text" name="url" :value="params.footer.default.url" class="form-control"/>
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
                                                <button class="btn btn-primary pull-right">{{ trans('Update Footer') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-for="data, domain in params.footer" v-if="domain!='default'" class="account-block account-profile-block" :id="'domain-' + domain +'-container'">
                                <div class="row">
                                    <div class="col-md-3 col-sm-12 col-xs-12">
                                        <h4 v-if="data.list">Domains:
                                            <v-select multiple :id="'domain_' + domain + '_select'" :value="getValueForSelect(data.list, params.domains, true, 'locale', 'locale')" :options="getOptions(params.domains, 'locale', 'locale')" @change="updateCurrentMultiselect('domain_' + domain + '_select')"></v-select>
                                            <input type="hidden" :name="'domain_' + domain + '_list'" :value="data.list" class="form-control" />
                                        </h4>
                                        <h4 v-else>Domain: All</h4> 
                                        <!--<i class="fa fa-trash ads-delete-image"></i>-->
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
                                                <button type="button" class="btn btn-primary btn-block button-upload" @click="uploadImage">{{ trans('Update Footer Image') }}</button>
                                                <input type="file" :name="'domain_' + domain + '_main_id'" class="logo-upload-input" @change="onImageChange" accept="image/jpeg,.jpg,.jpeg,image/gif,.gif,image/png,.png,video/*" style="display: none;" />
                                                <input type="hidden" class="input-file" :name="'domain_' + domain + '_main'" :value="data.main" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-9 col-sm-12 col-xs-12">
                                        <div class="row">
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
                                </div>
                            </div>

                            <div class="account-block account-profile-block" id="default-domain" style="display: none;">
                                <div class="row">
                                    <div class="col-md-3 col-sm-12 col-xs-12">
                                        <h4>Domain: </h4>
                                        <input type="hidden" :id="'default-list'" value="" class="form-control" />
                                        
                                        <div class="my-avatar">
                                            <div class="houzez-home-mainimage">
                                                <div class="houzez-thumb">
                                                    <div class="figure-image" :style="params.footer.default.main_image.type==2 ? 'display:none;' : ''">
                                                        <img class="home-image" :src="getImageUrl(params.footer.default.main_image.name)" alt="Main Image">
                                                    </div>
                                                    <div :style="params.footer.default.main_image.type==2 ? '' : 'display:none;'">
                                                        <video preload="auto" autoplay="true" loop="loop" muted="muted" volume="0" style="max-height: 100%; max-width: 100%;">
                                                            <source class="home-video" :src="getImageUrl(params.footer.default.main_image.name)">
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
                                                <button type="button" class="btn btn-primary btn-block button-upload">{{ trans('Update Footer Image') }}</button>
                                                <input type="file" id="default-main_id" class="logo-upload-input" accept="image/jpeg,.jpg,.jpeg,image/gif,.gif,image/png,.png,video/*" style="display: none;" />
                                                <input type="hidden" class="input-file" id="default-main" :value="params.footer.default.main" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-9 col-sm-12 col-xs-12">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label>{{ trans('Url') }}</label>
                                                    <input type="text" id="default-url" :value="params.footer.default.url" class="form-control"/>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <button type="button" id="default-delete" class="btn btn-primary btn-trans pull-right" style="margin-top:30px">{{ trans('Delete Domain') }}</button>
                                                </div>
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

            for(var gr in self.params.footer) {
                if (gr !== 'default' && gr !== 'All' && gr > maxGroup) maxGroup = gr;
            }
            self.maxGroup = maxGroup;
            $('#form-footer').on('click', '.ads-delete-image', function(e) {
                var container = $(e.target).closest('div');
                container.find('input.input-file').val('');
                container.find('img.home-image, source.home-video').attr('src', '/images/logo-profilepic.jpg');
                container.find('.houzez-thumb div').css('display', 'none');
                container.find('.figure-image').css('display', 'block');
            });
        },
        methods: {
            onImageChange: function(e) {
                var files = e.target.files || e.dataTransfer.files;
                if (!files.length) return false;
                this.createImage(files[0], $(e.target).closest('div.my-avatar').find(files[0].type.indexOf('video') == -1 ? 'img.home-image' : '.home-video'));
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
                    
                if ($('#'+newId).length > 0) {
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

                newContainer = defContainer.clone(true, true);
                newContainer.css('display', 'block');
                if (isAll) newContainer.find('h4').text('Domain: All');
                else {
                    newContainer.find('h4').text('Domain: ' + domain);
                    newContainer.find('#default-list').attr('id', prefix+'list').attr('name', prefix+'list').val(domain);
                }
                newContainer.attr('id', newId);
                newContainer.find('#default-url').attr('id', prefix+'url').attr('name', prefix+'url');
                newContainer.find('#default-main_id').attr('id', prefix+'main_id').attr('name', prefix+'main_id');
                newContainer.find('#default-main').attr('id', prefix+'main').attr('name', prefix+'main');
                newContainer.find('#default-delete').attr('id', prefix+'delete').on('click', function(e) {
                    self.deleteDomain(e);
                });
                newContainer.find('.button-upload').on('click', function(e) {
                    self.uploadImage(e);
                });
                newContainer.find('.logo-upload-input').on('change', function(e) {
                    self.onImageChange(e);
                });
                $('#form-footer').append(newContainer);

                return false;
            }
        }
    }
</script>

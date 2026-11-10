<template>
    <div class="user-dashboard-right dashboard-with-panel">
        <div class="board-header board-header-4">
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <div class="board-header-left">
                        <h3 v-if="params.partner.id" class="board-title">{{ trans('Edit Partner') }}</span></h3>
                        <h3 v-else class="board-title">{{ trans('New Partner') }}</span></h3>
                    </div>
                    <div class="board-header-right">
                        <ol class="breadcrumb">
                            <li itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
                                <a itemprop="url" v-bind:href:href="route('home')">
                                    <span itemprop="title">{{ trans('Home') }}</span>
                                </a>
                            </li>
                            <li v-if="params.partner.id" class="active">{{ trans('Edit Partner') }}</li>
                            <li v-else class="active">{{ trans('New Partner') }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="dashboard-content-area dashboard-fix">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="profile-content-area">
                        <form action="/save-partner" id="form-partner" method="post" enctype="multipart/form-data">
                            <div class="account-block account-profile-block">
                                <div class="row">
                                    <div class="col-md-3 col-sm-12 col-xs-12">
                                        <div class="my-avatar">
                                            <div class="houzez-partner-logo">
                                                <div class="houzez-thumb">
                                                    <div class="figure-image"><img class="partner-logo" :src="getImageUrl(params.partner.image.name)" alt="Partner Logo"></div>
                                                </div>
                                            </div>
                                            <div class="profile-img-controls">
                                                <div class="houzez-upload-errors"></div>
                                                <div class="houzez-upload-container"></div>
                                            </div>
                                            <div class="logo-upload-container" style="position: relative;">
                                                <button type="button" class="btn btn-primary btn-block" @click="uploadImage">{{ trans('Update Partner Logo') }}</button>
                                                <input type="file" name="logo_id" class="logo-upload-input" @change="onImageChange" accept="image/jpeg,.jpg,.jpeg,image/gif,.gif,image/png,.png" style="display: none;" />
                                                <input type="hidden" name="logo" :value="params.partner.logo" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-9 col-sm-12 col-xs-12">
                                        <div class="row">
                                            <div class="col-sm-6 col-xs-12">
                                                <div class="form-group">
                                                    <label>{{ trans('Title') }}</label>
                                                    <input type="text" name="title" :value="params.partner.title" class="form-control"/>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-xs-12">
                                                <div class="form-group">
                                                    <label>{{ trans('Name') }}</label>
                                                    <input type="text" name="name" :value="params.partner.name" class="form-control"/>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-xs-12">
                                                <div class="form-group">
                                                    <label>{{ trans('Url') }}</label>
                                                    <input type="text" name="url" :value="params.partner.url" class="form-control"/>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-xs-12">
                                                <div class="form-group">
                                                    <label>{{ trans('View') }}</label>
                                                    <select name="view_all" class="form-control" :value="params.partner.view_all">
                                                        <option v-for="v, k in params.view_statuses" :value="k">{{ v }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-xs-12">
                                                <div class="form-group" v-if="params.partner.id">
                                                    <label>{{ trans('Domain') }}</label>
                                                    <select id="domain-list" class="form-control">
                                                        <option v-for="d, i in params.domains" :value="d.locale">{{ d.country_name }} (.{{ d.locale }})</option>
                                                    </select>
                                                </div>
                                                <button type="button" v-if="params.partner.id" class="btn btn-primary btn-trans" @click="addDomain">{{ trans('Add Domain') }}</button>
                                            </div>
                                            <div class="col-sm-6 col-xs-12">
                                                <input type="hidden" name="id" :value="params.partner.id">
                                                <input type="hidden" name="_token" v-model="csrf">
                                                <button class="btn btn-primary pull-right" style="margin-top:30px">{{ trans('Save Partner') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-for="d, i in params.partner_domains" class="account-block account-profile-block" :id="'domain-' + d.domain +'-container'">
                                <div class="row">
                                    <div class="col-md-3 col-sm-12 col-xs-12">
                                        <h4>Domain: .{{ d.domain }}</h4>
                                        <div class="my-avatar">
                                            <div class="houzez-partner-logo">
                                                <div class="houzez-thumb">
                                                    <div class="figure-image"><img class="partner-logo" :src="getImageUrl(d.image.name)" alt="Partner Logo"></div>
                                                </div>
                                            </div>
                                            <div class="profile-img-controls">
                                                <div class="houzez-upload-errors"></div>
                                                <div class="houzez-upload-container"></div>
                                            </div>
                                            <div class="logo-upload-container" style="position: relative;">
                                                <button type="button" class="btn btn-primary btn-block button-upload" @click="uploadImage">{{ trans('Update Partner Logo') }}</button>
                                                <input type="file" :name="'domain_' + d.domain + '_logo_id'" class="logo-upload-input" @change="onImageChange" accept="image/jpeg,.jpg,.jpeg,image/gif,.gif,image/png,.png" style="display: none;" />
                                                <input type="hidden" :name="'domain_' + d.domain + '_logo'" :value="d.logo" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-9 col-sm-12 col-xs-12">
                                        <div class="row">
                                            <div class="col-sm-6 col-xs-12">
                                                <div class="form-group">
                                                    <label>{{ trans('Title') }}</label>
                                                    <input type="text" :name="'domain_' + d.domain + '_title'" :value="d.title" class="form-control"/>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-xs-12">
                                                <div class="form-group">
                                                    <label>{{ trans('Name') }}</label>
                                                    <input type="text" :name="'domain_' + d.domain + '_name'" :value="d.name" class="form-control"/>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-xs-12">
                                                <div class="form-group">
                                                    <label>{{ trans('Url') }}</label>
                                                    <input type="text" :name="'domain_' + d.domain + '_url'" :value="d.url" class="form-control"/>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-xs-12">
                                                <div class="form-group">
                                                    <button type="button" class="btn btn-primary btn-trans pull-right" style="margin-top:30px" @click="deleteDomain">{{ trans('Delete Domain') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="params.partner.id" class="account-block account-profile-block" id="default-domain" style="display: none;">
                                <div class="row">
                                    <div class="col-md-3 col-sm-12 col-xs-12">
                                        <h4>Domain</h4>
                                        <div class="my-avatar">
                                            <div class="houzez-partner-logo">
                                                <div class="houzez-thumb">
                                                    <div class="figure-image"><img class="partner-logo" :src="getImageUrl(params.partner.image.name)" alt="Partner Logo"></div>
                                                </div>
                                            </div>
                                            <div class="profile-img-controls">
                                                <div class="houzez-upload-errors"></div>
                                                <div class="houzez-upload-container"></div>
                                            </div>
                                            <div class="logo-upload-container" style="position: relative;">
                                                <button type="button" class="btn btn-primary btn-block button-upload">{{ trans('Update Partner Logo') }}</button>
                                                <input type="file" id="default-logo_id" class="logo-upload-input" accept="image/jpeg,.jpg,.jpeg,image/gif,.gif,image/png,.png" style="display: none;" />
                                                <input type="hidden" id="default-logo" :value="params.partner.logo" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-9 col-sm-12 col-xs-12">
                                        <div class="row">
                                            <div class="col-sm-6 col-xs-12">
                                                <div class="form-group">
                                                    <label>{{ trans('Title') }}</label>
                                                    <input type="text" id="default-title" :value="params.partner.title" class="form-control"/>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-xs-12">
                                                <div class="form-group">
                                                    <label>{{ trans('Name') }}</label>
                                                    <input type="text" id="default-name" :value="params.partner.name" class="form-control"/>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-xs-12">
                                                <div class="form-group">
                                                    <label>{{ trans('Url') }}</label>
                                                    <input type="text" id="default-url" :value="params.partner.url" class="form-control"/>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-xs-12">
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
            var self = this;
        },
        methods: {
            onImageChange: function(e) {
                var files = e.target.files || e.dataTransfer.files;
                if (!files.length) return false;
                this.createImage(files[0], $(e.target).closest('div.my-avatar').find('img.partner-logo'));
            },
            createImage: function(file, img) {
                var self = this,
                    reader = new FileReader();

                reader.onload = function(e) {
                    img.attr('src', e.target.result);
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
                    prefix = 'domain_' + domain + '_',
                    newId = 'domain-'+domain+'-container',
                    self = this,
                    newContainer;
                if($('#'+newId).length > 0) return false;

                newContainer = defContainer.clone(true);
                newContainer.css('display', 'block');
                newContainer.find('h4').text('Domain: .' + domain);
                newContainer.attr('id', newId);
                newContainer.find('#default-title').attr('id', prefix+'title').attr('name', prefix+'title');
                newContainer.find('#default-name').attr('id', prefix+'name').attr('name', prefix+'name');
                newContainer.find('#default-url').attr('id', prefix+'url').attr('name', prefix+'url');
                newContainer.find('#default-logo_id').attr('id', prefix+'logo_id').attr('name', prefix+'logo_id');
                newContainer.find('#default-logo').attr('id', prefix+'logo').attr('name', prefix+'logo');
                newContainer.find('#default-delete').attr('id', prefix+'delete').on('click', function(e) {
                    self.deleteDomain(e);
                });
                newContainer.find('.button-upload').on('click', function(e) {
                    self.uploadImage(e);
                });
                newContainer.find('.logo-upload-input').on('change', function(e) {
                    self.onImageChange(e);
                });
                $('#form-partner').append(newContainer);

                return false;
            }
        }
    }
</script>
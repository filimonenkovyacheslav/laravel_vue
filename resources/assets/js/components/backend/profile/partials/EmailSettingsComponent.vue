<template>
    <div class="user-dashboard-right dashboard-with-panel">
        <div class="board-header board-header-4">
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <div class="board-header-left">
                        <h3 class="board-title">{{ trans('Email Settings') }}</span></h3>
                    </div>
                    <div class="board-header-right">
                        <ol class="breadcrumb">
                            <li itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
                                <a itemprop="url" v-bind:href:href="route('home')">
                                    <span itemprop="title">{{ trans('Home') }}</span>
                                </a>
                            </li>
                            <li class="active">{{ trans('Email Settings') }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="dashboard-content-area dashboard-fix">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="profile-content-area">
                        <form action="/save-email-settings" id="emails-settings" method="POST">
                            <div class="account-block account-profile-block">
                                <div class="row">
                                    <div class="col-md-3 col-sm-12 col-xs-12">
                                        <h4>{{ trans('Settings') }}</h4>
                                    </div>
                                    <div class="col-md-9 col-sm-12 col-xs-12 email-setiings">
                                        <div class="row">
                                            <div class="col-sm-6 col-xs-12" v-for="field, index in params.fields">
                                                <html-element v-if="field.subsection=='settings'" :field="field" :index="index" :key="index" :params="params" :data-settings="index=='driver' ? 0 : 1"></html-element>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                    <hr>
                                    </div>
                                    <div class="col-md-3 col-sm-12 col-xs-12">
                                        <h4>{{ trans('Templates') }}</h4>
                                    </div>
                                    <div class="col-md-9 col-sm-12 col-xs-12">
                                        <div class="row">
                                            <div class="col-sm-6 col-xs-12" v-for="field, index in params.fields" v-if="field.subsection=='templates'">
                                                <div class="form-group">
                                                    <input type="checkbox" :name="index" :id="index" :value="1" :checked="getValue(field.value)" @change="switchCheckboxIcon" style="display: none;">
                                                    <div class="btn-group">
                                                        <label :for="index" class="btn btn-default">
                                                            <span :class="'checkbox-icon fa' + (getValue(field.value) ? ' fa-check' : '')"></span>
                                                        </label>
                                                        <label :for="index" class="btn btn-default active">{{ field.label }}</label>
                                                        <a class="btn a-button" v-bind:href="field.button">Edit</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="_token" v-model="csrf">
                                        <button class="btn btn-primary pull-right">{{ trans('Save') }}</button>
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
        props: ['params'],
        mounted: function() {
            var settingsForm = $('form[id="emails-settings"]');

            settingsForm.find('select[name="driver"]').on('change', function() {
                settingsForm.find('div.email-setiings [data-settings="1"]').find('input, select').prop('disabled', $(this).val() == '');
            }).trigger('change');
        }
    }
</script>
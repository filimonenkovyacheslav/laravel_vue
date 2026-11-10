<template>
    <div id="section-body" :data-jobEntity-id="params.entity.id">
        <div id="compare-controller" class="compare-panel">
            <div class="compare-panel-header">
                <h4 class="title"> {{ trans('Compare Listings') }} <span class="panel-btn-close pull-right"><i class="fa fa-times"></i></span></h4>
            </div>
            <div id="compare-jobEntities-basket"></div>
        </div>
        <page-breadcrumbs :title="params.entity.title" :items="items"></page-breadcrumbs>
        <jobentity-view-frontend-header :params="params"></jobentity-view-frontend-header>
        <section class="section-detail-content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12 container-contentbar">
                        <div class="detail-bar">
                            <jobentity-view-frontend-description :params="params"></jobentity-view-frontend-description>
                        </div>
                    </div>
                    <jobentity-view-frontend-sidebar :params="params" :captcha="captcha"></jobentity-view-frontend-sidebar>
                </div>
            </div>
        </section>
    </div>
</template>
<script>
/**
 * Get data via props in blade template <vue_template :props></vue_template>
 */
    export default {
        data: function() {
            return {
                items: [{
                    url: this.route('jobEntity.list.frontend'),
                    title: 'Jobs',
                }]
            }
        },
        props: ['params', 'captcha']
    }
</script>

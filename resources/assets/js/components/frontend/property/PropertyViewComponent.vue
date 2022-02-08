<template>
    <div id="section-body" :data-property-id="params.entity.id">
        <div id="compare-controller" class="compare-panel">
            <div class="compare-panel-header">
                <h4 class="title"> {{ trans('Compare Listings') }} <span class="panel-btn-close pull-right"><i class="fa fa-times"></i></span></h4>
            </div>
            <div id="compare-properties-basket"></div>
        </div>
        <page-breadcrumbs :title="params.entity.title" :items="items"></page-breadcrumbs>
        <property-view-frontend-header :params="params"></property-view-frontend-header>
        <section class="section-detail-content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12 container-contentbar">
                        <div class="detail-bar">
                            <property-view-frontend-description :params="params"></property-view-frontend-description>
                            <property-view-frontend-property-similar :params="params"></property-view-frontend-property-similar>
                        </div>
                    </div>
                    <property-view-frontend-sidebar :params="params" :captcha="captcha"></property-view-frontend-sidebar>
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
                    url: this.route('property.list.frontend', {'params': '?property_type=' + this.params.entity.property_type_view.id}),
                    title: this.params.entity.property_type_view.label
                }]
            }
        },
        props: ['params', 'captcha']
    }
</script>

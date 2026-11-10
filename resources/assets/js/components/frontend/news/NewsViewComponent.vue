<template>
    <div id="section-body" :data-property-id="params.entity.id">
        <page-breadcrumbs :title="params.entity.title" :items="items"></page-breadcrumbs>
        <news-view-frontend-header :params="params"></news-view-frontend-header>
        <section class="section-detail-content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12 container-contentbar">
                        <div class="detail-bar">
                            <news-view-frontend-post :params="params.entity"></news-view-frontend-post>
                        </div>
                    </div>
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
                    url: this.route('news.list.frontend'),
                    title: 'News'
                }]
            }
        },
        props: ['params'],
        mounted: function () {
            var self = this
            console.log(self.params.entity)
        }
    }
</script>

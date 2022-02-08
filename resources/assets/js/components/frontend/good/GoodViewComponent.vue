<template>
    <div id="section-body" :data-property-id="params.entity.id">
        <page-breadcrumbs :title="params.entity.title" :items="items"></page-breadcrumbs>
        <good-view-frontend-header :params="params"></good-view-frontend-header>
        <section class="section-detail-content">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="good-categories">
                            <span v-for="category, index in cat_list">{{ category.title }}</span>
                        </div>
                    </div>
                    <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12 container-contentbar">
                        <div class="detail-bar">
                            <good-view-frontend-description :params="params"></good-view-frontend-description>
                            <good-view-frontend-good-similar :params="params"></good-view-frontend-good-similar>
                        </div>
                    </div>
                    <good-view-frontend-sidebar :params="params" :captcha="captcha"></good-view-frontend-sidebar>
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
                    url: this.route('good.list.frontend'),
                    title: 'Marketplace'
                }],
                cat_list: []
            }
        },
        props: ['params', 'captcha'],
        mounted: function () {
            var self = this,
                categories = this.params.entity.categories;
            
            if (categories) {
                categories.forEach(function(value, index){
                    if (self.params.good_categories_front[value]) {
                        var item = {
                            title: self.params.good_categories_front[value],
                            url: self.route('good.list.frontend', {'params': '?category=' + value})
                        };
                        self.items.push(item);
                        self.cat_list.push(item);
                    }
                });
            }
        }
    }
</script>

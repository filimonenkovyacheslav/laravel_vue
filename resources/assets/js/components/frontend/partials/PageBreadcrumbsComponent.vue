<template>
    <section class="breadcrumbs-container">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <ol class="breadcrumb">
                        <li itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
                            <a itemprop="url" :href="route('home')">
                                <span itemprop="title">{{ trans('Home') }}</span>
                            </a>
                        </li>
                        <li v-for="item in breadcrumbs" itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
                            <a itemprop="url" :href="item.url">
                                <span itemprop="title">{{ trans(item.title) }}</span>
                            </a>
                        </li>
                        <li class="active">{{ title }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
    /**
     * Get data via props in blade template <vue_template :props></vue_template>
     */
    export default {
        data: function() {
            return {
                breadcrumbs: []
            }
        },
        props: ['items', 'title'],
        mounted: function() {
            this.breadcrumbs = this.items ? this.items : this.breadcrumbs;
        }
    }
</script>

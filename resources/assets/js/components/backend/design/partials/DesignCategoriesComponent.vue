<template>
    <div class="account-block form-step active">
        <div class="add-title-tab">
            <h3>{{ trans('Item Categories') }}</h3>
        </div>
        <div class="add-tab-content">
            <div class="add-tab-row">
                <div class="row">
                    <div class="col-sm-12 col-xs-12" v-if="fields">
                        <template v-for="field, index in fields.relation">
                            <html-element :field="field" :index="index" :key="index" :params="param"></html-element>
                        </template>
                        <a href="#addCategory" @click.stop="showCategoryForm()">{{ trans('Add category') }}</a>
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
                param: {
                    categories: []
                },
                fields: {},
            }
        },
        mounted: function() {
            var self = this;
            
            this.$eventHub.$on('designCategoryLoaded', function() {
                self.getDesignCategories();
            });
    
            this.$eventHub.$on('entityLoaded', function() {
                self.param.categories = self.$parent.entity.categories;
                if (typeof self.$parent.entity.fields !== 'undefined') {
                    self.fields = self.$parent.entity.fields;
                }
            });
        },
        methods: {
            showCategoryForm: function() {
                $('.add-category-form').toggleClass('opened');
            },
            getDesignCategories: function() {
                var self = this;
                axios.post('/api/designCategories', { _token: this.csrf }).then(function(response) {
                    self.$parent.entity.fields = response.data.categories;
                    self.fields = response.data.categories;
                }).catch(function(error) {
                    console.log(error);
                });
            }
        }
    }
</script>

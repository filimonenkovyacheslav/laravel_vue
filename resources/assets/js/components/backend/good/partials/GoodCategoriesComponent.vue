<template>
    <div class="account-block form-step active">
        <div class="add-title-tab">
            <h3>{{ trans('Item Categories') }} <a v-if="inArray($parent.params.user_role, ['administrator'])" href="#addCategory" class="btn btn-primary btn-small" @click.stop="showCategoryForm()">{{ trans('Add new category') }}</a></h3>
        </div>
        <div class="add-tab-content">
            <div class="add-tab-row">
                <div class="row" v-if="Object.keys(fields).length">
                    <template v-for="level, index in levels">
                        <div class="col-md-4 category-block" v-show="Object.keys(level.categories).length" :data-parents="level.parents" :data-level="index" :data-next-level="parseInt(index)+1" style="display:none;">
                            <label>{{ trans('Category level ') + (parseInt(index)+1) }}</label>
                            <div class="form-group">
                                <select multiple data-type="categories" @change="updateMultiselectForCategory" class="form-control">
                                    <template v-for="cat in level.categories">
                                        <option :value="cat.index" :data-parent="cat.parent" :selected="inArray(cat.index, params.categories)" style="display: none;">{{ cat.name }}</option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </template>
                    <input type="hidden" v-bind:name="fields.relation.categories.index" :id="fields.relation.categories.index" :value="params.categories">
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                params: {
                    categories: []
                },
                fields: {},
                levels: []
            }
        },
        mounted: function() {
            var self = this;
            
            this.$eventHub.$on('goodCategoryLoaded', function() {
                self.getGoodCategories();
            });
    
            this.$eventHub.$on('entityLoaded', function() {
                self.params.categories = self.$parent.entity.categories;
                if (typeof self.$parent.entity.fields !== 'undefined') {
                    self.fields = self.$parent.entity.fields;
                    self.levels = self.$parent.entity.fields.relation.categories.options;
                    setTimeout(function(){
                        self.checkSelectedBox();
                    }, 500);
                }
            });
        },
        methods: {
            showCategoryForm: function() {
                $('.add-category-form').toggleClass('opened');
            },
            getGoodCategories: function() {
                var self = this;
                axios.post('/api/goodCategories', { _token: this.csrf }).then(function(response) {
                    self.$parent.entity.fields = response.data.categories;
                    self.fields = response.data.categories;
                    self.levels = response.data.categories.relation.categories.options;
                }).catch(function(error) {
                    console.log(error);
                });
            },
            updateMultiselectForCategory: function() {
                var newValue = [];
    
                $('.opened').find('option:selected').each(function(){
                    newValue.push(this.value);
                });
                
                $('#categories').val(newValue);
                
                this.updateChidrenCategories();
            },
            updateChidrenCategories() {
                this.checkSelectedBox();
            },
            checkSelectedBox: function() {
                var self = this;
    
                $('.category-block').removeClass('opened').hide();
                $('.category-block').find('option').hide();
                $('.category-block').each(function(index){
                    var box = $(this),
                        select = $(this).find('select'),
                        selectedOptions = select.find('option:selected');
    
                    selectedOptions.each(function(){
                        var val = $(this).val();
    
                        self.toggleSelectBox(val);
                        self.toggleSelectOptions(val);
                        
                        $(this).show();
                    });
    
                    if (!index || selectedOptions.length) {
                        box.addClass('opened').show();
                        select.trigger('change');
                    }
    
                    if (!index) {
                        select.find('option').show();
                    }
    
                    if (!index && !selectedOptions.length) {
                        $('.category-block:not(:first)').removeClass('opened').hide();
                        $('.category-block:not(:first)').find('option').prop('selected', false).hide();
                        $('.category-block:not(:first)').find('select').trigger('change');
                        
                        return;
                    }
                });
            },
            toggleSelectBox: function(parent_id) {
                var self = this;
                $('.category-block').each(function(index){
                    var box = $(this),
                        boxParents = box.data('parents') ? box.attr('data-parents').split(',') : [];
                    
                    if (!index || self.inArray(parent_id, boxParents)) {
                        box.addClass('opened').show();
                    }
                });
            },
            toggleSelectOptions: function(parent_id) {
                var self = this;
                
                $('.category-block').each(function(index){
                    var box = $(this),
                        select = $(this).find('select');
    
                    if (!index) {
                        select.find('option').show();
                    } else {
                        select.find('option[data-parent="'+parent_id+'"]').show();
                    }
    
                    var nextLevel = box.attr('data-next-level');
                    if (!select.find('option:selected').length) {
                        $('.category-block[data-level="'+nextLevel+'"]').removeClass('opened').hide();
                        $('.category-block[data-level="'+nextLevel+'"]').find('option').prop('selected', false).hide();
                        $('.category-block[data-level="'+nextLevel+'"]').find('select').trigger('change');
                    }
                });
            }
        }
    }
</script>

<template>
    <div class="account-block form-step active">
        <div class="add-title-tab">
            <h3>{{ trans('Simple Keywords') }} <a href="#addSimpleKeyword" class="btn btn-outline-primary btn-sm" @click.stop="showSimpleKeywordForm()">{{ trans('Add new') }}</a></h3>
        </div>
        <div class="add-tab-content">
            <div class="add-tab-row">
                <div class="row">
                    <div class="col-sm-12 col-xs-12">
                        <div class="form-group">
                            <input type="text" name="keyword" id="keyword-autocomplete" class="form-control" :placeholder="trans('Search Keyword')" />
                        </div>
                        <div class="form-group">
                            <ul class="simple-keyword-list">
                                <li v-for="item, index in $parent.entity.keywords">
                                    <i class="fa fa-times" @click="deleteKeyword(item.key_id)"></i> <label>{{ item.keyword }}</label>
                                    <input type="hidden" name="keywords[]" :value="item.key_id"/>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                keywords: null
            }
        },
        mounted: function() {
            var self = this;

            this.getSimpleKeywordsAutocomplete($('#keyword-autocomplete'), {type: ''},
                function(item, event, ui) {
                    self.$parent.entity.keywords.push({key_id: item.id, keyword: item.value});
                }
            );
        },
        methods: {
            showSimpleKeywordForm: function() {
                var searchInput = $('#keyword-autocomplete'),
                    form = $('.add-simple-keyword-form');
                form.toggleClass('opened');
                form.find('input[name="keyword"]').val(searchInput.val());
                searchInput.val('');
            },
            deleteKeyword: function(id) {
                var self = this,
                    len = self.$parent.entity.keywords.length;
                for (var i = 0; i < len; i++) { 
                    if (self.$parent.entity.keywords[i].key_id == id) {
                        self.$parent.entity.keywords.splice(i, 1);
                        break;
                    }
                }
                $('li#agent'+id).remove();
                return false;
            },
        }
    }
</script>
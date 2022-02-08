<template>
    <div class="add-category-form">
        <a href="#close" class="close-btn" @click.stop="closeModal()">&times;</a>
        <h4>{{ trans('Add Category') }}</h4>
        <form id="save-wineCategory-form" action="/save-wineCategory" method="POST" class="add-frontend-wineCategory">
            <div class="submit-form-wrap">
                <div class="form-group">
                    <label for="name">{{ trans('Category Title *') }}</label>
                    <input id="name" class="form-control" name="name" required>
                </div>
                <div class="form-group">
                    <label for="slug">{{ trans('Slug') }}</label>
                    <input id="slug" class="form-control" name="slug">
                </div>
                <div class="form-group" v-if="typeof $parent.entity.fields !== 'undefined'">
                    <label for="parent_id">{{ trans('Parent category') }}</label>
                    <select id="parent_id" class="form-control" name="parent_id">
                        <option value="0">{{ trans('Choose parent category') }}</option>
                        <template v-for="level in $parent.entity.fields.relation.categories.options">
                            <option v-for="cat in level.categories" :value="cat.index"><span v-html="cat.name"></span></option>
                        </template>
                    </select>
                </div>
                <input type="hidden" name="_token" v-model="csrf">
                <button type="button" class="btn btn-primary btn-block" @click="onSubmitWineCategory()">{{ trans('Save') }}</button>
            </div>
        </form>
    </div>
</template>

<script>
	export default {
		name: "AddWineCategoryFormComponent",
        mounted: function() {
            var self = this;
        },
        methods: {
            onSubmitWineCategory: function() {
                var self = this,
                    form = $('#save-wineCategory-form'),
                    formData = form.serialize();
    
                axios.post('/save-wineCategory/back', formData).then(function(response) {
                    self.$parent.entity.categories.push(response.data.wine_category_id);
                    form.find('#name,#slug').val('');
                    form.find('#parent_id').val(0).trigger('change');
                    self.closeModal();
                    self.$eventHub.$emit('wineCategoryLoaded');
                }).catch(function(error) {
                    console.log(error);
                });
                
                return false;
            },
            closeModal: function() {
                $('.add-category-form').toggleClass('opened');
            }
        }
	}
</script>

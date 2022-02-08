<template>
    <div class="dashboard-content-area dashboard-fix">
        <div class="container">
            <div class="row dashboard-inner-main">
                <div class="col-lg-10 col-md-9 col-sm-12 dashboard-inner-left">
                    <form id="save-propertyCategory-form" action="/save-propertyCategory" method="POST" class="add-frontend-productCategory">
                        <div class="submit-form-wrap">
                            <html-element :field="params.fields.slug" :index="params.fields.slug.index" :params="params"></html-element>
                            <html-element :field="params.fields.name" :index="params.fields.name.index" :params="params"></html-element>
                            <div class="form-group">
                              <label for="parent_id">{{ trans('Parent category') }}</label>
                              <select id="parent_id" class="form-control" name="parent_id" :value="propertyCategory.parent_id">
                                <option value="0">{{ trans('Choose parent category') }}</option>
                                <option v-if="id != propertyCategory.property_category_id" v-for="name, id in params.property_categories_admin" :value="id"> {{ name }} </option>
                              </select>
                            </div>
                            <input type="hidden" name="_token" v-model="csrf">
                            <html-element :field="params.fields.property_category_id" :index="params.fields.property_category_id.index" :params="params"></html-element>
                        </div>
                    </form>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-12 dashboard-inner-right">
                    <div class="dashboard-sidebar">
                        <div class="dashboard-sidebar-inner">
                            <button type="submit" class="btn btn-default btn-block" onclick="event.preventDefault(); document.getElementById('save-propertyCategory-form').submit();">{{ trans('Save') }}</button>
                            <a v-bind:href="route('propertyCategory.edit.admin')" class="btn btn-primary btn-block">{{ trans('Add New') }}</a>
                            <a v-bind:href="route('user.profile.propertyCategories')" class="btn btn-default btn-block">{{ trans('Categories') }}</a>
                        </div>
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
                  propertyCategory: {
                      property_category_id: (this.params.propertyCategory && this.params.propertyCategory.property_category_id) ? this.params.propertyCategory.property_category_id : '',
                      parent_id: (this.params.propertyCategory && this.params.propertyCategory.parent_id) ? this.params.propertyCategory.parent_id : 0,
                  },
              }
        },
        props: ['params'],
        mounted: function() {
            var self = this;
          }
    }
</script>
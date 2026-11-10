<template>
    <div class="dashboard-content-area dashboard-fix">
        <div class="container">
            <div class="row dashboard-inner-main">
                <div class="col-lg-10 col-md-9 col-sm-12 dashboard-inner-left">
                    <form id="save-jobCategory-form" action="/save-productCategory" method="POST" class="add-frontend-productCategory">
                        <div class="submit-form-wrap">
                            <html-element :field="params.fields.slug" :index="params.fields.slug.index" :params="params"></html-element>
                            <html-element :field="params.fields.name" :index="params.fields.name.index" :params="params"></html-element>
                            <div class="form-group">
                              <label for="parent_id">{{ trans('Parent category') }}</label>
                              <select id="parent_id" class="form-control" name="parent_id" :value="productCategory.parent_id">
                                <option value="0">{{ trans('Choose parent category') }}</option>
                                <option v-if="id != productCategory.product_category_id" v-for="name, id in params.product_categories_admin" :value="id"> {{ name }} </option>
                              </select>
                            </div>
                            <input type="hidden" name="_token" v-model="csrf">
                            <html-element :field="params.fields.product_category_id" :index="params.fields.product_category_id.index" :params="params"></html-element>
                        </div>
                    </form>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-12 dashboard-inner-right">
                    <div class="dashboard-sidebar">
                        <div class="dashboard-sidebar-inner">
                            <button type="submit" class="btn btn-default btn-block" onclick="event.preventDefault(); document.getElementById('save-jobCategory-form').submit();">{{ trans('Save') }}</button>
                            <a v-bind:href="route('productCategory.edit.admin')" class="btn btn-primary btn-block">{{ trans('Add New') }}</a>
                            <a v-bind:href="route('user.profile.productCategories')" class="btn btn-default btn-block">{{ trans('Categories') }}</a>
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
                  productCategory: {
                      product_category_id: (this.params.productCategory && this.params.productCategory.product_category_id) ? this.params.productCategory.product_category_id : '',
                      parent_id: (this.params.productCategory && this.params.productCategory.parent_id) ? this.params.productCategory.parent_id : 0,
                  },
              }
        },
        props: ['params'],
        mounted: function() {
            var self = this;
          }
    }
</script>

<template>
    <div class="dashboard-content-area dashboard-fix">
        <div class="container">
            <div class="row dashboard-inner-main">
                <div class="col-lg-10 col-md-9 col-sm-12 dashboard-inner-left">
                    <form id="save-jobCategory-form" action="/save-goodCategory" method="POST" class="add-frontend-goodCategory">
                        <div class="submit-form-wrap">
                            <html-element :field="params.fields.slug" :index="params.fields.slug.index" :params="params"></html-element>
                            <html-element :field="params.fields.name" :index="params.fields.name.index" :params="params"></html-element>
                            <div class="form-group">
                              <label for="parent_id">{{ trans('Parent category') }}</label>
                              <select id="parent_id" class="form-control" name="parent_id" :value="goodCategory.parent_id">
                                <option value="0">{{ trans('Choose parent category') }}</option>
                                <option v-if="id != goodCategory.good_category_id" v-for="name, id in params.good_categories_admin" :value="id"> {{ name }} </option>
                              </select>
                            </div>
                            <input type="hidden" name="_token" v-model="csrf">
                            <html-element :field="params.fields.good_category_id" :index="params.fields.good_category_id.index" :params="params"></html-element>
                        </div>
                    </form>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-12 dashboard-inner-right">
                    <div class="dashboard-sidebar">
                        <div class="dashboard-sidebar-inner">
                            <button type="submit" class="btn btn-default btn-block" onclick="event.preventDefault(); document.getElementById('save-jobCategory-form').submit();">{{ trans('Save') }}</button>
                            <a v-bind:href="route('goodCategory.edit.admin')" class="btn btn-primary btn-block">{{ trans('Add New') }}</a>
                            <a v-bind:href="route('user.profile.goodCategories')" class="btn btn-default btn-block">{{ trans('Categories') }}</a>
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
                  goodCategory: {
                      good_category_id: (this.params.goodCategory && this.params.goodCategory.good_category_id) ? this.params.goodCategory.good_category_id : '',
                      parent_id: (this.params.goodCategory && this.params.goodCategory.parent_id) ? this.params.goodCategory.parent_id : 0,
                  },
              }
        },
        props: ['params'],
        mounted: function() {
            var self = this;
          }
    }
</script>

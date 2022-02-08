<template>
    <select class="sort-select form-control" :value="urlGetParam('order_by')">
        <option value="">{{ trans('Default Order') }}</option>
		<option v-if="route_name && inArray(route_name, ['product.list.frontend', 'seller.view.frontend'])" value="a_price">{{ trans('Price Low to High') }}</option>
        <option v-if="route_name && inArray(route_name, ['wine.list.frontend', 'wineseller.view.frontend'])" value="a_price">{{ trans('Price Low to High') }}</option>
        <option v-if="route_name && inArray(route_name, ['furniture.list.frontend', 'furnitureseller.view.frontend'])" value="a_price">{{ trans('Price Low to High') }}</option>
        <option v-if="!route_name || !inArray(route_name, ['impression.list.frontend','design.list.frontend','jobEntity.list.frontend','brand.view.frontend','news.list.frontend'])" value="d_price">{{ trans('Price High to Low') }}</option>
        <option v-if="route_name && route_name=='brand.list.frontend'" value="title">{{ trans('Brand Name A-Z') }}</option>
        <option v-if="route_name && route_name=='news.list.frontend'" value="a_title">{{ trans('Name A-Z') }}</option>
        <option v-if="route_name && route_name=='news.list.frontend'" value="d_title">{{ trans('Name Z-A') }}</option>
        <option v-if="route_name && route_name=='good.list.frontend'" value="a_title">{{ trans('Name A-Z') }}</option>
        <option v-if="route_name && route_name=='good.list.frontend'" value="d_title">{{ trans('Name Z-A') }}</option>
        <option v-if="route_name && route_name=='design.list.frontend'" value="a_date">{{ trans('Date Old to New') }}</option>
        <option v-if="route_name && route_name=='design.list.frontend'" value="d_date">{{ trans('Date New to Old') }}</option>
    </select>
</template>

<script>
    /**
     * Get data via props in blade template <vue_template :props></vue_template>
     */
    export default {
        props: ['route_name'],
        mounted: function() {
            var self = this;

            $('.sort-select').on('change', function() {
                var addParams = {
                    'page': 0
                };
                window.location = self.modifyUrl('order_by', $(this).val(), addParams);
            });
        }
    }
</script>

<template>
    <div>
    <div :class="'label-wrap ' + className">
        <span :class="'label label-product label-default label-'+entityData.label_view.color" v-if="entityData.label">
            {{ entityData.label_view.label }}
        </span>
        <span class="label-status label label-default" v-if="entityData.product_status_view && entityData.product_status_view.label && entityData.product_status_view.slug">
            <a :href="route('product.list.frontend') + '?product_status=' + entityData.product_status">{{ entityData.product_status_view.label }}</a>
        </span>
        <span class="label label-default" v-if="entityData.product_type_view && entityData.product_type_view.label">
            <a :href="route('product.list.frontend') + '?product_type=' + entityData.product_type">{{ entityData.product_type_view.label }}</a>
        </span>
    </div>
    </div>
</template>

<script>
    /**
     * Get data via props in blade template <vue_template :props></vue_template>
     */
    export default {
        props: ['entityData', 'className']
    }
</script>

<template>
    <div>
    <div :class="'label-wrap ' + className">
        <span :class="'label label-product label-default label-'+entityData.label_view.color" v-if="entityData.label">
            {{ entityData.label_view.label }}
        </span>
        <span class="label-status label label-default" v-if="entityData.design_status_view && entityData.design_status_view.label && entityData.design_status_view.slug">
            <a :href="route('design.list.frontend') + '?product_status=' + entityData.design_status">{{ entityData.design_status_view.label }}</a>
        </span>
        <span class="label label-default" v-if="entityData.design_type_view && entityData.design_type_view.label">
            <a :href="route('design.list.frontend') + '?product_type=' + entityData.design_type">{{ entityData.design_type_view.label }}</a>
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

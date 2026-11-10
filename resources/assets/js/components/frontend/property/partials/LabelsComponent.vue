<template>
    <div>
    <div :class="'label-wrap ' + className">
        <span :class="'label label-default label-'+entityData.label_view.color" v-if="entityData.label">
            {{ entityData.label_view.label }}
        </span>
        <span class="label-status label label-default" v-if="entityData.property_status_view && entityData.property_status_view.label && entityData.property_status_view.slug">
            <a :href="route('property.list.frontend') + '?property_status=' + entityData.property_status">{{ entityData.property_status_view.label }}</a>
        </span>
        <span class="label label-default" v-if="entityData.property_type_view && entityData.property_type_view.label">
            <a :href="route('property.list.frontend') + '?property_type=' + entityData.property_type">{{ entityData.property_type_view.label }}</a>
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
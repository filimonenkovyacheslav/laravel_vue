<template>
    <div>
    <div :class="'label-wrap ' + className">
        <span :class="'label label-furniture label-default label-'+entityData.label_view.color" v-if="entityData.label">
            {{ entityData.label_view.label }}
        </span>
        <span class="label-status label label-default" v-if="entityData.furniture_status_view && entityData.furniture_status_view.label && entityData.furniture_status_view.slug">
            <a :href="route('furniture.list.frontend') + '?furniture_status=' + entityData.furniture_status">{{ entityData.furniture_status_view.label }}</a>
        </span>
        <span class="label label-default" v-if="entityData.furniture_type_view && entityData.furniture_type_view.label">
            <a :href="route('furniture.list.frontend') + '?furniture_type=' + entityData.furniture_type">{{ entityData.furniture_type_view.label }}</a>
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

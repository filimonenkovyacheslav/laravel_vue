<template>
    <div>
    <div :class="'label-wrap ' + className">
        <span :class="'label label-wine label-default label-'+entityData.label_view.color" v-if="entityData.label">
            {{ entityData.label_view.label }}
        </span>
        <span class="label-status label label-default" v-if="entityData.wine_status_view && entityData.wine_status_view.label && entityData.wine_status_view.slug">
            <a :href="route('wine.list.frontend') + '?wine_status=' + entityData.wine_status">{{ entityData.wine_status_view.label }}</a>
        </span>
        <span class="label label-default" v-if="entityData.wine_type_view && entityData.wine_type_view.label">
            <a :href="route('wine.list.frontend') + '?wine_type=' + entityData.wine_type">{{ entityData.wine_type_view.label }}</a>
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

<template>
    <div>
    <div :class="'label-wrap ' + className">
        <span :class="'label label-good label-default label-'+entityData.label_view.color" v-if="entityData.label">
            {{ entityData.label_view.label }}
        </span>
        <span class="label-status label label-default" v-if="entityData.good_status_view && entityData.good_status_view.label && entityData.good_status_view.slug">
            <a :href="route('good.list.frontend') + '?good_status=' + entityData.good_status">{{ entityData.good_status_view.label }}</a>
        </span>
        <span class="label label-default" v-if="entityData.good_type_view && entityData.good_type_view.label">
            <a :href="route('good.list.frontend') + '?good_type=' + entityData.good_type">{{ entityData.good_type_view.label }}</a>
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

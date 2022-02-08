<template>
    <div>
    <div :class="'label-wrap ' + className">
        <span :class="'label label-news label-default label-'+entityData.label_view.color" v-if="entityData.label">
            {{ entityData.label_view.label }}
        </span>
        <span class="label-status label label-default" v-if="entityData.news_status_view && entityData.news_status_view.label && entityData.news_status_view.slug">
            <a :href="route('news.list.frontend') + '?news_status=' + entityData.news_status">{{ entityData.news_status_view.label }}</a>
        </span>
        <span class="label label-default" v-if="entityData.news_type_view && entityData.news_type_view.label">
            <a :href="route('news.list.frontend') + '?news_type=' + entityData.news_type">{{ entityData.news_type_view.label }}</a>
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

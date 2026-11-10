<template>
    <div class="property-similar" v-if="params.entities_similar && params.entities_similar.length">
        <div class="detail-title">
            <h2 class="title-left">{{ trans('Similar Properties') }}</h2>
        </div>
        <div class="property-listing list-view">
            <div class="row">
                <property-list-frontend-list-item :entities="params.entities_similar" :params="params"></property-list-frontend-list-item>
            </div>
        </div>
        <hr>
    </div>
</template>

<script>
    /**
     * Get data via props in blade template <vue_template :props></vue_template>
     */
    export default {
        props: ['params']
    }
</script>
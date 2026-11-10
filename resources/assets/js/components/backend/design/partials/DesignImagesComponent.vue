<template>
    <div class="account-block form-step active">
        <div class="add-title-tab">
            <h3>{{ trans('Item media') }}</h3>
            <div class="add-expand"></div>
        </div>
        <div class="add-tab-content">
            <div class="add-tab-row">
                <file-uploader :fieldData="{'index': 'photos[]'}" :entityData="$parent.entity" :entityType="'design'"></file-uploader>
            </div>
        </div>
    </div>
</template>

<script>
    /**
     * Get data via props in blade template <vue_template :props></vue_template>
     */
    export default {
    }
</script>

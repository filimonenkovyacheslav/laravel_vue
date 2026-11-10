<template>
    <div class="container site-messages">
        <div class="row">
            <div class="col-md-12 col-xs-12">
                <div v-if="message" :class="['alert', isError ? 'alert-danger' : (isProcessing ? 'alert-warning' : 'alert-success')]" role="alert">{{ message }} <i v-if="isProcessing" class="fa fa-spinner fa-spin"></i></div>
                <div v-for="error in errors" class="alert alert-danger" role="alert"><span v-for="err in error">{{ err }}</span></div>
            </div>
        </div>
    </div>
</template>

<script>
    /**
     * Get data via props in blade template <vue_template :props></vue_template>
     */
    export default {
        props: ['message','errors', 'isError', 'isProcessing']
    }
</script>
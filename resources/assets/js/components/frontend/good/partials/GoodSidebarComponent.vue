<template>
    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 col-md-offset-0 col-sm-offset-3 container-sidebar ">
        <aside id="sidebar" class="sidebar-white">
            <good-contacts-widget :params="params" :captcha="captcha"></good-contacts-widget>
        </aside>
    </div>
</template>

<script>
    /**
     * Get data via props in blade template <vue_template :props></vue_template>
     */
    export default {
        props: ['params', 'captcha'],
    }
</script>

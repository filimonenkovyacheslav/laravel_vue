<template>
    <div :class="className">
        <a class="btn-facebook" v-if="user.facebook" :href="user.facebook" target="_blank"><i class="fa fa-facebook-square"></i></a>
        <a class="btn-twitter" v-if="user.twitter" :href="user.twitter" target="_blank"><i class="fa fa-twitter-square"></i></a>
        <a class="btn-linkedin" v-if="user.linkedin" :href="user.linkedin" target="_blank"><i class="fa fa-linkedin-square"></i></a>
        <a class="btn-instagram" v-if="user.instagram" :href="user.instagram" target="_blank"><i class="fa fa-instagram"></i></a>
        <a class="btn-google-plus" v-if="user.google_plus" :href="user.google_plus" target="_blank"><i class="fa fa-google-plus-square"></i></a>
        <a class="btn-youtube" v-if="user.youtube" :href="user.youtube" target="_blank"><i class="fa fa-youtube-square"></i></a>
        <a class="btn-pinterest" v-if="user.pinterest" :href="user.pinterest" target="_blank"><i class="fa fa-pinterest-square"></i></a>
        <a class="btn-vimeo" v-if="user.vimeo" :href="user.vimeo" target="_blank"><i class="fa fa-vimeo-square"></i></a>
        <a class="btn-skype" v-if="user.skype" :href="user.skype" target="_blank"><i class="fa fa-skype"></i></a>
    </div>
</template>

<script>
    /**
     * Get data via props in blade template <vue_template :props></vue_template>
     */
    export default {
        props: ['user', 'className']
    }
</script>
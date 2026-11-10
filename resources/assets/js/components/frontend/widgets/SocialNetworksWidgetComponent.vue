<template>
    <ul class="actions" style="display: none; float: right;">
        <li class="share-btn">
            <user-social-networks-list :user="user" :className="'share_tooltip fade'"></user-social-networks-list>
            <span title="" data-placement="top" data-toggle="tooltip" data-original-title="share">
                <i class="fa fa-share-alt"></i>
            </span>
        </li>
    </ul>
</template>

<script>
    /**
     * Get data via props in blade template <vue_template :props></vue_template>
     */
    export default {
        props: ['user']
    }
</script>
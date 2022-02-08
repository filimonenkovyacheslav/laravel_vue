<template>
    <ul :class="className">
        <li v-if="user.phone"><span>OFFICE:</span> <a :href="user.phone.substr(-1) == '*' ? route('login') : 'tel:' + user.phone">{{ user.phone }}</a></li>
        <li v-if="user.mobile"><span>MOBILE:</span> <a :href="user.mobile.substr(-1) == '*' ? route('login') : 'tel:' + user.mobile">{{ user.mobile }}</a></li>
        <li v-if="user.fax_number"><span>FAX:</span> <a :href="user.fax_number.substr(-1) == '*' ? route('login') : 'tel:' + user.fax_number">{{ user.fax_number }}</a></li>
        <li v-if="user.tax_number"><span>TAX:</span> <a :href="user.tax_number.substr(-1) == '*' ? route('login') : 'tel:' + user.tax_number">{{ user.tax_number }}</a></li>
        <!--<li v-if="user.email" class="email"><span>Email:</span> <a :href="'mailto:' + user.email">{{ user.email }}</a></li>-->
        <li v-if="user.website" class="website"><a :href="user.website" target="_blank"><span>{{ trans('Website') }}</span></a></li>
    </ul>
</template>

<script>
    /**
     * Get data via props in blade template <vue_template :props></vue_template>
     */
    export default {
        props: ['user', 'className']
    }
</script>
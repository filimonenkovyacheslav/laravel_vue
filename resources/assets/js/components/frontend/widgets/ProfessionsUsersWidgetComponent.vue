<template>
    <div class="widget widget_glt_widget">
        <div class="widget-top">
            <h3 class="widget-title">{{ trans('Professionals') }}</h3>
        </div>
        <div class="professions-list">
            <ul v-for="item, key in professions">
                <li v-if><a :href="route('professional.list.frontend', {'params': item.slug})">{{ item.name }} <span v-if="item.users_count">({{ item.users_count }})</span></a>
                  <ul v-if="item.children != ''">
                    <li v-for="item2, key2, in item.children"><a :href="route('professional.list.frontend', {'params': item2.slug})">{{ item2.name }} <span v-if="item2.users_count">({{ item2.users_count }})</span> </a></li>
                  </ul>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
    /**
     * Get data via props in blade template <vue_template :props></vue_template>
     */
    export default {
        props: ['professions']
    }
</script>

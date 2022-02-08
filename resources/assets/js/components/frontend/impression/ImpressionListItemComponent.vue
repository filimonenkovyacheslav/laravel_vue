<template>
    <div class="row">
        <div class="item-wrap infobox_trigger" v-for="prop in entities" :data-upload-id="prop.upload_id">
            <div class="property-item table-list">
                <div class="table-cell">
                    <impession-popup-view :property="prop"></impession-popup-view>
                </div>
                <div class="item-body table-cell">
                    <div class="body-left table-cell">
                        <div class="info-row">
                            <div class="label-wrap hide-on-grid"></div>
                            <h2 class="property-title">
                                <span v-if="prop.status==5">{{ prop.address_view }}</span>
                                <a v-else v-bind:href="route('property.view.frontend', {'slug': prop.slug})">{{ prop.address_view }}</a>
                            </h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


<script>
    /**
     * Get data via props in blade template <vue_template :props></vue_template>
     */
    export default {
        props: ['params', 'entities'],
         
}

</script>
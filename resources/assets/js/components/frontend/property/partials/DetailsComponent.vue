<template>
    <div :class="className ? 'info-row amenities ' + className : 'info-row amenities'">
        <p>
            <span v-if="entityData.bedrooms"><strong>{{ trans('Beds') }}:</strong> {{ entityData.bedrooms }}</span>
            <span v-if="entityData.bathrooms"><strong>{{ trans('Baths') }}:</strong> {{ entityData.bathrooms }}</span>
        </p>
        <p>
            <span v-if="entityData.property_area_view.sqmeter.value || entityData.property_area_view.sqfeet.value"><strong>{{ trans('Area') }}:</strong>
                <template v-if="entityData.property_area_view.sqmeter.value">{{ entityData.property_area_view.sqmeter.value }} {{ entityData.property_area_view.sqmeter.symbol }}</template><template v-if="entityData.property_area_view.sqfeet.value"> / </template><template v-if="entityData.property_area_view.sqfeet.value">{{ entityData.property_area_view.sqfeet.value }} {{ entityData.property_area_view.sqfeet.symbol }}</template>
            </span>
        </p>
        <p>
            <span><template v-if="entityData.property_type_view.label">{{ entityData.property_type_view.label }}<template v-if="entityData.property_type_view.label && entityData.property_status_view.label">, </template></template><template v-if="entityData.property_status_view.label">{{ entityData.property_status_view.label }}</template></span>
        </p>
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
<template>
    <div :class="'price ' + className">
        <template v-if="entityData.price_hidden || !entityData.price_view">
            <span class="item-price-container">{{ trans('Price upon request') }}</span>
        </template>
        <template v-else-if="type && type == 'short'">
            <span class="item-price-container item-price-short">
                <span class="item-price">{{ entityData.price_view.local.price }}</span>
            </span>
        </template>
        <template v-else>
            <template v-if="entityData.price_default > 1">
                <span class="item-price-container">
                    <span class="item-price">{{ entityData.price_view.local.price }}</span>
                    <span class="item-price-name">{{ entityData.price_view.local.name }}</span>
                </span>
            </template>
            <span v-else class="item-price-container">
                <span class="item-price">POA</span>
            </span>
        </template>
    </div>
</template>

<script>
    /**
     * Get data via props in blade template <vue_template :props></vue_template>
     */
    export default {
        props: ['entityData', 'className', 'type']
    }
</script>

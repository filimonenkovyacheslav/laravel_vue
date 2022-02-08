<template>
    <div :class="'price ' + className">
        <template v-if="entityData.price_hidden">
            <span class="item-price-container">{{ trans('Price upon request') }}</span>
        </template>
        <template v-else>
            <span v-if="entityData.price_default > 1">
                <span class="item-price-container">
                    <span class="item-price">
                        <span v-if="entityData.price_before">{{ entityData.price_before }}</span>{{ entityData.price_view.local.price }}<span v-if="entityData.price_after">{{ entityData.price_after }}</span>
                    </span>
                    <span class="item-price-name">{{ entityData.price_view.local.name }}</span>
                </span>
                <span class="item-price-container">
                    <span class="item-price">
                        <span v-if="entityData.price_before">{{ entityData.price_before }}</span>{{ entityData.price_view.default.price }}<span v-if="entityData.price_after">{{ entityData.price_after }}</span>
                    </span>
                    <span class="item-price-name">{{ entityData.price_view.default.name }}</span>
                </span>
            </span>
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
        props: ['entityData', 'className']
    }
</script>

<template>
    <ul :class="['category-list', 'category-list-children', inArray(item.product_category_id, params.selected_parents) ? 'open' : '']" :data-level="level" :data-parent="item.product_category_id" v-if="show > 0">
        <template v-for="child in item.children">
            <li v-if="child.total_products" :class="['category-list-item', urlGetParam('category') == child.product_category_id ? 'active' : '']">
                <a :href="modifyUrl('category', child.product_category_id)" class="category-list-link">{{ child.name }}</a>
                <template v-if="child.children && Object.keys(child.children).length">
                    <i :class="['fa', 'fa-chevron-right', inArray(child.product_category_id, params.selected_parents) ? 'active' : '']" v-on:click.stop="showFilterChildrenCats(child.product_category_id, $event)"></i>
                    <product-category-recursive :entity_item="child" :level_item="level+1" :params="params"></product-category-recursive>
                </template>
            </li>
        </template>
    </ul>
</template>

<script>
	export default {
        data: function() {
            return {
                item: {},
                level: 1,
                show: 0
            }
        },
        props: ['entity_item', 'level_item', 'params'],
        mounted: function() {
            this.item = typeof this.entity_item !== 'undefined' ? this.entity_item : this.item;
            this.level = typeof this.level_item !== 'undefined' ? this.level_item : this.level;
            this.show = Object.keys(this.item.children).length;
        }
	}
</script>

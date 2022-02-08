<template>
    <div>
        <div class="child-category-row" v-for="child, childIndex in item.children">
            <div class="item-wrap">
                <div class="media my-property">
                    <div class="media-body align-self-center">
                        <div class="my-description">
                            <h4><i class="fa fa-level-up fa-flip-horizontal child-category-icon" aria-hidden="true"></i> {{ child.name }}</h4>
                            <div class="status">
                                <p>
                                    <span><strong>Items:</strong> {{ child.total_designs }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="my-actions">
                            <div class="btn-group">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ trans('Actions') }} <i class="fa fa-angle-down"></i></button>
                                <ul class="dropdown-menu actions-dropdown">
                                    <li><a v-bind:href="route('designCategory.edit.admin', {'id': child.design_category_id})"><i class="fa fa-edit"></i> {{ trans('Edit') }}</a></li>
                                    <li><a v-bind:href="route('designCategory.delete.admin', {'id': child.design_category_id})" class="delete-jobCategory"><i class="fa fa-close"></i> {{ trans('Delete') }}</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <template v-if="child.children">
                <user-profile-design-category-child :entity_item="child"></user-profile-design-category-child>
            </template>
        </div>
    </div>
</template>

<script>
	export default {
        data: function() {
		    return {
		        item: {}
            }
        },
        props: ['entity_item'],
        mounted: function() {
            this.item = typeof this.entity_item !== 'undefined' ? this.entity_item : this.item;
        }
	}
</script>

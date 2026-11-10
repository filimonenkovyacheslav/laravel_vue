<template>
    <div>
        <div class="child-category-row" v-for="child, childIndex in item.children">
            <div class="item-wrap">
                <div class="media my-property">
                    <div class="media-body align-self-center">
                        <div class="my-description">
                            <h4><i class="fa fa-level-up fa-flip-horizontal child-category-icon" aria-hidden="true"></i> {{ child.name }}</h4>
                        </div>
                        <div class="my-actions">
                            <div class="btn-group">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ trans('Actions') }} <i class="fa fa-angle-down"></i></button>
                                <ul class="dropdown-menu actions-dropdown">
                                    <li><a v-bind:href="route('artCategory.edit.admin', {'id': child.art_category_id})"><i class="fa fa-edit"></i> {{ trans('Edit') }}</a></li>
                                    <li><a v-bind:href="route('artCategory.delete.admin', {'id': child.art_category_id})" class="delete-jobCategory"><i class="fa fa-close"></i> {{ trans('Delete') }}</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <template v-if="child.children">
                <user-profile-art-category-child :entity_item="child"></user-profile-art-category-child>
            </template>
        </div>
    </div>
</template>

<script>
	export default {
		name: "ArtCategoryListChildComponent",
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

<template>
    <div>
        <div class="child-category-row" v-for="child, childIndex in item.children">
            <div class="item-wrap">
                <div class="media my-property">
                    <input type="checkbox" :value="child.wine_category_id" class="bulkEditItems" id="bulkEditItems" style="margin-top:47px"/>
                    <div class="media-body align-self-center">
                        <div class="my-description">
                            <h4><i class="fa fa-level-up fa-flip-horizontal child-category-icon" aria-hidden="true"></i> {{ child.name }}</h4>
                            <div class="status">
                                <div class="status">
                                    <p>
                                        <span><strong>{{ trans('ID') }}:</strong> #{{ child.wine_category_id }}</span>
                                    </p>
                                    <p>
                                        <span><strong>{{ trans('Status') }}:</strong> <template v-if="child.status">{{ trans('Published') }}</template><template v-else>{{ trans('Unpublished') }}</template></span>
                                    </p>
                                    <p>
                                        <span><strong>{{ trans('Wines') }}:</strong> {{ child.total_wines }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="my-actions">
                            <div class="btn-group">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ trans('Actions') }} <i class="fa fa-angle-down"></i></button>
                                <ul class="dropdown-menu actions-dropdown">
                                    <li><a v-bind:href="route('wineCategory.edit.admin', {'id': child.wine_category_id})"><i class="fa fa-edit"></i> {{ trans('Edit') }}</a></li>
                                    <li v-if="inArray(params.user_role, ['administrator'])">
                                        <a v-if="!child.status" v-bind:href="route('wineCategory.status.admin', {'id': child.wine_category_id, 'status': 1})">
                                            <i class="fa fa-check"></i> {{ trans('Publish') }}
                                        </a>
                                        <a v-else v-bind:href="route('wineCategory.status.admin', {'id': child.wine_category_id, 'status': 0})">
                                            <i class="fa fa-minus"></i> {{ trans('Unpublish') }}
                                        </a>
                                    </li>
                                    <li><a v-bind:href="route('wineCategory.delete.admin', {'id': child.wine_category_id})" class="delete-jobCategory"><i class="fa fa-close"></i> {{ trans('Delete') }}</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <template v-if="child.children">
                <user-profile-wine-category-child :entity_item="child" :params="params"></user-profile-wine-category-child>
            </template>
        </div>
    </div>
</template>

<script>
	export default {
		name: "WineCategoryListChildComponent",
        data: function() {
		    return {
		        item: {}
            }
        },
        props: ['entity_item', 'params'],
        mounted: function() {
            this.item = typeof this.entity_item !== 'undefined' ? this.entity_item : this.item;
        }
	}
</script>

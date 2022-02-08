<template>
    <div class="wine-filters" id="wineFilters" v-if="categories">
        <form :action="route('wine.list.frontend')" method="get" autocomplete="off" id="search-filters-form" class="search-entity-form">
            <div class="row filter-categories">
                <div class="col-sm-12">
                    <h4>{{ trans('Categories') }}</h4>
                </div>
                <div class="col-sm-12">
                    <div class="form-group no-margin">
                        <ul class="category-list">
                            <li class="category-list-item">
                                <a v-if="filter_category" :href="modifyUrl('category', 0)" class="category-list-link"><i class="fa fa-chevron-left"></i> {{ trans('All categories') }}</a>
                                <span v-else class="category-list-current">{{ trans('All categories') }}</span>
                            </li>
                            <template v-for="item, index in params.selected_parents">
                                <li v-if="params.wine_categories_front[item]" class="category-list-item">
                                    <a :href="modifyUrl('category', item)" class="category-list-current"><i class="fa fa-chevron-left"></i> {{ params.wine_categories_front[item] }}</a>
                                </li>
                            </template>
                            <li v-if="filter_category && params.wine_categories_front[filter_category]" class="category-list-item">
                                <span class="category-list-current">{{ params.wine_categories_front[filter_category] }}</span>
                            </li>
                            <template v-for="item, index in categories">
                                <li v-if="item.total_wines" class="category-list-item category-list-children">
                                    <a :href="modifyUrl('category', item.wine_category_id)" class="category-list-link">{{ item.name }}</a>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row" v-if="urlGetParam('category', 'fcategory') && params.parent_category">
                <!--<div class="col-sm-6">-->
                    <!--<div class="form-group no-margin">-->
                        <!--<button type="submit" class="btn btn-black btn-block">{{ trans('Search') }}</button>-->
                    <!--</div>-->
                <!--</div>-->
                <div class="col-sm-6">
                    <div class="form-group no-margin">
                        <button type="button" class="btn btn-default btn-block" @click.stop="goToListPage()">{{ trans('Clear') }}</button>
                    </div>
                </div>
            </div>
            <input type="hidden" name="_token" v-model="csrf">
        </form>
        <input type="hidden" id="location_address" :value="urlGetParam('search_location')">
    </div>
</template>

<script>
	export default {
		name: "WineListFiltersComponent",
        data: function() {
            return {
                categories: {},
                filter_category: false
            };
        },
        props: ['params'],
        mounted: function() {
		    this.categories = this.params.wine_categories_filter && Object.keys(this.params.wine_categories_filter).length ? this.params.wine_categories_filter : this.categories;
            this.filter_category = this.urlGetParam('category', 'fcategory');
        },
        methods: {
            goToListPage: function() {
                window.location.href = this.route('wine.list.frontend');
            },
            setCategory: function(catId) {
                window.location = this.modifyUrl('category', catId);
                
                return false;
            }
        }
	}
</script>

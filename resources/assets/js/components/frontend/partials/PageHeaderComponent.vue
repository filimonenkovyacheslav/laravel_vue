<template>
    <div class="page-title">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-left">
                    <h1 class="title-head">{{ title }}</h1>
                </div>
                <div class="page-title-right">
                    <save-search-results :entities="entities" :autorized="autorized"></save-search-results>
                    <div class="view" v-if="switchView">
                        <div class="table-cell hidden-xs">
                            <span :class="'view-btn btn-list' + (defGrid == 'btn-list' ? ' active' : '')" data-class="list-view" v-on:click="switchViewMode"><i class="fa fa-th-list"></i></span>
                            <span :class="'view-btn btn-grid' + (defGrid == 'btn-grid' ? ' active' : '')" data-class="grid-view" v-on:click="switchViewMode"><i class="fa fa-th-large"></i></span>
                            <span :class="'view-btn btn-grid-3-col' + (!defGrid || defGrid == 'btn-grid-3-col' ? ' active' : '')" data-class="grid-view grid-view-3-col" v-on:click="switchViewMode"><i class="fa fa-th"></i></span>
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
        props: ['title', 'entities', 'autorized', 'switchView', 'defGrid'],
        mounted: function(){
            if (location.href.indexOf('/news') !== -1){
                $('.table-cell > span.view-btn').hide();
            }
        },
        methods: {
            switchViewMode: function(e) {
                var listing = $('.property-listing');

                if(listing.length) {
                    var classes = ['list-view', 'grid-view', 'grid-view grid-view-3-col'],
                        btn = $(e.target).parents('.view-btn:first'),
                        btnsShell = btn.parents('.table-cell:first');

                    for(var i = 0; i < classes.length; i++) {
                        listing.removeClass(classes[i]);
                    }
                    btnsShell.find('.view-btn').removeClass('active');
                    listing.addClass(btn.data('class'));
                    btn.addClass('active');
                }
            }
        }
    }
</script>

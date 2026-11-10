<template>
    <div class="pagination-main" v-if="pagination.total > pagination.per_page">
        <hr>
        <ul class="pagination">
            <!--<li :class="pagination.current_page != 1 ? '' : 'disabled'">-->
                <!--<a href="javascript:void(0)" aria-label="First" v-on:click.prevent="changePage(1)">-->
                    <!--<span aria-hidden="true"><i class="fa fa-angle-double-left"></i></span>-->
                <!--</a>-->
            <!--</li>-->
            <li v-if="pagination.current_page > 1">
                <a href="javascript:void(0)" aria-label="Previous" v-on:click.prevent="changePage(pagination.current_page - 1)">
                    <span aria-hidden="true"><i class="fa fa-angle-left"></i></span>
                </a>
            </li>
            <li v-for="page in pagesNumber" :class="{'active': page == pagination.current_page}">
                <a href="javascript:void(0)" v-on:click.prevent="changePage(page)">{{ page }}</a>
            </li>
            <li v-if="pagination.current_page < pagination.last_page">
                <a href="javascript:void(0)" aria-label="Next" v-on:click.prevent="changePage(pagination.current_page + 1)">
                    <span aria-hidden="true"><i class="fa fa-angle-right"></i></span>
                </a>
            </li>
            <!--<li :class="pagination.current_page != pagination.last_page ? '' : 'disabled'">-->
                <!--<a href="javascript:void(0)" aria-label="Last" v-on:click.prevent="changePage(pagination.last_page)">-->
                    <!--<span aria-hidden="true"><i class="fa fa-angle-double-right"></i></span>-->
                <!--</a>-->
            <!--</li>-->
        </ul>
    </div>
</template>

<script>
    /**
     * Get data via props in blade template <vue_template :props></vue_template>
     */
    export default{
        props: ['pagination'],
        computed: {
            pagesNumber: function() {
                if (!this.pagination.to) {
                    return [];
                }
                var from = this.pagination.current_page - this.offset,
                    pagesArray = [],
                    to;

                from = from < 1 ? 1 : from;
                to = from + (this.offset * 2);
                to = to >= this.pagination.last_page ? this.pagination.last_page : to;

                for(var page = from; page <= to; page++) {
                    pagesArray.push(page);
                }
                return pagesArray;
            }
        },
        methods : {
            changePage: function(page) {
                window.location = this.modifyUrl('page', page);
//                page = page < 1 ? 1 : page;
//                page = page >  this.pagination.last_page ? this.pagination.last_page : page;
//                this.pagination.current_page = page;
//                this.$emit('paginate');
            }
        }
    }
</script>
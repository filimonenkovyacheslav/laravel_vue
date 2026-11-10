<template>
    <section :class="'detail-top' + (params.entity.uploadsList && params.entity.uploadsList.length ? ' detail-top-slideshow' : '')">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="header-detail">
                        <div class="header-left">
                            <div class="table-list">
                                <div class="table-cell">
									<div class="job-entity-page-header-row">
										<div class="job-entity-page-header-logotype" v-if="params.entity.photoImage">
											<img :src="params.entity.photoImage">
										</div>
										<div class="job-entity-page-header-title">
											<h1>{{ params.entity.title }}</h1>
											<span v-if="params.entity.company_name" class="job-company-name">{{ params.entity.company_name }}</span>
										</div>
									</div>
                                </div>
                            </div>
                            <!-- <jobentity-view-frontend-price :entityData="params.entity" :className="''"></jobentity-view-frontend-price> -->
                            <!--<address class="property-address">{{ params.entity.address }}</address>-->
                        </div>
                        <div class="header-right">
                            <div class="table-cell hidden-sm hidden-xs">
                                <jobentity-view-frontend-labels  :entityData="params.entity" :className="''"></jobentity-view-frontend-labels>
                            </div>
                            <jobentity-view-frontend-actions :entityData="params.entity" :userRole="params.user_role" :items="['share-btn', 'favorite-btn', 'print-btn']"></jobentity-view-frontend-actions>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                        <ul class="detail-tabs nav nav-tabs" id="jobTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="description-tab" data-toggle="tab" href="#description" role="tab" aria-controls="description" aria-selected="true">{{ trans('Description') }}</a>
                            </li>
                            <li v-if="params.entity.address || params.entity.map_address" class="nav-item">
                                <a class="nav-link" id="address-tab" data-toggle="tab" href="#address" role="tab" aria-controls="address" aria-selected="false">{{ trans('Address') }}</a>
                            </li>
                            <li v-if="this.inArray(1, params.entity.uploadsTypes)" class="nav-item">
                                <a class="nav-link" id="photo-tab" data-toggle="tab" href="#photo" role="tab" aria-controls="photo" aria-selected="false">{{ trans('Photos') }}</a>
                            </li>
                            <li v-if="this.inArray(2, params.entity.uploadsTypes)" class="nav-item">
                                <a class="nav-link" id="video-tab" data-toggle="tab" href="#video" role="tab" aria-controls="video" aria-selected="false">{{ trans('Video') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="contacts-tab" data-toggle="tab" href=".widget.widget-contacts" role="tab" aria-controls=".widget.widget-contacts" aria-selected="false">{{ trans('Contact') }}</a>
                            </li>
                        </ul>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
    /**
     * Get data via props in blade template <vue_template :props></vue_template>
     */
    export default {
        props: ['params'],
        mounted: function() {
             $('a.nav-link').on('click', function() {
                var elementClick = $(this).attr('href');
                $('html').animate({ scrollTop: $(elementClick).offset().top }, 1100);
            });
        }
    }
</script>

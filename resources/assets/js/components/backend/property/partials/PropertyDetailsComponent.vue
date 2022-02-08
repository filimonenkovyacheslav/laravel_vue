<template>
    <div class="account-block form-step">
        <div class="add-title-tab">
            <h3>{{ trans('Property Details') }}</h3>
            <div class="add-expand"></div>
        </div>
        <div class="add-tab-content">
            <div class="add-tab-row push-padding-bottom">
                <div class="row">
                    <div class="account-block form-step">
                        <div class="add-title-tab">
                            <h3>{{ trans('Property Details') }}</h3>
                            <div class="add-expand"></div>
                        </div>
                        <div class="add-tab-content">
                            <div class="add-tab-row push-padding-bottom">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="property_area">{{ trans('Area Size ( Only digits )') }}</label>
                                            <input type="text" name="property_area" id="property_area" v-model="$parent.entity.property_area" class="form-control"/>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label>{{ trans('Area Size Measure') }}</label>
                                            <div class="radio">
                                                <label v-for="item, index in $parent.params.measures" style="margin-top: 0.5rem;">
                                                    <input type="radio" name="property_area_measure" v-model="$parent.entity.property_area_measure" :value="index" />
                                                    <span style="padding-right: 10px;">{{ item }}</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="property_area">{{ trans('Bedrooms') }}</label>
                                            <input type="text" name="bedrooms" id="bedrooms" v-model="$parent.entity.bedrooms" class="form-control"/>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="land_area">{{ trans('Land Area ( Only digits )') }}</label>
                                            <input type="text" name="land_area" id="land_area" v-model="$parent.entity.land_area" class="form-control"/>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label>{{ trans('Land Size Measure') }}</label>
                                            <div class="radio">
                                                <label v-for="item, index in $parent.params.measures" style="margin-top: 0.5rem;">
                                                    <input type="radio" name="land_area_measure" v-model="$parent.entity.land_area_measure" :value="index" />
                                                    <span style="padding-right: 10px;">{{ item }}</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="bathrooms">{{ trans('Bathrooms') }}</label>
                                            <input type="text" name="bathrooms" id="bathrooms" v-model="$parent.entity.bathrooms" class="form-control"/>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="garage_area">{{ trans('Garages Size ( Only digits )') }}</label>
                                            <input type="text" name="garage_area" id="garage_area" v-model="$parent.entity.garage_area" class="form-control"/>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label>{{ trans('Garages Size Measure') }}</label>
                                            <div class="radio">
                                                <label v-for="item, index in $parent.params.measures" style="margin-top: 0.5rem;">
                                                    <input type="radio" name="garage_area_measure" v-model="$parent.entity.garage_area_measure" :value="index" />
                                                    <span style="padding-right: 10px;">{{ item }}</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="garage">{{ trans('Garages') }}</label>
                                            <input type="text" name="garage" id="garage" v-model="$parent.entity.garage" class="form-control"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
    }
</script>
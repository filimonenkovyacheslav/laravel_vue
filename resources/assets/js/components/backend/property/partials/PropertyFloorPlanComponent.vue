<template>
    <div class="account-block form-step">
        <div class="add-title-tab">
            <h3>{{ trans('Floor Plans') }}</h3>
            <div class="add-expand"></div>
        </div>
        <div class="add-tab-content">
            <div class="add-tab-row push-padding-bottom">
                <div class="row">
                    <div class="col-12">
                        <div class="floors-list">
                            <ul v-for="item, order in $parent.entity.floors" :key="item.id" class="floor-item list-three-col">
                                <li><span class="sort-floor"><i class="fa fa-navicon"></i></span></li>
                                <li>
                                    <div>
                                        <span v-if="item.title"><strong>{{ trans('Title') }}:</strong><span> {{ item.title }}</span></span>
                                        <span v-if="item.price"><strong>{{ trans('Price') }}:</strong><span> {{ item.price }} {{ $parent.params.currencies[item.currency_code] }}</span></span>
                                        <span v-if="item.area_size"><strong>{{ trans('Area Size') }}:</strong><span> {{ item.area_size }} {{ $parent.params.measures[item.area_size_measure] }}</span></span>
                                        <span v-if="item.bedrooms"><strong>{{ trans('Bedrooms') }}:</strong> {{ item.bedrooms }}</span>
                                        <span v-if="item.bathrooms"><strong>{{ trans('Bathrooms') }}:</strong> {{ item.bathrooms }}</span>
                                    </div>
                                    <div>
                                        <span v-if="item.description"><strong>{{ trans('Description') }}:</strong> {{ item.description }}</span>
                                    </div>
                                </li>
                                <li><span @click="editFloor(order)" :data-order="order" class="edit-floor"><i class="fa fa-edit"></i></span></li>
                                <li><span @click="deleteFloor(order)" :data-order="order" class="remove-floor"><i class="fa fa-remove"></i></span></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label for="floor_title">{{ trans('Title') }}</label>
                            <input type="text" name="floor_title" v-model="floorData.title" id="floor_title" class="form-control" />
                            <input type="hidden" name="floor_order" v-model="floorOrder" class="form-control" />
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="floor_bedrooms">{{ trans('Bedrooms') }}</label>
                            <input type="text" name="floor_bedrooms" v-model="floorData.bedrooms" id="floor_bedrooms" class="form-control" />
                        </div>
                    </div>
                    <div class="col-sm-6 col-12">
                        <div class="form-group">
                            <label for="floor_bathrooms">{{ trans('Bathrooms') }}</label>
                            <input type="text" name="floor_bathrooms" v-model="floorData.bathrooms" id="floor_bathrooms" class="form-control" />
                        </div>
                    </div>
                    <div class="col-sm-6 col-12">
                        <div class="form-group">
                            <label for="floor_price">{{ trans('Price') }}</label>
                            <input type="text" name="floor_price" v-model="floorData.price" id="floor_price" class="form-control" />
                        </div>
                    </div>
                    <div class="col-sm-6 col-12">
                        <div class="form-group">
                            <label for="floor_currency_code">{{ trans('Currency') }}</label>
                            <select name="floor_currency_code" v-model="floorData.currency_code" id="floor_currency_code" class="form-control">
                                <option v-for="item, index in $parent.params.currencies" :value="index">{{ item }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6 col-12">
                        <div class="form-group">
                            <label for="floor_area_size">{{ trans('Area Size') }}</label>
                            <input type="text" name="floor_area_size" v-model="floorData.area_size" id="floor_area_size" class="form-control" />
                        </div>
                    </div>
                    <div class="col-sm-6 col-12">
                        <div class="form-group">
                            <label for="floor_size_measure">{{ trans('Ares Size Measure') }}</label>
                            <div class="radio">
                                <label v-for="item, index in $parent.params.measures" style="margin-top: 0.75rem;">
                                    <input type="radio" name="floor_size_measure" v-model="floorData.area_size_measure" id="floor_size_measure" :value="index" />
                                    <span style="padding-right: 10px;">{{ item }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <!--<div class="col-12">-->
                        <!--<div class="form-group">-->
                            <!--<input type="text" name="floor_image" v-model="floorData.image" class="form-control" />-->
                            <!--<button type="button" class="btn btn-primary" @click="uploadImage">{{ trans('Upload') }}</button>-->
                            <!--<input type="file" name="floor_image_id" id="floor-upload-input" @change="onImageChange" accept="image/jpeg,.jpg,.jpeg,image/gif,.gif,image/png,.png" style="display: none;" />-->
                        <!--</div>-->
                    <!--</div>-->
                    <div class="col-12">
                        <div class="form-group">
                            <label for="floor_description">{{ trans('Description') }}</label>
                            <textarea name="floor_description" v-model="floorData.description" id="floor_description" rows="4" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <button href="#" class="btn btn-primary btn-block" @click.stop.prevent="addFloor"><i class="fa fa-save"></i> {{ trans('Save Floor') }}</button>
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
        data: function() {
            return {
                floorDataDef: {
                    title: '',
                    bedrooms: '',
                    bathrooms: '',
                    price: '',
                    currency_code: 840,
                    area_size: '',
                    area_size_measure: 1,
                    image: '',
                    description: '',
                },
                floorData: {},
                floorOrder: -1,
                sortedItem: -1
            }
        },
        mounted: function() {
            var self = this;
            self.$eventHub.$on('entityLoaded', function() {
                self.cleanFloorFormData();
                $('.floors-list').sortable({
                    start: function(e, ui) {
                        self.sortedItem = ui.item.index();
                    },
                    stop: function(e, ui) {
                        if(self.$parent.entity.floors[self.sortedItem]) {
                            var newIndex = ui.item.index(),
                                movedItem = self.$parent.entity.floors.splice(self.sortedItem, 1);
                            self.$parent.entity.floors.splice(newIndex, 0, movedItem[0]);
                        }
                    }
                });
            });
        },
        methods: {
            addFloor: function() {
                var floor = Object.assign({}, this.floorData);
                if(this.floorOrder != -1 && this.$parent.entity.floors[this.floorOrder]) {
                    this.$parent.entity.floors[this.floorOrder] = floor;
                } else {
                    this.$parent.entity.floors.push(floor);
                }
                this.cleanFloorFormData();
            },
            editFloor: function(order) {
                if(this.$parent.entity.floors[order]) {
                    this.floorData = Object.assign({}, this.$parent.entity.floors[order]);
                    this.floorOrder = order;
                }
            },
            deleteFloor: function(order) {
                if(this.$parent.entity.floors[order]) {
                    if(confirm(this.trans('Are you sure you want to delete floor plan '+ this.$parent.entity.floors[order]['title']+ '?'))) {
                        this.$parent.entity.floors.splice(order, 1);
                        if(this.floorOrder == order) {
                            this.cleanFloorFormData();
                        }
                    }
                }
            },
            cleanFloorFormData: function() {
                this.floorData = Object.assign({}, this.floorDataDef);
                this.floorOrder = -1;
            },
            onImageChange: function(e) {
                axios.post('/api/uploadImage' + id, { _token: this.csrf }).then(function(response) {
                    self.entity = response.data.entity;
                    self.$eventHub.$emit('entityLoaded');
                }).catch(function(error) {
                    console.log(error);
                });
            },
            uploadImage: function() {
                $(this.$el).find('#floor-upload-input').click();
            },
        }
    }
</script>

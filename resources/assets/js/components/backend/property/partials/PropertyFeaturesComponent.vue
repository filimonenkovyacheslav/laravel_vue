<template>
    <div class="account-block form-step">
        <div class="add-title-tab">
            <h3>{{ trans('Property Features') }}</h3>
            <div class="add-expand"></div>
        </div>
        <div class="add-tab-content">
            <div class="add-tab-row push-padding-bottom">
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-sm-12 col-12" v-for="item, index in $parent.params.features">
                        <div class="form-group">
                            <input type="checkbox" name="features[]" :id="'features-' + index" :value="item.feature_id" :checked="$parent.entity && $parent.entity.features && inArray(item.feature_id, $parent.entity.features)" @change="switchFeature" style="display: none;">
                            <div class="btn-group">
                                <label :for="'features-' + index" class="btn btn-default">
                                    <span :class="['checkbox-icon fa', $parent.entity && $parent.entity.features && inArray(item.feature_id, $parent.entity.features) ? 'fa-check' : '']"></span>
                                </label>
                                <label :for="'features-' + index" class="btn btn-default active">{{ item.name }}</label>
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
        methods: {
            switchFeature: function(e) {
                this.switchCheckboxIcon(e);

                var item = $(e.target),
                    val = parseInt(item.val()),
                    pos = typeof this.$parent.entity.features !== 'undefined' ? this.$parent.entity.features.indexOf(val) : -1;
    
                this.$parent.entity.features = typeof this.$parent.entity.features !== 'undefined' ? this.$parent.entity.features : [];
                if(item.is(':checked')) {
                    if(pos === -1) {
                        this.$parent.entity.features.push(val);
                    }
                } else {
                    if(pos !== -1) {
                        this.$parent.entity.features.splice(pos, 1);
                    }
                }
            }
        }
    }
</script>

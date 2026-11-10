<template>
    <div class="account-block form-step active">
        <div class="add-title-tab">
            <h3>{{ trans('Property description and price') }}</h3>
            <div class="add-expand"></div>
        </div>
        <div class="add-tab-content">
            <div class="add-tab-row push-padding-bottom">
                <div class="row">
                    <div class="col-sm-12" v-for="item in [
                        params.fields.title,
                        params.fields.slug,
                        params.fields.description,
                    ]">
                        <html-element :field="item" :index="item.index" :key="item.index" :params="params"></html-element>
                    </div>
                </div>
            </div>
            <div class="add-tab-row push-padding-bottom">
                <div class="row">
                    <div class="col-sm-4" v-for="item in [
                        params.fields.property_status,
                        params.fields.property_type,
                        params.fields.property_subtype,
                        params.fields.property_rent_schedule,
                    ]" :class="item.index == 'property_rent_schedule' ? 'property-status-subopt' : item.index + '_container'" :style="item.index == 'property_status' ? '' : 'display: none;'">
                        <html-element :field="item" :index="item.index" :key="item.index" :params="params"></html-element>
                    </div>
                </div>
            </div>
            <div class="add-tab-row push-padding-bottom">
                <div class="row">
                    <div class="col-sm-4" v-for="item in [
                        params.fields.price,
                        params.fields.currency_code,
                        params.fields.price_hidden,
                        params.fields.price_second,
                        params.fields.price_before,
                        params.fields.price_after,
                    ]">
                        <html-element :field="item" :index="item.index" :key="item.index" :params="params"></html-element>
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
        props: ['params'],
         mounted: function() {
            var type_links = this.params.property_type_links,
                property_types = this.params.property_types,
                property_subtypes = this.params.property_subtypes,
                saveForm = $('form[id="save-property-form"]'),
                statusList = saveForm.find('select[name="property_status"]'),
                property_type = this.params.entity.property_type,
                property_subtype = this.params.entity.property_subtype;

            saveForm.find('select[name="property_type"]').on('change', function() {
                var subTypeContainer = saveForm.find('.property_subtype_container'),
                    subTypeList = subTypeContainer.find('select').empty(),
                    statusVal = statusList.val(),
                    typeVal = $(this).val(),
                    subTypesAll = type_links.type_status_subtypes,
                    subTypes = typeVal in subTypesAll ? (statusVal in subTypesAll[typeVal] ? subTypesAll[typeVal][statusVal] : 'all') : false;

                subTypeList.append($('<option value="" selected>None</option>'));
                if(subTypes === false) {
                    subTypeContainer.hide();
                } else {
                    $.each(property_subtypes, function(index, property_subtype){
                        if(subTypes === 'all' || subTypes.indexOf(property_subtype.id) >= 0) {
                            subTypeList.append($('<option value="' + property_subtype.id + '">' + property_subtype.label + '</option>'));
                        }
                    });
                    subTypeList.val(property_subtype == null ? '' : property_subtype);
                    subTypeContainer.show();
                }
            });

            statusList.on('change', function() {
                var subopts = $('.property-status-subopt'),
                    statusVal = $(this).val(),
                    typeContainer = saveForm.find('.property_type_container'),
                    typeList = typeContainer.find('select').empty(),
                    statusTypes = statusVal in type_links.status_types ? type_links.status_types[statusVal] : 'all';

                if(statusVal == 1) {
                    subopts.show();
                } else {
                    subopts.hide();
                }

                typeList.append($('<option value="" selected>None</option>'));
                if(statusVal == '') {
                    typeContainer.hide();
                } else {
                    $.each(property_types, function(index, property_type){
                        if(statusTypes === 'all' || statusTypes.indexOf(property_type.id) >= 0) {
                            typeList.append($('<option value="' + property_type.id + '">' + property_type.label + '</option>'));
                        }
                    });
                    typeList.val(property_type == null ? '' : property_type);
                    typeContainer.show();
                    typeList.trigger('change');
                }
                property_type = null;
                property_subtype = null;
            }).trigger('change');
        }
    }
</script>
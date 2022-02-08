<template>
	<div class="account-block form-step">
		<div class="add-title-tab">
			<h3>{{ trans('Job') }}</h3>
			<div class="add-expand"></div>
		</div>
		<div class="add-tab-content">
			<div class="add-tab-row push-padding-bottom">
				<div class="row">
					<div class="col-sm-12" v-if="params.user_role=='administrator'" style="display:none">
						<div :class="['form-group', errorsList.author ? 'has-danger' : '']">
							<label for="author_name">{{ trans('Author') }} *</label>
							<input readonly type="text" value="1" name="author_name" id="author_name" v-model="entity.author_name"
							:class="['form-control', errorsList.author ? 'form-control-danger' : '']" :placeholder="trans('Enter user name or ID')" />
							<span v-if="errorsList.author" :class="['form-control-feedback']">{{ errorsList.author[0] }}</span>
						</div>
					</div>
					<div class="col-sm-12" >
						<div :class="['form-group', errorsList.title ? 'has-danger' : '']">
							<label for="title">{{ trans('Job Title') }} *</label>
							<input type="text" name="title" id="title" v-model="entity.title" :class="['form-control', errorsList.title ? 'form-control-danger' : '']"/>
							<span v-if="errorsList.title" :class="['form-control-feedback']">{{ errorsList.title[0] }}</span>
						</div>
					</div>
					<div class="col-sm-12">
						<div class="form-group">
							<label>{{ trans('Job Description') }}</label>
							<tinymce name="description" id="description" :value="entity.description" :content="entity.description"></tinymce>
						</div>
					</div>
					<div class="col-sm-4 job_type_container">
						<div class="form-group">
							<label for="job_type">{{ trans('Job Type') }}</label>
							<select name="job_type" id="job_type" v-model="entity.job_entity_types" class="form-control" @change="jobTypeChange">
								<option value="" :selected="true" :disabled="true">{{ trans('Choose Job Type') }}</option>
								<option v-for="item, index in params.job_entity_types" :value="index">{{ item }}</option>
							</select>
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
        props: ['params'],
         mounted: function() {
            var type_links = this.params.job_type_links,
                job_types = this.params.job_types,
                job_subtypes = this.params.job_subtypes,
                saveForm = $('form[id="save-jobEntity-form"]'),
                statusList = saveForm.find('select[name="job_status"]'),
                job_type = this.params.entity.job_type,
                job_subtype = this.params.entity.job_subtype;

            saveForm.find('select[name="job_type"]').on('change', function() {
                var subTypeContainer = saveForm.find('.job_subtype_container'),
                    subTypeList = subTypeContainer.find('select').empty(),
                    statusVal = statusList.val(),
                    typeVal = $(this).val(),
                    subTypesAll = type_links.type_status_subtypes,
                    subTypes = typeVal in subTypesAll ? (statusVal in subTypesAll[typeVal] ? subTypesAll[typeVal][statusVal] : 'all') : false;

                subTypeList.append($('<option value="" selected>None</option>'));
                if(subTypes === false) {
                    subTypeContainer.hide();
                } else {
                    $.each(job_subtypes, function(index, job_subtype){
                        if(subTypes === 'all' || subTypes.indexOf(job_subtype.id) >= 0) {
                            subTypeList.append($('<option value="' + job_subtype.id + '">' + job_subtype.label + '</option>'));
                        }
                    });
                    subTypeList.val(job_subtype == null ? '' : job_subtype);
                    subTypeContainer.show();
                }
            });

            statusList.on('change', function() {
                var subopts = $('.jobEntity-status-subopt'),
                    statusVal = $(this).val(),
                    typeContainer = saveForm.find('.job_type_container'),
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
                    $.each(job_types, function(index, job_type){
                        if(statusTypes === 'all' || statusTypes.indexOf(job_type.id) >= 0) {
                            typeList.append($('<option value="' + job_type.id + '">' + job_type.label + '</option>'));
                        }
                    });
                    typeList.val(job_type == null ? '' : job_type);
                    typeContainer.show();
                    typeList.trigger('change');
                }
                job_type = null;
                job_subtype = null;
            }).trigger('change');
        }
    }
</script>

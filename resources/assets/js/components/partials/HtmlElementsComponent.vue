<template>
	<div class="form-group">
		<!-- Hidden Input -->
		<input v-if="field.type == 'hidden'" type="hidden" v-bind:name="index" :id="prepareIndex(index)" :value="getValue(field.value)" class="form-control"/>

		<!-- Text Input -->
		<template v-if="field.type == 'text'">
		    <label v-bind:for="index">{{ field.label }}</label>
		    <input type="text" v-bind:name="index" :id="prepareIndex(index)" :value="getValue(field.value)" :placeholder="field.placeholder" :disabled="field.disabled == 1" class="form-control"/>
		</template>

        <!-- File Input -->
        <template v-if="field.type == 'file'">
            <label v-bind:for="index">{{ field.label }}</label>
            <input type="file" v-bind:name="index" :id="prepareIndex(index)" :disabled="field.disabled == 1" class="form-control" />
            <input type="hidden" v-bind:name="index.replace('_id', '')" :value="getValue(field.value)" class="form-control" />
        </template>

		<!-- File Uploader -->
		<template v-if="field.type == 'upload'">
		    <label v-if="field.type.label">{{ field.label }}</label>
		    <file-uploader :field="field" :index="field.index" :params="params" ></file-uploader>
		</template>

		<!-- Textarea -->
		<template v-if="field.type == 'textarea'">
		    <label>{{ field.label }}</label>
		    <textarea v-bind:name="index" :id="prepareIndex(index)" :value="getValue(field.value)" :placeholder="field.placeholder" :disabled="field.disabled == 1" class="form-control" :style="field.style"></textarea>
		</template>

        <!-- TinyMCE -->
        <template v-if="field.type == 'tinymce'">
			<label>{{ field.label }}</label>
            <tinymce v-bind:name="index" v-bind:id="index.replace('[', '-').replace(']', '')" :value="getValue(field.value)" :content="getValue(field.value)"></tinymce>
        </template>

		<!-- Selectbox -->
		<template v-if="field.type == 'selectbox'">
		    <label>{{ field.label }}</label>
		    <select v-bind:name="index" :id="prepareIndex(index)" :value="getValue(field.value)" :disabled="field.disabled == 1" class="form-control">
		    	<option v-for="v, k in field.options" :value="k">{{ v }}</option>
		    </select>
		</template>

        <!-- Multiple Selectbox -->
        <template v-if="field.type == 'multiselectbox'">
            <label>{{ field.label }}</label>
            <v-select multiple :value="getValueForSelect(field.value, field.options)" :options="getOptions(field.options)" :onChange="updateMultiselect"></v-select>
            <input type="hidden" v-bind:name="index" :id="prepareIndex(index)" :value="getValue(field.value)" class="form-control" />
        </template>

        <!-- Checkbox -->
        <template v-if="field.type == 'checkbox'">
            <input type="checkbox" v-bind:name="name || index" :id="prepareIndex(index)" :value="value || field.value" :checked="typeof checked != 'undefined' ? checked == true : getValue(field.value)" :disabled="field.disabled == 1" @change="switchCheckboxIcon()" style="display: none;">
            <div class="btn-group" :style="field.group_style">
                <label :for="prepareIndex(index)" class="btn btn-default">
                    <span :class="'checkbox-icon fa' + ((typeof checked != 'undefined' ? checked == true : getValue(field.value)) ? ' fa-check' : '')"></span>
                </label>
                <label :for="prepareIndex(index)" class="btn btn-default active" :title="label">{{ label || field.label }}</label>
                <a v-if="field.button" class="btn a-button" v-bind:href="field.button">Edit</a>
            </div>
        </template>

        <!-- Radio -->
        <template v-if="field.type == 'radio'">
            <label>{{ field.label }}</label>
            <div class="radio">
                <template v-for="v, k in field.options">
                    <label style="margin-top: 0.5rem;">
                        <input type="radio" v-bind:name="index" v-model="field.value" :value="k" />
                        <span style="padding-right: 10px;">{{ v }}</span>
                    </label>
                </template>
            </div>
        </template>

        <!-- Map -->
        <template v-if="field.type == 'map'">
            <label v-bind:for="index">{{ field.label }}</label>
            <input type="text" :name="index" :index="index" :id="prepareIndex(index)" :value="getValue(field.value.map, path)" class="form-control" />
            <input type="hidden" name="lat" :value="getValue(field.value.lat, path)" />
            <input type="hidden" name="lng" :value="getValue(field.value.lng, path)" />
        </template>

        <!-- Video Input -->
        <template v-if="field.type == 'video'">
            <label v-bind:for="index">{{ field.label }}</label>
            <input type="file" v-bind:name="index" :id="prepareIndex(index)" :disabled="field.disabled == 1" class="form-control" />
            <input type="hidden" v-bind:name="index.replace('_id', '')" :value="getValue(field.value.video_id, path)" :data-input="prepareIndex(index)" class="form-control" />
            <template v-if="getValue(field.value.video_id, path) && getValue(field.value.video, path)">
                <video controls :data-preview="prepareIndex(index)" style="width: 100%; margin-top: 10px;">
                    <source :src="'/uploads/' + getValue(field.value.video, path)">
                    {{ trans('Your browser does not support the video tag.') }}
                </video>
                <button type="button" class="btn btn-primary btn-block" :data-btn="prepareIndex(index)" @click="deleteUpload(getValue(field.value.video_id, path), prepareIndex(index))">{{ trans('Remove') }}</button>
            </template>
        </template>

		<!-- Button -->
		<button v-if="field.type == 'button'" type="submit" class="btn">{{ field.label }}</button>
	</div>
</template>

<script>
/**
 * Get data via props in blade template <vue_template :props></vue_template>
 */
    export default {
        props: ['index', 'field', 'params', 'name', 'label', 'value', 'checked', 'csrf_field', 'path'],
        methods: {
            deleteUpload: function(id, elemId) {
                $.post({
                    url: '/uploads-delete',
                    data: {id: id, _token: $('[name="_token"]').val()},
                    dataType: 'json',
                    success: function (data) {
                        var parent = $('#' + elemId).parents('.form-group:first'),
                            input = parent.find('input[data-input="' + elemId + '"]'),
                            upload = parent.find('[data-preview="' +elemId + '"]'),
                            btn =  parent.find('[data-btn="' +elemId + '"]');

                        if(input.length) {
                            input.val('');
                        }
                        if(upload.length) {
                            upload.fadeOut('slow', function() {
                                upload.remove();
                            });
                        }
                        if(btn.length) {
                            btn.hide();
                        }
                    }
                });
            }
        }
    }
</script>

<template>
    <div class="file-uploader row">
        <div class="col-md-12 col-xs-12">
            <form method="post" action="/uploads-save" enctype="multipart/form-data" :id="dropZoneId" class="dropzone">
                <div class="dz-message">
                    <div class="col-xs-8">
                        <div class="message">
                            <div class="media-drag-drop" style="position: relative;">
                                <span class="icon-cloud-upload text-primary"><i class="fa fa-cloud-upload"></i></span>
                                <h4 class="drag-title">{{ trans('Drag and drop uploads here') }}</h4>
                                <a href="javascript:;" class="btn btn-primary" style="position: relative; z-index: 1;">{{ trans('Select Files') }}</a>
                                <div class="moxie-shim moxie-shim-html5" style="position: absolute; top: 0px; left: 0px; width: 0px; height: 0px; overflow: hidden; z-index: 0;">
                                    <input type="file" multiple="multiple" accept="upload/jpeg,.jpg,.jpeg,upload/gif,.gif,upload/png,.png" style="font-size: 999px; opacity: 0; position: absolute; top: 0px; left: 0px; width: 100%; height: 100%;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="fallback">
                    <input type="file" name="file" multiple style="visibility: hidden;" />
                </div>
                <input type="hidden" name="_token" v-model="csrf" />
            </form>
            <div class="upload-inputs-shell">
                <template v-if="entityData && entityData.uploadsList">
                    <input v-for="upload in entityData.uploadsList" type="hidden" :name="fieldData.index" class="upload-input" v-model="upload.id" :data-id="upload.id"  />
                </template>
            </div>
            <div class="upload-uploads-shell" style="margin-top: 10px;">
                <template v-if="entityData && entityData.uploadsList">
                    <div v-for="upload in entityData.uploadsList" class="upload-block" :data-id="upload.id" v-if="upload.type!=0">
                        <template v-if="upload.type == 1">
                            <div class="upload-image" :style="getBgImageStyle(upload.name)"></div>
                            <span class="upload-featured label-featured label label-success" :style="upload.is_featured==1 ? '' : 'display: none;'">{{ trans(entityType=='franchise' ? 'Logo' : 'Featured') }}</span>
                            <a href="#" v-if="inArray(entityType, ['user','artist'])" class="upload-actions btn btn-primary" v-on:click="setCaption(upload.id);" :data-id="upload.id" style="top: 20%;">{{ trans('Set Caption') }}</a>
                            <a v-if="entityType != 'jobEntity'" href="#" class="upload-actions btn btn-primary" v-on:click="makeFeatured(upload.id, $event);" :data-id="upload.id" style="top: 20%;">{{ trans(entityType=='franchise' ? 'Set as Logo' : 'Make Featured') }}</a>
                            <a href="#" class="upload-actions btn btn-primary" v-on:click="deleteUpload(upload.id);" :data-id="upload.id" style="top: 50%;">{{ trans('Delete') }}</a>
                            <input v-if="inArray(entityType, ['user','artist']) && upload.caption" type="hidden" :name="'photo_caption' + upload.id" :value="upload.caption" />
                        </template>
                        <template v-if="upload.type == 2">
                            <video controls style="width: auto; height: 120px;">
                                <source :src="'/uploads/' + upload.name">
                                {{ trans('Your browser does not support the video tag.') }}
                            </video>
                            <a href="#" class="upload-actions btn btn-primary" v-on:click="deleteUpload(upload.id);" :data-id="upload.id" style="top: 5%;">{{ trans('Delete') }}</a>
                        </template>
                    </div>
                </template>
                <template v-if="entityData && entityData.uploadsList">
                <div>
                    <div v-for="upload in entityData.uploadsList" class="attach-upload-block" :data-id="upload.id" v-if="upload.type==0">
                        <template v-if="upload.type == 0">
                            <i class="fa fa-trash" @click="deleteUpload(upload.id)" style="cursor: pointer;"></i>
                            <a :href="'/uploads/' + upload.name" target="_blank">{{ getClearFileName(upload.name) }}</a>
                        </template>
                    </div>
                </div>
                </template>
            </div>
            <div v-if="inArray(entityType, ['user','artist'])">
                <modal name="set-photo-caption" :width="600" :height="250">
                    <div class="modal-content-shell">
                        <h1 class="title">{{ trans('Photo Caption') }}</h1>
                        <form action="#" method="post" class="form-horizontal" onSubmit="return false;">
                            <div class="row">
                                <div class="col-sm-12 col-xs-12">
                                    <div class="form-group">
                                        <input type="text" :id="'photo_caption_txt'+ idSuffix" name="photo_caption_txt" value="" class="form-control" :placeholder="trans('Caption')">
                                        <input type="hidden" :id="'photo_caption_id'+ idSuffix" name="photo_caption_id" value="" />
                                    </div>
                                </div>
                                <div class="col-sm-4 offset-4 col-xs-12">
                                    <div class="form-horizontal">
                                        <button class="btn btn-light btn-block" v-on:click="saveCaption();">{{ trans('Save') }}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </modal>
            </div>
            <input type="hidden" name="featured_image" :value="getFeaturedImageId()" />
            <input type="hidden" :name="fieldData.index" :id="'upload-input-example'+ idSuffix" class="upload-input" disabled="disabled" />
        </div>
        <!-- --Dropzone Preview Template-- -->
        <div :id="'preview'+ idSuffix" style="display: none;">
            <div class="dz-preview dz-file-preview">
                <div class="dz-upload"><img data-dz-thumbnail /></div>
                <div class="dz-details">
                    <div class="dz-size"><span data-dz-size></span></div>
                    <div class="dz-filename"><span data-dz-name></span></div>
                </div>
                <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>
                <div class="dz-error-message"><span data-dz-errormessage></span></div>
                <div class="dz-success-mark">
                    <svg width="54px" height="54px" viewBox="0 0 54 54" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns">
                        <!-- Generator: Sketch 3.2.1 (9971) - http://www.bohemiancoding.com/sketch -->
                        <title>Check</title>
                        <desc>Created with Sketch.</desc>
                        <defs></defs>
                        <g :id="'Page-1'+ idSuffix" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage">
                            <path d="M23.5,31.8431458 L17.5852419,25.9283877 C16.0248253,24.3679711 13.4910294,24.366835 11.9289322,25.9289322 C10.3700136,27.4878508 10.3665912,30.0234455 11.9283877,31.5852419 L20.4147581,40.0716123 C20.5133999,40.1702541 20.6159315,40.2626649 20.7218615,40.3488435 C22.2835669,41.8725651 24.794234,41.8626202 26.3461564,40.3106978 L43.3106978,23.3461564 C44.8771021,21.7797521 44.8758057,19.2483887 43.3137085,17.6862915 C41.7547899,16.1273729 39.2176035,16.1255422 37.6538436,17.6893022 L23.5,31.8431458 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z" :id="'Oval-2'+ idSuffix" stroke-opacity="0.198794158" stroke="#747474" fill-opacity="0.816519475" fill="#FFFFFF" sketch:type="MSShapeGroup"></path>
                        </g>
                    </svg>
                </div>
                <div class="dz-error-mark">
                    <svg width="54px" height="54px" viewBox="0 0 54 54" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns">
                        <!-- Generator: Sketch 3.2.1 (9971) - http://www.bohemiancoding.com/sketch -->
                        <title>error</title>
                        <desc>Created with Sketch.</desc>
                        <defs></defs>
                        <g :id="'Page-1'+ idSuffix" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage">
                            <g :id="'Check-+-Oval-2'+ idSuffix" sketch:type="MSLayerGroup" stroke="#747474" stroke-opacity="0.198794158" fill="#FFFFFF" fill-opacity="0.816519475">
                                <path d="M32.6568542,29 L38.3106978,23.3461564 C39.8771021,21.7797521 39.8758057,19.2483887 38.3137085,17.6862915 C36.7547899,16.1273729 34.2176035,16.1255422 32.6538436,17.6893022 L27,23.3431458 L21.3461564,17.6893022 C19.7823965,16.1255422 17.2452101,16.1273729 15.6862915,17.6862915 C14.1241943,19.2483887 14.1228979,21.7797521 15.6893022,23.3461564 L21.3431458,29 L15.6893022,34.6538436 C14.1228979,36.2202479 14.1241943,38.7516113 15.6862915,40.3137085 C17.2452101,41.8726271 19.7823965,41.8744578 21.3461564,40.3106978 L27,34.6568542 L32.6538436,40.3106978 C34.2176035,41.8744578 36.7547899,41.8726271 38.3137085,40.3137085 C39.8758057,38.7516113 39.8771021,36.2202479 38.3106978,34.6538436 L32.6568542,29 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z" :id="'Oval-2'+ idSuffix" sketch:type="MSShapeGroup"></path>
                            </g>
                        </g>
                    </svg>
                </div>
            </div>
        </div>
        <!-- --End of Dropzone Preview Template-- -->
    </div>
</template>

<script>
    /**
     * Get data via props in blade template <vue_template :props></vue_template>
     */
    export default {
        data: function() {
            return {
                dropZoneId: 'uploader-dropzone',
                idSuffix: '',
                currentUpload: 0,
                parallelUploads: 5,
                elems: {
                    csrf: {
                      'type': 'csrf',
                    },
                }
            }
        },
        props: ['idSuffixVal', 'entityData', 'fieldData', 'entityType', 'isArtist'],
        created: function() {
            this.idSuffix = this.idSuffixVal ? '-'+ this.idSuffixVal : this.idSuffix;
            this.dropZoneId = this.dropZoneId + this.idSuffix;
            this.dropzoneOptionsId = this.dropZoneId.split('-').map(function(item, index) { return index ? item.charAt(0).toUpperCase() + item.slice(1) : item; }).join('')
        },
        mounted: function() {
            var self = this,
                currentUpload = this.currentUpload,
                parallelUploads = this.parallelUploads,
                url = typeof this.entityType !== 'undefined' ? '/uploads-save/' + this.entityType : '/uploads-save';
    
            url = typeof this.isArtist !== 'undefined' ? '/uploads-save/art' : url;

            Dropzone.options[this.dropzoneOptionsId] = {
                url: url,
                uploadMultiple: true,
                parallelUploads: parallelUploads,
                maxFilesize: 260,
                previewTemplate: document.querySelector('#preview'+ self.idSuffix).innerHTML,
                addRemoveLinks: false,
                dictRemoveFile: 'Remove file',
                dictFileTooBig: 'Image is larger than 256MB',
                timeout: 180000,
                init: function () {
                    this.on("addedfile", function(file) {
                        var self = this,
                            removeButton = Dropzone.createElement('<a class="dz-remove">Remove file</a>');

                        removeButton.addEventListener("click", function(e) {
                            e.preventDefault();
                            e.stopPropagation();

                            self.removeFile(file);
                            $.post({
                                url: '/uploads-delete',
                                data: {id: file.id, _token: $('[name="_token"]').val()},
                                dataType: 'json',
                                success: function (data) {
                                    var parent = $('#uploader-dropzone'+ self.idSuffix).parent(),
                                        input = parent.find('.upload-inputs-shell input[data-id="' + file.id + '"]');

                                    if(input.length) {
                                        input.remove();
                                    }
                                }
                            });
                        });
                        file.previewElement.appendChild(removeButton);
                    });
                },
                success: function (file, done) {
                    currentUpload = currentUpload < parallelUploads && currentUpload < done.data.length ? currentUpload : 0;

                    var parent = $('#uploader-dropzone'+ self.idSuffix).parent(),
                        id = done.data[currentUpload],
                        input = parent.find('#upload-input-example'+ self.idSuffix),
                        newInput = input.clone();

                    file.id = id;
                    newInput.removeAttr('id');
                    newInput.removeAttr('disabled');
                    newInput.val(id);
                    newInput.data('id', id);
                    newInput.attr('data-id', id);
                    newInput.appendTo(parent.find('.upload-inputs-shell'));
                    currentUpload++;
                },
                error:  function(file, response, request) {
                    var message = response.message ? response.message : 'There is error during request',
                        ref = file.previewElement.querySelectorAll("[data-dz-errormessage]"),
                        results = [],
                        node, i;
                    if (file.size > this.options.maxFilesize * 1024 * 1024) {
                        this.emit("toobig", file);
                        message = this.options.dictFileTooBig;
                    }

                    file.previewElement.classList.add("dz-error");

                    for (i = 0; i < ref.length; i++) {
                        node = ref[i];
                        results.push(node.textContent = message);
                    }
                    return results;
                },
            };
        },
        methods: {
            getFeaturedImageId: function() {
                var list = this.entityData && this.entityData.uploadsList ? this.entityData.uploadsList : [];

                for(var i = 0; i < list.length; i++) {
                    if(list[i].is_featured == 1) {
                        return list[i].id;
                    }
                }
                return '';
            },
            makeFeatured: function(id, e) {
                e.preventDefault();
                var parent = $('#uploader-dropzone'+ this.idSuffix).parent();

                parent.find('.file-uploader').find('input[name="featured_image"]').val(id);
                if (this.entityData && this.entityData.uploadsList) {
                    var list = this.entityData.uploadsList;
    
                    for(var i = 0; i < list.length; i++) {
                        if(list[i].id == id) {
                            this.entityData.uploadsList[i].is_featured = 1;
                        } else {
                            this.entityData.uploadsList[i].is_featured = 0;
                        }
                    }
                }
                parent.find('.upload-inputs-shell input[data-id="' + id + '"]').prependTo('.upload-inputs-shell');
                parent.find('.upload-block').each(function() {
                    var block = $(this),
                        label = block.find('.upload-featured');

                    if(block.attr('data-id') == id) {
                        label.show();
                        block.prependTo('.upload-uploads-shell');
                    } else {
                        label.hide();
                    }
                });
            },
            deleteUpload: function(id) {
                var self = this;
                if(confirm('Delete this file?')) {
                    $.post({
                        url: '/uploads-delete',
                        data: {id: id, _token: $('[name="_token"]').val()},
                        dataType: 'json',
                        success: function (data) {
                            var parent = $('#uploader-dropzone'+ self.idSuffix).parent(),
                                input = parent.find('.upload-inputs-shell input[data-id="' + id + '"]'),
                                upload = parent.find('.upload-uploads-shell .upload-block[data-id="' + id + '"], .upload-uploads-shell .attach-upload-block[data-id="' + id + '"]');

                            if(input.length) {
                                input.remove();
                            }
                            if(upload.length) {
                                upload.fadeOut('slow', function() {
                                    upload.remove();
                                });
                            }
                        }
                    });
                }
            },
            saveCaption: function(e) {
                var txt = $('#photo_caption_txt'+ this.idSuffix).val(),
                    id = $('#photo_caption_id'+ this.idSuffix).val(),
                    input = $('#uploader-dropzone'+ this.idSuffix+ ' input[name=photo_caption' + id + ']');

                if(!input || !input.length) {
                    $('input[name=featured_image]').after('<input type="hidden" name="photo_caption' + id + '" value="' + txt + '"/>');
                } else {
                    input.val(txt);
                }
                this.$modal.hide('set-photo-caption');
                return false;
            },
            hideCaption: function(e) {
                this.$modal.hide('set-photo-caption');
                return false;
            },
            setCaption: function(id) {
                this.$modal.show('set-photo-caption');

                var self = this,
                    caption = $('input[name=photo_caption' + id + ']'),
                    txt = caption.length ? caption.val() : '';

                setTimeout(function() {
                    $('#photo_caption_txt'+ self.idSuffix).val(txt);
                    $('#photo_caption_id'+ self.idSuffix).val(id);
                });
                return false;
            }
        }
    }
</script>

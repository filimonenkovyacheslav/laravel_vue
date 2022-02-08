<template>
    <div class="account-block form-step">
        <div class="add-title-tab">
            <h3>Attachments</h3>
        </div>
        <div class="add-tab-content">
            <div class="add-tab-row push-padding-bottom">
                <div class="add-attachment">
                    <div class="attach-list" id="houzez_property_attachments_container"></div>
                    <div id="plupload-container"></div>
                    <div id="houzez_errors"></div>
                    <div id="houzez_attachment_dragDrop" style="position: relative;">
                        <a id="select_attachments" href="javascript:;" class="btn btn-primary" style="position: relative; z-index: 1;">{{ trans('Select Attachment') }}</a>
                        <div id="html5_1ck03l96t9pdltf16l41nrcnlm6_container" class="moxie-shim moxie-shim-html5" style="position: absolute; top: 0px; left: 0px; width: 0px; height: 0px; overflow: hidden; z-index: 0;"><input id="html5_1ck03l96t9pdltf16l41nrcnlm6" type="file" style="font-size: 999px; opacity: 0; position: absolute; top: 0px; left: 0px; width: 100%; height: 100%;" multiple="" accept="image/jpeg,.jpg,.jpeg,image/png,.png,application/pdf,.pdf,application/zip,.zip"></div>
                    </div>
                    <p>{{ trans('You can attach PDF files, Map images OR other documents to provide further details related to property.') }}</p>
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
        props: ['params']
    }
</script>
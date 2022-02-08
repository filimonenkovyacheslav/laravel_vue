<template>
    <div class="account-block form-step">
        <div class="add-title-tab">
            <h3>{{ trans('360° Virtual Tour') }}</h3>
            <div class="add-expand"></div>
        </div>
        <div class="add-tab-content">
            <div class="add-tab-row push-padding-bottom">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <textarea class="form-control" name="virtual_tour" id="virtual_tour" rows="6" :placeholder="trans('Enter virtual tour iframe / embeded code)"></textarea>
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
        props: ['params']
    }
</script>
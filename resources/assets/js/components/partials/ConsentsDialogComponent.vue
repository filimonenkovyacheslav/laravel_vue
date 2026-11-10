<template>
    <modal v-if="!this.apply_consents" name="consents-dialog" class="consents-dialog" width="100%" height="170" :pivotX="0" :pivotY="1" @before-close="beforeClose">
        <div class="modal-content-shell">
            <div class="row">
                <div class="col-xl-7 offset-xl-1 col-lg-9 col-md-12 col-sm-12 col-xs-12">
                    <h1 class="title title-modal">{{ trans('Medicaleer - all about health locally and globally. Medicaleer is the largest global health portal operating in more than 70 countries.') }}</h1>
                    <p class="description">{{ trans('Tailor my experience using cookies. By continuing to browse this site or use this app, I agree that Medicaleer may use cookies and similar technologies to improve its products and services, serve me relevant content and to personalize my experience.').replace('%s', site_name) }}
                        <a href="/terms-and-privacy" target="_blank">{{ trans('Learn more') }}</a>
                    </p>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12 col-xs-12 button-container">
                    <button class="btn btn-light btn-block" v-on:click.prevent="onClick">{{ trans('Continue') }}</button>
                </div>
            </div>
        </div>
    </modal>
</template>

<script>
    export default
    {
        props: ['site_name', 'apply_consents'],
        mounted: function() {
            if(!this.apply_consents) {
                this.$modal.show('consents-dialog');
            }
        },
        methods: {
            beforeClose: function(e) {
                var data = { _token: this.csrf };
                $.post({
                    url: '/apply-consents',
                    data: data,
                    dataType: 'json',
                    success: function (data) {
                        //window.location.reload();
                    }
                });
            },
            onClick: function(e) {
                this.$modal.hide('consents-dialog');
            }
        }
    }
</script>

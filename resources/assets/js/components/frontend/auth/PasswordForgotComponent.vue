<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="auth-container">
                    <p>{{ trans('Please enter your username or email address. You will receive a link to create a new password via email.') }}</p>
                    <div class="message"></div>
                    <form v-bind:action="route('password.email')" method="post">
                        <div class="form-group">
                            <div class="input-user input-icon">
                                <input name="email" :placeholder="trans('Enter your username or email')" class="form-control">
                            </div>
                        </div>
                        <input type="hidden" name="_token" v-model="csrf" />
                        <button type="submit" class="btn btn-primary btn-block">{{ trans('Get new password') }}</button>
                    </form>
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
        props: ['roles'],
        mounted: function() {
            var parent = $('form'),
                location = parent.find('input[name="map_address"]');

            //this.mapSearchAutocompleate(location, { parent: parent });
        }
    }
</script>
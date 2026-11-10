<template>
    <div class="container login-page">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="auth-container">
                    <h1 class="text-center">Login</h1>
                    <form action="login" method="post">
                        <div class="form-group field-group">
                            <div class="input-user input-icon">
                                <input name="email" :placeholder="trans('Username or Email')" type="text" :value="olddata.email">
                            </div>
                            <div class="input-pass input-icon">
                                <input name="password" :placeholder="trans('Password')" type="password">
                            </div>
                        </div>
                        <div class="forget-block clearfix">
                            <div class="form-group">
                                <input type="checkbox" name="remember" id="remember" @change="switchCheckboxIcon" style="display: none;">
                                <div class="btn-group">
                                    <label for="remember" class="btn btn-default">
                                        <span class="checkbox-icon fa"></span>
                                    </label>
                                    <label for="remember" class="btn btn-default active">{{ trans('Remember me') }}</label>
                                </div>
                            </div>
                            <!--<div class="form-group pull-right">-->
                                <!--<a v-bind:href="route('password.request')">{{ trans('Lost your password?') }}</a>-->
                            <!--</div>-->
                        </div>
                        <input type="hidden" name="_token" v-model="csrf" />
                        <button type="submit" class="fave-login-button btn btn-primary btn-block">{{ trans('Login') }}</button>
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
        props: ['olddata']
    }
</script>
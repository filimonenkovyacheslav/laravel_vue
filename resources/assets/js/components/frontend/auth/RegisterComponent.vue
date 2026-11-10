<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="auth-container">
                    <h1 class="text-center">Register</h1>
                    <form action="register" method="post">
                        <div class="form-group field-group">
                            <div class="input-user input-icon">
                                <input name="name" type="text" :placeholder="trans('Username')" :value="olddata.name">
                            </div>
                            <div class="input-email input-icon">
                                <input name="email" type="email" :placeholder="trans('Email')" :value="olddata.email">
                            </div>
                            <div class="input-pass input-icon">
                                <input name="password" :placeholder="trans('Password')" type="password">
                            </div>
                            <div class="input-pass input-icon">
                                <input name="password_confirmation" :placeholder="trans('Retype Password')" type="password">
                            </div>
                            <div class="input-user input-icon">
                                <input name="first_name" type="text" :placeholder="trans('First Name')" :value="olddata.first_name">
                            </div>
                            <div class="input-user input-icon">
                                <input name="last_name" type="text" :placeholder="trans('Last Name')" :value="olddata.last_name">
                            </div>
                            <div class="input-map input-icon">
                                <input name="map_address" id="map_address" type="text" :placeholder="trans('Address')" autocomplete="off">
                                <input name="lat" type="hidden">
                                <input name="lng" type="hidden">
                                <input name="city" type="hidden">
                                <input name="country" type="hidden">
                            </div>
                        </div>
                        <div class="form-group">
                            <select name="role_id" class="form-control">
                                <option v-if="!onlyrole || onlyrole=='all'" value=""> {{ trans('Select Type') }}</option>
                                <option v-for="item in roles" :selected="onlyrole == item.name || olddata.role_id == item.id" :value="item.id">{{ item.title }}</option>
                            </select>
                        </div>
                        <div id="prof-list" class="form-group" :style="olddata.role_id == 8 ? '' : 'display:none'">
                            <v-select multiple searchable value="" :options="getOptionsSort(params.professions, params.professions_sort)" :onChange="updateMultiselect"></v-select>
                            <input type="hidden" name="professions" id="professions" value="">
                        </div>
                        <div class="form-group">
                            <input type="checkbox" name="term_condition" id="term_condition" @change="switchCheckboxIcon" style="display: none;">
                            <div class="btn-group">
                                <label for="term_condition" class="btn btn-default">
                                    <span class="checkbox-icon fa"></span>
                                </label>
                                <label for="term_condition" class="btn btn-default active">{{ trans('I agree with your') }} <a v-bind:href="route('page.terms')" target="_blank">{{ trans('Terms & Conditions') }}</a></label>
                            </div>
                        </div>
                        <input type="hidden" name="_token" v-model="csrf" />
                        <input type="hidden" name="role" :value="onlyrole">
                        <button type="submit" class="fave-register-button btn btn-primary btn-block">{{ trans('Register') }}</button>
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
        props: ['roles', 'onlyrole', 'params', 'olddata'],
        mounted: function() {
            var self=this,
                parent = $('form'),
                profList = parent.find('#prof-list');

            this.geoSearchAutocompleate(document.getElementById('map_address'), {
                setMarker: false,
                onSelect: function(item, event) {
                    parent.find('input[name="lat"]').val(item.lat);
                    parent.find('input[name="lng"]').val(item.lng);
                    parent.find('input[name="city"]').val(item.city);
                    parent.find('input[name="country"]').val(item.country);
                }
            });
            parent.find('select[name="role_id"]').on('change', function() {
                var selected = $(this).find('option:selected').val();
                if(selected == 1) {
                    alert(self.trans('You create a super admin, he will have all your opportunities. Are you sure?'));
                }
                if(selected == 8) {
                    profList.show();
                } else {
                    profList.hide();
                }
            });
        }
    }
</script>
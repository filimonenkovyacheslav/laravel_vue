<template>
    <div class="account-block form-step">
        <div class="add-title-tab">
            <h3>{{ trans('Contact Information') }}</h3>
            <div class="add-expand"></div>
        </div>
        <div class="add-tab-content">
            <div class="add-tab-row push-padding-bottom">
                <p>{{ trans('What information do you want to display in the agent data container?') }}</p>
                <div class="radio">
                    <label><input value="agency_info" type="radio" class="rwmb-radio" checked="checked" name="fave_agent_display_option">{{ trans('Agency Data.') }}</label>
                </div>
                <div class="radio">
                    <label><input value="agent_info" type="radio" class="rwmb-radio" name="fave_agent_display_option">{{ trans('Agent Data (Choose agent from the list below)') }}</label>
                </div>
                <div class="radio">
                    <label><input value="none" type="radio" class="rwmb-radio" name="fave_agent_display_option">{{ trans('Do not display') }}</label>
                </div>
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <select name="fave_agents" class="selectpicker" data-live-search="false" data-live-search-style="begins">
                                <option value="-1">None</option>
                                <option value="507759">Agent2 TestAgent</option>
                                <option value="507758">Agent TestAgent</option>
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
        props: ['params']
    }
</script>
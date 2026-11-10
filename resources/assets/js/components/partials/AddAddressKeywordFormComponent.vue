<template>
    <div class="add-address-keyword-form">
        <a href="#close" class="close-btn" @click.stop="closeModal()">&times;</a>
        <h4>{{ trans('Add Address Keyword') }}</h4>
        <form id="save-address-keyword-form" action="/save-address-keyword" method="POST">
            <div class="submit-form-wrap">
                <div class="form-group">
                    <label for="keyword">{{ trans('Keyword *') }}</label>
                    <input id="keyword" class="form-control" name="keyword" required>
                </div>
                <div class="form-group">
                    <label for="slug">{{ trans('Slug') }}</label>
                    <input id="slug" class="form-control" name="slug">
                </div>
                <input type="hidden" name="_token" v-model="csrf">
                <button type="button" class="btn btn-primary btn-block" @click="onSubmitAddressKeyword()">{{ trans('Save') }}</button>
            </div>
        </form>
    </div>
</template>

<script>
	export default {
		name: "AddAddressKeywordFormComponent",
        mounted: function() {
            var self = this;
        },
        methods: {
            onSubmitAddressKeyword: function() {
                var self = this,
                    form = $('#save-address-keyword-form'),
                    formData = form.serialize();
    
                axios.post('/save-address-keyword', formData).then(function(response) {
                    if (self.$parent.entity.keywords) {
                        self.$parent.entity.keywords.push(response.data);
                    } else {
                        self.$parent.addKeyword({id: response.data.key_id, value: response.data.keyword});
                    }
                    self.closeModal();
                }).catch(function(error) {
                    console.log(error);
                });
                
                return false;
            },
            closeModal: function() {
                $('.add-address-keyword-form').toggleClass('opened');
            }
        }
	}
</script>

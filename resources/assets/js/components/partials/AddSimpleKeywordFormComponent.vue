<template>
    <div class="add-simple-keyword-form">
        <a href="#close" class="close-btn" @click.stop="closeModal()">&times;</a>
        <h4>{{ trans('Add Simple Keyword') }}</h4>
        <form id="save-simple-keyword-form" action="/save-simple-keyword" method="POST">
            <div class="submit-form-wrap">
                <div class="form-group">
                    <label for="keyword">{{ trans('Keyword *') }}</label>
                    <input id="keyword" class="form-control" name="keyword" required>
                </div>
                <input type="hidden" id="slug" class="form-control" name="slug">
                <input type="hidden" name="_token" v-model="csrf">
                <button type="button" class="btn btn-primary btn-block" @click="onSubmitSimpleKeyword()">{{ trans('Save') }}</button>
            </div>
        </form>
    </div>
</template>

<script>
	export default {
		name: "AddSimpleKeywordFormComponent",
        mounted: function() {
            var self = this;
        },
        methods: {
            onSubmitSimpleKeyword: function() {
                var self = this,
                    form = $('#save-simple-keyword-form'),
                    formData = form.serialize();
    
                axios.post('/save-simple-keyword', formData).then(function(response) {
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
                $('.add-simple-keyword-form').toggleClass('opened');
            }
        }
	}
</script>

<template>
    <div class="user-dashboard-right dashboard-with-panel">
        <div class="board-header board-header-4">
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <div class="board-header-left">
                        <h3 class="board-title">{{ trans('Edit Page') }}</span></h3>
                    </div>
                    <div class="board-header-right">
                        <ol class="breadcrumb">
                            <li itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
                                <a itemprop="url" v-bind:href:href="route('home')">
                                    <span itemprop="title">{{ trans('Home') }}</span>
                                </a>
                            </li>
                            <li class="active">{{ trans('Edit Page') }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="dashboard-content-area dashboard-fix">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="my-profile-search">
                        <form action="/save-page-content" id="form-page-content" method="POST">
                            <div class="account-block account-profile-block">
                                <div class="row">
                                    <div class="profile-top-left">
                                        <h4>{{ params.page_title }}</h4>
                                    </div>
                                    <div class="profile-top-right text-right">
                                        <div class="sort-tab text-right">
                                            <select class="sort-select form-control" name="lang_id" id="page_lang">
                                                <option v-for="lang, index in params.languages" :value="lang.code" > {{ lang.name }} ({{ index }})</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <tinymce name="content" id="page_content" :value="params.page_content" :content="params.page_content" :options="{height: 400}"></tinymce>
                                </div>
                                <input type="hidden" name="name" :value="params.page_name">
                                <input type="hidden" name="_token" v-model="csrf">
                                <button class="btn btn-primary pull-right">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['params'],
        mounted: function() {
            var self = this;
            this.contents = this.params.translations;

            $('#page_lang').val(this.params.page_lang).on('change', function() {           
                tinymce.editors['page_content'].setContent(self.getPageContent($(this).val()));
            });
        },
        methods: {
            getPageContent: function(langId) {
                var pageContent = '',
                    data;
                for(var i in this.contents) {
                    data = this.contents[i];

                    if(data['lang_id'] == langId) {
                        pageContent = data['content'];
                        break;
                    }
                }
                return pageContent;
            }
        }
    }
</script>
<template>
<div id="section-body" :data-franchise-id="params.entity.id" style="padding-top: 0;">
<div class="container">
<div id="compare-controller" class="compare-panel">
    <div class="compare-panel-header">
        <h4 class="title"> {{ trans('Compare Listings') }} <span class="panel-btn-close pull-right"><i class="fa fa-times"></i></span></h4>
    </div>
    <div id="compare-properties-basket"></div>
</div>
<div class="page-title breadcrumb-top">
    <div class="row">
        <div class="col-sm-12">
            <ol class="breadcrumb">
                <li itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
                    <a itemprop="url" :href="route('home')">
                        <span itemprop="title">{{ trans('Home') }}</span>
                    </a>
                </li>
                <li class="active">{{ params.entity.title}}</li>
            </ol>
            <div class="page-title-left">
                <h1 class="title-head">{{ params.entity.title}}</h1>
            </div>
        </div>
    </div>
</div>
    <div class="row">
        <div class="col-sm-12">
            <franchise-view-frontend-detail :params="params" :captcha="captcha"></franchise-view-frontend-detail>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div id="content-area">
                <franchise-view-frontend-tabs :params="params"></franchise-view-frontend-tabs>
            </div>
        </div>
    </div>
</div>
</div>
</template>

<script>
    export default {
        props: ['params', 'captcha']
    }
</script>

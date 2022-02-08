<template>
<div id="section-body" :data-id="params.user.id" style="padding-top: 0;">
<div class="container">
<div class="page-title breadcrumb-top">
    <div class="row">
        <div class="col-sm-12">
            <ol class="breadcrumb">
                <li itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
                    <a itemprop="url" :href="route('home')">
                        <span itemprop="title">Home</span>
                    </a>
                </li>
                <li class="active">{{ params.user.first_name}} {{ params.user.last_name}}</li>
            </ol>
            <div class="page-title-left">
                <h1 class="title-head">
                    {{ params.user.first_name}} {{ params.user.last_name}}
                </h1>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <agent-view-frontend-detail :params="params"></agent-view-frontend-detail>
    </div>
</div>
<div class="row">
<div class="col-lg-8 col-md-8 col-sm-12 col-xs-12 list-grid-area container-contentbar">
<div id="content-area">
<div class="property-listing list-view">
<div class="row">
    <property-list-frontend-list-item :entities="params.user.properties" :params="params"></property-list-frontend-list-item>
</div>
</div>
</div>
</div>
<div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 col-md-offset-0 col-sm-offset-3 container-sidebar ">
    <aside id="sidebar" class="sidebar-white"></aside>
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

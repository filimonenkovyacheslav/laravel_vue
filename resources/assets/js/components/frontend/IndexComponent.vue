<template>
	<div id="section-body">
        <div class="statistics container">
            <div class="row justify-content-center">
                <div class="col-lg-2 col-md-2 col-sm-4 col-6" v-for="item in params.counter">
                    <div class="stat">
                        <div class="icon"><img :src="item.icon" :alt="item.label" /></div>
                        <div class="number">{{ item.count }}</div>
                        <div class="count-title">{{ item.label }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
    	props: ['params']
    }
</script>

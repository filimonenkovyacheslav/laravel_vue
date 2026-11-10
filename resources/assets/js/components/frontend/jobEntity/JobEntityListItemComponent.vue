<template>
    <div class="job-list-wrapper">
			<div v-bind:class="{'job-list-el':'true', 'job-list-el-favorite' : (prop.label) }" v-for="prop in entities" :data-jobentity-id="prop.id">
				<h2 class="job-list-el-title">
					<a :href="route('jobEntity.view.frontend', {'slug': prop.slug})">{{ prop.title }}</a>
				</h2>
				<div class="job-list-el-time-ago" v-if="prop.created_at && !prop.label">
					{{ prop.created_at }}
				</div>
				<div class="job-list-el-company-name" v-if="prop.company_name">
					{{ prop.company_name }}
				</div>
				<div class="job-list-el-category job-list-el-bold" v-if="prop.job_category" >
					{{ prop.job_category }}
				</div>
				<address class="job-list-el-location job-list-el-bold" v-if="prop.city" >
					<span :v-if="prop.city">{{ prop.city }}</span><span :v-if="prop.state">, {{ prop.state }}</span>
				</address>
				<div v-if="!prop.price_hidden" class="job-ul-li-bold">
					<span v-if="prop.price_before" >{{ prop.price_before }}</span>
					<span v-if="prop.price_view && prop.price_view.default.price" >{{ prop.price_view.default.price }}</span>
					<span v-if="prop.price_view_second && prop.price_view_second.default.price" > - {{ prop.price_view_second.default.price }}</span>
					<span v-if="prop.job_salary_type" >{{ prop.job_salary_type }}</span>
					<span v-if="prop.price_after" >{{ prop.price_after }}</span>
				</div>
				<div v-if="prop.job_type" class="job-ul-li-bold">{{ prop.job_type }}</div>
				<div class="job-list-el-short-description" v-if="prop.short_description" v-html="prop.short_description"></div>
				<jobentity-view-frontend-labels :entityData="prop" :className="'label-right hide-on-list'"></jobentity-view-frontend-labels>
				<div class="job-list-el-row">
					<div class="job-list-el-buttons">
						<jobentity-view-frontend-actions :entityData="prop" :userRole="params.user_role" :items="['favorite-btn', 'camera-btn', 'compare-btn']" ></jobentity-view-frontend-actions>
					</div>
					<div class="job-list-el-logotype" v-if="prop.photoImage != ''">
						<img :src="prop.photoImage">
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
        props: ['params', 'entities']
    }
</script>

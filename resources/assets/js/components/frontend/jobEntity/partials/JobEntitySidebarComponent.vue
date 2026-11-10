<template>
    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 col-md-offset-0 col-sm-offset-3 container-sidebar ">
        <aside id="sidebar" class="sidebar-white">
            <div id="houzez_mortgage_calculator" class="widget widget-calculate">
                <div class="widget-body">
                    <ul class="job-ul">
						<li class="job-ul-li-bold">{{ this.params.entity.created_at }}</li>
						<li v-if="this.params.entity.city" class="job-ul-li-bold">
							{{ this.params.entity.city }}<span v-if="this.params.entity.state">, {{ this.params.entity.state }}</span>
						</li>
						<li v-if="!this.params.entity.price_hidden" class="job-ul-li-bold">
							<span v-if="this.params.entity.price_before" >{{ this.params.entity.price_before }}</span>
							<span v-if="this.params.entity.price_view && this.params.entity.price_view.default.price" >{{ this.params.entity.price_view.default.price }}</span>
							<span v-if="this.params.entity.price_view_second && this.params.entity.price_view_second.default.price" > - {{ this.params.entity.price_view_second.default.price }}</span>
							<span v-if="this.params.entity.job_salary_type" >{{ this.params.entity.job_salary_type }}</span>
							<span v-if="this.params.entity.price_after" >{{ this.params.entity.price_after }}</span>
						</li>
						<li v-if="this.params.entity.job_type" class="job-ul-li-bold">{{ this.params.entity.job_type }}</li>
						<li v-if="this.params.entity.job_category" class="job-ul-li-bold">{{ this.params.entity.job_category }}</li>
					</ul>
					<a class="job-view-page-pdf-button" v-for="item in this.params.entity.pdfList" :href="'/uploads/'+item" target="_blank">{{ trans('Download PDF file') }}</a>
                </div>
            </div>
            <jobentity-contacts-widget :params="params" :captcha="captcha"></jobentity-contacts-widget>
        </aside>
    </div>
</template>

<script>
    /**
     * Get data via props in blade template <vue_template :props></vue_template>
     */
    export default {
        props: ['params', 'captcha'],
        mounted: function() {
			var self = this;
			self._prepareValues();
            var money = this.params.entity.price_view;

            if( $('#houzez_mortgage_calculator').length > 0 ) {
                $('#houzez_mortgage_calculate').click(function(e) {
                    e.preventDefault();

                    var totalPrice  = 0,
                        down_payment = 0,
                        amount_financed = 0,
                        term_years = 0,
                        interest_rate = 0,
                        monthInterest = 0,
                        intVal = 0,
                        mortgage_pay = 0,
                        annualCost = 0,
                        reg,
                        payment_period = $('#mc_payment_period').val(),
                        mortgage_pay_text = $('#mc_payment_period option[value="' + payment_period + '"').text() + ' ' + $('#mc_txt_payment').val(),
                        currency_symb = money.local.symbol,
                        currency_first = money.local.symbol_first,
                        thousands_separator = money.local.thousands_separator,
                        decimal_mark = money.local.decimal_mark;

                    payment_period = $('#mc_payment_period').val();

                    totalPrice = $('#mc_total_amount').val();
                    down_payment = $('#mc_down_payment').val();
                    if(thousands_separator != '') {
                        reg = new RegExp('\\' + thousands_separator, 'g');
                        totalPrice = totalPrice.replace(reg, '');
                        down_payment = down_payment.replace(reg, '');
                    }
                    if(decimal_mark != '.') {
                        reg = new RegExp('\\' + decimal_mark, 'g');
                        totalPrice = totalPrice.replace(reg, '.');
                        down_payment = down_payment.replace(reg, '.');
                    }
                    if(currency_symb != '') {
                        reg = new RegExp('\\' + currency_symb, 'g');
                        totalPrice = totalPrice.replace(reg, '').trim();
                        down_payment = down_payment.replace(reg, '').trim();
                    }

                    amount_financed = totalPrice - down_payment;
                    term_years =  parseInt ($('#mc_term_years').val(),10) * payment_period;
                    interest_rate = parseFloat ($('#mc_interest_rate').val(),10);
                    monthInterest = interest_rate / (payment_period * 100);
                    intVal = Math.pow( 1 + monthInterest, -term_years );
                    mortgage_pay = amount_financed * (monthInterest / (1 - intVal));
                    annualCost = mortgage_pay * payment_period;

                    if(currency_first) {
                        $('#mortgage_mwbi').html("<h3>"+mortgage_pay_text+ ":<span> " +currency_symb+ (Math.round(mortgage_pay * 100)) / 100 + "</span></h3>");
                        $('#amount_financed').html(currency_symb+(Math.round(amount_financed * 100)) / 100);
                        $('#mortgage_pay').html(currency_symb+(Math.round(mortgage_pay * 100)) / 100);
                        $('#annual_cost').html(currency_symb+(Math.round(annualCost * 100)) / 100);
                    } else {
                        $('#mortgage_mwbi').html("<h3>"+mortgage_pay_text+ ":<span> "+(Math.round(mortgage_pay * 100)) / 100 + currency_symb + "</span></h3>");
                        $('#amount_financed').html(((Math.round(amount_financed * 100)) / 100)+currency_symb);
                        $('#mortgage_pay').html(((Math.round(mortgage_pay * 100)) / 100)+currency_symb);
                        $('#annual_cost').html(( (Math.round(annualCost * 100)) / 100)+currency_symb);
                    }

                    $('#total_mortgage_with_interest').html();
                    $('.morg-detail').show();
                });
            }
            $('.show-morg').on('click',function () {
                if($(this).hasClass('active')) {
                    $('.morg-summery').slideUp();
                    $(this).removeClass('active');
                } else {
                    $('.morg-summery').slideDown();
                    $(this).addClass('active');
                }
            });
        },
		methods: {
			_prepareValues: function() {
				this.params.entity.job_type = this.params.entity.job_type === null ? '' : this.params.entity.job_type;
				this.params.entity.job_salary_type = this.params.entity.job_salary_type === null ? '' : this.params.entity.job_salary_type;
            },
		},
    }
</script>

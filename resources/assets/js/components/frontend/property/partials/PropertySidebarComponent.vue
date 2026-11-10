<template>
    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 col-md-offset-0 col-sm-offset-3 container-sidebar ">
        <aside id="sidebar" class="sidebar-white">
            <div id="houzez_mortgage_calculator" class="widget widget-calculate">
                <div class="widget-top">
                    <div class="widget-title detail-title">
                        <img src="/images/items_icons/calculator.png" />
                        <span>{{ trans('Mortgage Calculator') }}</span>
                    </div>
                </div>
                <div class="widget-body">
                    <form method="" action="#">
                        <div class="form-group icon-holder">
                            <input class="form-control" id="mc_total_amount" :placeholder="trans('Total Amount')" type="text">
                            <span class="field-icon">$</span>
                        </div>
                        <div class="form-group icon-holder">
                            <input class="form-control" id="mc_down_payment" :placeholder="trans('Down Payment')" type="text">
                            <span class="field-icon">$</span>
                        </div>
                        <div class="form-group icon-holder">
                            <input class="form-control" id="mc_interest_rate" :placeholder="trans('Interest Rate')" type="text">
                            <span class="field-icon">%</span>
                        </div>
                        <div class="form-group icon-holder">
                            <input class="form-control" id="mc_term_years" :placeholder="trans('Loan Term (Years)')" type="text">
                            <span class="field-icon"><i class="fa fa-calendar"></i></span>
                        </div>
                        <div class="form-group icon-holder">
                            <select class="form-control" id="mc_payment_period">
                                <option value="12">{{ trans('Monthly') }}</option>
                                <option value="26">{{ trans('Bi-Weekly') }}</option>
                                <option value="52">{{ trans('Weekly') }}</option>
                            </select>
                            <input type="hidden" id="mc_txt_payment" :value="trans('Payment')">
                        </div>

                        <button id="houzez_mortgage_calculate" class="btn btn-black btn-block">{{ trans('Calculate') }}</button>
                        <div class="morg-detail">
                            <div class="morg-result">
                                <div id="mortgage_mwbi"></div>
                                <img src="/images/items_icons/info.png" alt="icon inspector" class="show-morg">
                            </div>
                            <div class="morg-summery">
                                <div class="result-title">
                                    {{ trans('Amount Financed') }}:
                                </div>
                                <div id="amount_financed" class="result-value"></div>
                                <div class="result-title">
                                    {{ trans('Mortgage Payments') }}:
                                </div>
                                <div id="mortgage_pay" class="result-value"></div>
                                <div class="result-title">
                                    {{ trans('Annual cost of Loan') }}:
                                </div>
                                <div id="annual_cost" class="result-value"></div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <property-contacts-widget :params="params" :captcha="captcha"></property-contacts-widget>
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

                    payment_period = $('#mc_payment_period').val() || 1;

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
                    term_years =  parseInt ($('#mc_term_years').val()||0,10) * payment_period;
                    interest_rate = parseFloat ($('#mc_interest_rate').val()||0,10);
                    monthInterest = interest_rate / (payment_period * 100);
                    intVal = Math.pow( 1 + monthInterest, -term_years );
                    mortgage_pay = amount_financed * (monthInterest / (1 - intVal)||1);
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
        }
    }
</script>

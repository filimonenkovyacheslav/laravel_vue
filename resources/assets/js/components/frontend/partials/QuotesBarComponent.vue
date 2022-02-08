<template>
    <div class="advanced-search advance-search-header advanced-search-home">
        <div class="container" id="quote-container">
            <div class="row">
                <div class="col-sm-12 search-container">
                    <form action="" method="post" autocomplete="off" id="quote-form">
                                <div class="main-quotes-label">{{ trans('Get Recommendations') }}</div>

                        <div class="row">
                            <div class="col-lg-4 col-md-4 col-xs-12">
                                <div class="form-group no-margin">
                                    <label class="quotes-label quotes-label-full">{{ trans('What medical service do you need?') }}</label>
                                    <input type="text" name="quote_what" id="quote_what" :placeholder="trans('Please enter service')" class="form-control">
                                    <input type="hidden" name="quote_id" :value="0"/>
                                    <input type="hidden" id="quote_what_error" :value="trans('Please enter service')"/>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-xs-12">
                                <div class="form-group no-margin">
                                    <label class="quotes-label quotes-label-full">{{ trans('Where do you need it?') }}</label>
                                    <input type="text" name="quote_where" id="quote_where" :placeholder="trans('Please enter your suburb and state')" class="form-control">
                                    <input type="hidden" id="quote_where_error" :value="trans('Please enter your suburb and state')"/>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-xs-12">
                                <div class="form-group no-margin">
                                    <label class="quotes-label quotes-label-leer"> </label>
                                    <button type="button" id="quote-start" class="btn btn-black btn-block">{{ trans('Next') }}</button>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="_token" v-model="csrf">
                        <input type="hidden" name="quote_when" value="">
                        <input type="hidden" name="quote_date" value="">
                        <input type="hidden" name="quote_budget" value="">
                        <input type="hidden" name="quote_about" value="">
                        <input type="hidden" name="quote_fname" value="">
                        <input type="hidden" name="quote_lname" value="">
                        <input type="hidden" name="quote_email" value="">
                        <input type="hidden" name="quote_phone" value="">
                    </form>
                </div>
            </div>
        </div>
        <modal name="quotes-step-1" class="modal-quotes" :width="400" :height="360" data-step="1" @before-open="beforeOpen" @before-close="beforeClose">
            <div class="modal-quotes-shell">
                <label class="quotes-label">{{ trans('When do you need this medical service?') }}</label>
                <div class="form-group no-margin features-list">
                    <div class="col-md-6">
                        <label class="checkbox-inline">
                            <input type="radio" name="quote_when" value="urgently" class="quotes-radio" checked v-on:change="quotesWhenChange"><span> {{ trans('Urgently') }}</span>
                        </label>
                    </div>
                    <div class="col-md-6">
                        <label class="checkbox-inline">
                            <input type="radio" name="quote_when" value="week" class="quotes-radio" v-on:change="quotesWhenChange"><span> {{ trans('Within a week') }}</span>
                        </label>
                    </div>
                    <div class="col-md-6">
                        <label class="checkbox-inline">
                            <input type="radio" name="quote_when" value="month" class="quotes-radio" v-on:change="quotesWhenChange"><span> {{ trans('Within a month') }}</span>
                        </label>
                    </div>
                    <div class="col-md-12">
                        <label class="checkbox-inline" style="float:left;">
                            <input type="radio" name="quote_when" value="date" class="quotes-radio" v-on:change="quotesWhenChange"><span> {{ trans('Specfic date') }}</span>
                        </label>
                        <div style="float:left;display:none;" id="quotes-when-date">
                            <input type="date" name="quote_date" value="" class="form-control quotes-date">
                        </div>
                        <div style="clear:both;">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="checkbox-inline">
                            <input type="radio" name="quote_when" value="flexible" class="quotes-radio" v-on:change="quotesWhenChange"><span> {{ trans('Flexible') }}</span>
                        </label>
                    </div>
                </div>
                <div class="row quotes-step-buttons">
                    <div class="col-md-4">
                        <div class="form-group no-margin">
                            <button type="button" class="btn btn-black btn-block" @click="prevStep">{{ trans('Close') }}</button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="quotes-label step-label">{{ trans('Step') }} 1/3</label>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group no-margin">
                            <button type="button" class="btn btn-black btn-block" @click="nextStep">{{ trans('Next') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </modal>
        <modal name="quotes-step-2" class="modal-quotes" :width="500" :height="270" data-step="2" @before-open="beforeOpen" @before-close="beforeClose">
            <div class="modal-quotes-shell">
                <label class="quotes-label">{{ trans('Tell us more about the medical service you need') }}</label>
                <div class="form-group no-margin">
                    <textarea name="quote_about" value="" class="form-control" style="height:100px;"></textarea>
                </div>
                <div class="row quotes-step-buttons">
                    <div class="col-md-4">
                        <div class="form-group no-margin">
                            <button type="button" class="btn btn-black btn-block" @click="prevStep">{{ trans('Previous') }}</button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="quotes-label step-label">{{ trans('Step') }} 2/3</label>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group no-margin">
                            <button type="button" class="btn btn-black btn-block" @click="nextStep">{{ trans('Next') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </modal>
        <modal name="quotes-step-3" class="modal-quotes" :width="500" :height="510" data-step="3" @before-open="beforeOpen" @before-close="beforeClose">
            <div class="modal-quotes-shell">
                <label class="quotes-label">{{ trans('First name') }} * <span class="quotes-error" data-field="quote_fname">{{ trans('Field is requered') }}</span></label>
                <div class="form-group no-margin">
                    <input type="text" name="quote_fname" value="" class="form-control" v-on:change="quotesFieldChange">
                </div>
                <label class="quotes-label">{{ trans('Last name') }} * <span class="quotes-error" data-field="quote_fname">{{ trans('Field is requered') }}</span></label>
                <div class="form-group no-margin">
                    <input type="text" name="quote_lname" value="" class="form-control" v-on:change="quotesFieldChange">
                </div>
                <label class="quotes-label">{{ trans('Your email address') }} * <span class="quotes-error" data-field="quote_email">{{ trans('Field is requered') }}</span></label>
                <div class="form-group no-margin">
                    <input type="text" name="quote_email" value="" class="form-control" v-on:change="quotesFieldChange">
                </div>
                <label class="quotes-label">{{ trans('Your phone number') }} * <span class="quotes-error" data-field="quote_phone">{{ trans('Field is requered') }}</span></label>
                <div class="form-group no-margin">
                    <input type="text" name="quote_phone" value="" class="form-control" v-on:change="quotesFieldChange">
                </div>
                <div class="row quotes-step-buttons">
                    <div class="col-md-4">
                        <div class="form-group no-margin">
                            <button type="button" class="btn btn-black btn-block" @click="prevStep">{{ trans('Previous') }}</button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="quotes-label step-label">{{ trans('Step') }} 3/3</label>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group no-margin">
                            <button type="button" class="btn btn-black btn-block" @click="nextStep">{{ trans('Submit') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </modal>
        <modal name="quotes-step-4" class="modal-quotes" :width="350" :height="200" data-step="5" @before-open="beforeOpen" @before-close="beforeClose">
            <div class="modal-quotes-shell">
                <label class="quotes-label quotes-label-center"><i aria-hidden="true" class="fa fa-check-circle" style="font-size:48px;color:#20b720"></i></label>
                <label class="quotes-label quotes-label-center">{{ trans('Recommendation Form submitted.') }}</label>
                <label class="quotes-label quotes-label-center">{{ trans('We will contact you as soon as we receive a response from users. Thank you.') }}</label>
            </div>
        </modal>
    </div>
</template>

<script>
    export default {
        props: ['params'],
        mounted: function() {
            var self = this,
                quoteForm = $('#quote-form'),
                quoteId = quoteForm.find('input[name="quote_id"]'),
                quoteWhat = quoteForm.find('#quote_what'),
                quoteWhere = quoteForm.find('#quote_where');

            this.getUserAutocomplete(quoteWhat, {
                url: '/search-quote',
                minlen: 1,
                onSelect: function(item, event) {
                    quoteId.val(item.id);
                }
            });

            quoteForm.find('#quote-start').on('click', function() {
                var error = false;
                if(quoteWhat.val().length == 0) {
                    quoteWhat.attr('placeholder', quoteForm.find('#quote_what_error').val());
                    error = true;
                }
                if(quoteWhere.val().length == 0) {
                    quoteWhere.attr('placeholder', quoteForm.find('#quote_where_error').val());
                    error = true;
                }
                if(!error) {
                    self.$modal.show('quotes-step-1');
                }
            });
        },
        methods: {
            beforeOpen(event) {
                $('#quote-container').hide();
                $('.advanced-search-home').css('transform', 'none');
            },
            beforeClose(event) {
                var form = $('#quote-form');
                $('.modal-quotes-shell').find('input, textarea').each(function() {
                    var $this = $(this),
                        formInput = form.find('input[name="'+$this.attr('name')+'"]');
                    if(formInput.length > 0 && ($this.attr('type') != 'radio' || $this.is(':checked'))) {
                        formInput.val($this.val());
                    }
                })
                $('#quote-container').show();
                $('.advanced-search-home').css('transform', 'translate(0%,-50%)');
            },
            prevStep(event) {
                var step = $(event.target).closest('div.modal-quotes').data('step');
                this.$modal.hide('quotes-step-'+step);
                if(step > 1) {
                    this.$modal.show('quotes-step-'+(step-1));
                }
            },
            nextStep(event) {
                var modal = $(event.target).closest('div.modal-quotes'),
                    step = modal.data('step');
                if(step < 3) {
                    this.$modal.hide('quotes-step-'+step);
                    this.$modal.show('quotes-step-'+(step+1));
                } else {
                    var errors = false;
                    modal.find('.quotes-error').each(function() {
                        var fieldName = $(this).data('field');
                        if(modal.find('input[name="'+fieldName+'"]').val().length == 0) {
                            $(this).css('display', 'inline-flex');
                            errors = true;
                        }
                    });
                    if(!errors) {
                        this.$modal.hide('quotes-step-'+step);
                        //$('#quote-form').submit();
                        $.post({
                            url: '/send-quote',
                            data: $('#quote-form').serialize(),
                            dataType: 'json',
                        });
                        this.$modal.show('quotes-step-'+(step+1));
                    }
                }
            },
            quotesWhenChange(event) {
                $('#quotes-when-date').css('display', $(event.target).val() == 'date' ? 'block' : 'none');
            },
            quotesFieldChange(event) {
                var field = $(event.target),
                    errorText = $('.modal-quotes-shell .quotes-error[data-field="'+field.attr('name')+'"]');
                if(errorText.length > 0) {
                    errorText.css('display', field.val().length == 0 ? 'inline-flex' : 'none');
                }
            },

        }
    }
</script>

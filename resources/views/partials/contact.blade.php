<div class="container">
    <div class="detail-block">
        <div class="row content-title">
            <h1>{{ __('Contact Us') }}</h1>
        </div>
        <div class="content-body">
        	<div class="form-small">
                <form method="post" action="/contact-send-message">
                    <div class="form-group">
                        <select class="sort-select form-control" name="mr_mss" id="contact_mr_mss">
                            <option value="Mr">{{ __('Mr') }}</option>
                            <option value="Mrs">{{ __('Mrs') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="text" name="first_name" value="{{ old('first_name') }}" id="contact_first_name" placeholder="{{ __('First Name') }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <input type="text" name="last_name" value="{{ old('last_name') }}" id="contact_last_name" placeholder="{{ __('Last Name') }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <input type="text" name="company_name" value="{{ old('company_name') }}" id="contact_company_name" placeholder="{{ __('Company Name') }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <input type="text" name="phone" value="{{ old('phone') }}" id="contact_phone" placeholder="{{ __('Telephone') }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" value="{{ old('email') }}" id="contact_email" placeholder="{{ __('Email') }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <textarea name="message" id="contact_message" rows="4" class="form-control">{{ old('message') }}</textarea>
                    </div>
                    <div class="form-group">
                        <recaptcha :captcha="{{ json_encode($captcha) }}"></recaptcha>
                    </div>
                    <input type="hidden" name="_token" v-model="csrf">
                    <button type="submit" id="contact_btn" class="btn btn-secondary btn-block">{{ __('SEND MESSAGE') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 30);
            $table->string('title', 50);
            $table->tinyInteger('lang_id');
            $table->string('from_address', 50);
            $table->string('from_name', 50);
            $table->string('to_address', 50);
            $table->string('to_name', 50);
            $table->string('subject', 255);
            $table->text('greeting')->nullable();
            $table->text('body')->nullable();
            $table->text('signature')->nullable();
            $table->timestamps();
        });

        $templates = [
            [
                'name' => 'new_user',
                'title' => 'New User',
                'lang_id' => 37,
                'from_address' => '{admin_email}',
                'from_name' => '{admin_name}',
                'to_address' => '{admin_email}',
                'to_name' => '{admin_name}',
                'subject' => 'New User for {site_name}',
                'greeting' => 'New user registered on the site {site_name}.',
                'body' => 'Account username: {user_name}
Account e-mail: {user_email}
Account First Name: {user_first_name}
Account Last Name: {user_last_name}
Account Address: {map_address}
To approve please visit the following url: <a href="{approve_user}">{approve_user}</a>',
                'signature' => 'Thanks, {site_name}'
            ],
            [
                'name' => 'signup_user',
                'title' => 'Sign Up User',
                'lang_id' => 37,
                'from_address' => '{admin_email}',
                'from_name' => '{admin_name}',
                'to_address' => '{user_email}',
                'to_name' => '{user_first_name} {user_last_name}',
                'subject' => 'Thank you for signing up with {site_name}',
                'greeting' => 'Hi, {user_first_name}.',
                'body' => 'Your account is being verified.
Username: {user_name}
E-mail: {user_email}
Password: Your password
First Name: {user_first_name}
Last Name: {user_last_name}
Address: {map_address}

If you have any questions, please contact us at {admin_email}',
                'signature' => 'Thanks, {site_name}'
            ],
            [
                'name' => 'new_agent',
                'title' => 'New Agent',
                'lang_id' => 37,
                'from_address' => '{agency_email}',
                'from_name' => '{agency_name}',
                'to_address' => '{user_email}',
                'to_name' => '{user_first_name} {user_last_name}',
                'subject' => 'New {user_role} for {agency_name}',
                'greeting' => 'Hi, {user_first_name} {user_last_name}.',
                'body' => 'I registered you on the site {site_name} as an {user_role} for the {agency_name}.
Here is your registration information:
E-mail: {user_email}
Username: {user_name}
Password: {password}
To login please visit the following url: <a href="{login_url}">{login_url}</a>',
                'signature' => 'Thanks, {agency_name}'
            ],
            [
                'name' => 'approve_user',
                'title' => 'Approve User',
                'lang_id' => 37,
                'from_address' => '{admin_email}',
                'from_name' => '{admin_name}',
                'to_address' => '{user_email}',
                'to_name' => '{user_first_name} {user_last_name}',
                'subject' => 'Your account on {site_name} is now active',
                'greeting' => 'Hi, {user_first_name} {user_last_name}.',
                'body' => 'Thank you for signing up with {site_name}!
Your account has been approved and is now active.
To login please visit the following url: <a href="{login_url}">{login_url}</a>
Your account e-mail: {user_email}
Your account username: {user_name}
If you have any problems, please contact us at {admin_email}',
                'signature' => 'Thanks, {site_name}'
            ],
            [
                'name' => 'reject_user',
                'title' => 'Reject User',
                'lang_id' => 37,
                'from_address' => '{admin_email}',
                'from_name' => '{admin_name}',
                'to_address' => '{user_email}',
                'to_name' => '{user_first_name} {user_last_name}',
                'subject' => 'Your account has been rejected',
                'greeting' => 'Hi, {user_first_name} {user_last_name}.',
                'body' => 'Thank you for signing up with {site_name}!
We have reviewed your information and unfortunately we are unable to accept you as a member at this moment.
Please feel free to apply again at a future date.',
                'signature' => 'Thanks, {site_name}'
            ],
			[
                'name' => 'new_job',
                'title' => 'New Job',
                'lang_id' => 37,
                'from_address' => '{admin_email}',
                'from_name' => '{admin_name}',
                'to_address' => '{admin_email}',
                'to_name' => '{admin_name}',
                'subject' => 'New Job for {site_name}',
                'greeting' => 'Job was created.',
                'body' => 'Job: <a href="{entity_url}">{entity_title}</a>
To approve please visit the following url: <a href="{approved_job}">{approved_job}</a>',
                'signature' => 'Thanks, {site_name}'
            ],
			[
                'name' => 'approve_job',
                'title' => 'Approve Job',
                'lang_id' => 37,
                'from_address' => '{admin_email}',
                'from_name' => '{admin_name}',
                'to_address' => '{user_email}',
                'to_name' => '{user_first_name} {user_last_name}',
                'subject' => 'Your job on {site_name} was published',
                'greeting' => 'Hi, {user_first_name} {user_last_name}.',
                'body' => 'Your property <a href="{entity_url}">{entity_title}</a> was published on {site_name}
To edit the job please visit the following url: <a href="{edit_url}">{edit_url}</a>
If you have any problems, please contact us at {admin_email}',
                'signature' => 'Thanks, {site_name}'
            ],
			[
                'name' => 'job_send_message',
                'title' => 'Request info (job)',
                'lang_id' => 37,
                'from_address' => '{admin_email}',
                'from_name' => '{admin_name}',
                'to_address' => '{user_email}',
                'to_name' => '{user_first_name} {user_last_name}',
                'subject' => 'Request info from {site_name}',
                'greeting' => 'Hi, {user_first_name} {user_last_name}.',
                'body' => 'Job: <a href="{entity_url}">{entity_title}</a>
{message}',
                'signature' => 'Name: {from_name}
Phone: {from_phone}
Email: {from_email}'
            ],
            [
                'name' => 'new_property',
                'title' => 'New Property',
                'lang_id' => 37,
                'from_address' => '{admin_email}',
                'from_name' => '{admin_name}',
                'to_address' => '{admin_email}',
                'to_name' => '{admin_name}',
                'subject' => 'New Property for {site_name}',
                'greeting' => 'New Property was created.',
                'body' => 'Property: <a href="{entity_url}">{entity_title}</a>
To approve please visit the following url: <a href="{approved_property}">{approved_property}</a>',
                'signature' => 'Thanks, {site_name}'
            ],
            [
                'name' => 'approve_property',
                'title' => 'Approve Property',
                'lang_id' => 37,
                'from_address' => '{admin_email}',
                'from_name' => '{admin_name}',
                'to_address' => '{user_email}',
                'to_name' => '{user_first_name} {user_last_name}',
                'subject' => 'Your property on {site_name} was published',
                'greeting' => 'Hi, {user_first_name} {user_last_name}.',
                'body' => 'Your property <a href="{entity_url}">{entity_title}</a> was published on {site_name}
To edit the property please visit the following url: <a href="{edit_url}">{edit_url}</a>
If you have any problems, please contact us at {admin_email}',
                'signature' => 'Thanks, {site_name}'
            ],
            [
                'name' => 'property_send_message',
                'title' => 'Request info (property)',
                'lang_id' => 37,
                'from_address' => '{admin_email}',
                'from_name' => '{admin_name}',
                'to_address' => '{user_email}',
                'to_name' => '{user_first_name} {user_last_name}',
                'subject' => 'Request info from {site_name}',
                'greeting' => 'Hi, {user_first_name} {user_last_name}.',
                'body' => 'Property: <a href="{entity_url}">{entity_title}</a>
{message}',
                'signature' => 'Name: {from_name}
Phone: {from_phone}
Email: {from_email}'
            ],
            [
                'name' => 'new_franchise',
                'title' => 'New Franchise',
                'lang_id' => 37,
                'from_address' => '{admin_email}',
                'from_name' => '{admin_name}',
                'to_address' => '{admin_email}',
                'to_name' => '{admin_name}',
                'subject' => 'New Franchise for {site_name}',
                'greeting' => 'New Franchise was created.',
                'body' => 'Franchise: <a href="{entity_url}">{entity_title}</a>
To approve please visit the following url: <a href="{approved_franchise}">{approved_franchise}</a>',
                'signature' => 'Thanks, {site_name}'
            ],
            [
                'name' => 'approve_franchise',
                'title' => 'Approve Franchise',
                'lang_id' => 37,
                'from_address' => '{admin_email}',
                'from_name' => '{admin_name}',
                'to_address' => '{user_email}',
                'to_name' => '{user_first_name} {user_last_name}',
                'subject' => 'Your franchise on {site_name} was published',
                'greeting' => 'Hi, {user_first_name} {user_last_name}.',
                'body' => 'Your franchise <a href="{entity_url}">{entity_title}</a> was published on {site_name}
To edit the franchise please visit the following url: <a href="{edit_url}">{edit_url}</a>
If you have any problems, please contact us at {admin_email}',
                'signature' => 'Thanks, {site_name}'
            ],
            [
                'name' => 'franchise_send_message',
                'title' => 'Request info (franchise)',
                'lang_id' => 37,
                'from_address' => '{admin_email}',
                'from_name' => '{admin_name}',
                'to_address' => '{user_email}',
                'to_name' => '{user_first_name} {user_last_name}',
                'subject' => 'Request info from {site_name}',
                'greeting' => 'Hi, {user_first_name} {user_last_name}.',
                'body' => 'Franchise: <a href="{entity_url}">{entity_title}</a>
{message}',
                'signature' => 'Name: {from_name}
Phone: {from_phone}
Email: {from_email}'
            ],
            [
                'name' => 'agent_send_message',
                'title' => 'Contact Agent',
                'lang_id' => 37,
                'from_address' => '{admin_email}',
                'from_name' => '{admin_name}',
                'to_address' => '{user_email}',
                'to_name' => '{user_first_name} {user_last_name}',
                'subject' => 'Contact Agent from {site_name}',
                'greeting' => 'Hi, {user_first_name} {user_last_name}.',
                'body' => '{message}',
                'signature' => 'Name: {from_name}
Phone: {from_phone}
Email: {from_email}'
            ],
            [
                'name' => 'get_quotes',
                'title' => 'Get Quotes',
                'lang_id' => 37,
                'from_address' => '{admin_email}',
                'from_name' => '{admin_name}',
                'to_address' => '{admin_email}',
                'to_name' => 'Quotes Admin',
                'subject' => 'Get Quotes from {site_name}',
                'greeting' => '',
                'body' => '
What: {quote_what}
Where: {quote_where}
When: {quote_when} {quote_date}
Budget: {quote_budget}
About: {quote_about}
First Name: {quote_fname}
Last Name: {quote_lname}
Email: {quote_email}
Phone: {quote_phone}',
                'signature' => ''
            ],
			[
                'name' => 'send_quotes_request',
                'title' => 'New quotes request',
                'lang_id' => 37,
                'from_address' => '{admin_email}',
                'from_name' => '{admin_name}',
                'to_address' => '{user_email}',
                'to_name' => '{user_name}',
                'subject' => 'New quotes request {site_name}',
                'greeting' => '',
                'body' => '{info} {link}',
                'signature' => ''
            ],
            [
                'name' => 'contact_us',
                'title' => 'Contact Us',
                'lang_id' => 37,
                'from_address' => '{admin_email}',
                'from_name' => '{admin_name}',
                'to_address' => '{admin_email}',
                'to_name' => '{admin_name}',
                'subject' => 'Contact Us from {site_name}',
                'greeting' => '',
                'body' => '{message}',
                'signature' => 'Mr/Mss: {mr_mss}
First Name: {first_name}
Last Name: {last_name}
Company Name: {company_name}
Phone: {phone}
Email: {email}'
            ],
            [
                'name' => 'new_art',
                'title' => 'New Art',
                'lang_id' => 37,
                'from_address' => '{admin_email}',
                'from_name' => '{admin_name}',
                'to_address' => '{admin_email}',
                'to_name' => '{admin_name}',
                'subject' => 'New Art for {site_name}',
                'greeting' => 'New Art was created.',
                'body' => 'Art: <a href="{entity_url}">{entity_title}</a>
To approve please visit the following url: <a href="{approved_property}">{approved_property}</a>',
                'signature' => 'Thanks, {site_name}'
            ],
            [
                'name' => 'approve_art',
                'title' => 'Approve Art',
                'lang_id' => 37,
                'from_address' => '{admin_email}',
                'from_name' => '{admin_name}',
                'to_address' => '{user_email}',
                'to_name' => '{user_first_name} {user_last_name}',
                'subject' => 'Your art on {site_name} was published',
                'greeting' => 'Hi, {user_first_name} {user_last_name}.',
                'body' => 'Your art <a href="{entity_url}">{entity_title}</a> was published on {site_name}
To edit the art please visit the following url: <a href="{edit_url}">{edit_url}</a>
If you have any problems, please contact us at {admin_email}',
                'signature' => 'Thanks, {site_name}'
            ],
            [
                'name' => 'art_send_message',
                'title' => 'Request info (art)',
                'lang_id' => 37,
                'from_address' => '{admin_email}',
                'from_name' => '{admin_name}',
                'to_address' => '{user_email}',
                'to_name' => '{user_first_name} {user_last_name}',
                'subject' => 'Request info from {site_name}',
                'greeting' => 'Hi, {user_first_name} {user_last_name}.',
                'body' => 'Art: <a href="{entity_url}">{entity_title}</a>
{message}',
                'signature' => 'Name: {from_name}
Phone: {from_phone}
Email: {from_email}'
            ],
        ];

        foreach($templates as $t) {
            $template = new EmailTemplate();
            $template->fill($t);
            $template->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_templates');
    }
}

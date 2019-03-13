<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require 'vendor/autoload.php';
use Mailgun\Mailgun;

class eemgforms {
	public static $name         = 'EE Mailgun Forms';
	public static $version      = '0.1.0';
	public static $author       = 'Dennis Wyman';
	public static $author_url   = 'https://denniswyman.com';
	public static $description  = "An ExpressionEngine addon for building mail forms that use Mailgun";
	public static $typography   = FALSE;
    
	public $return_data = '';

	public function __construct()
	{
		// Fetch, sanitize, and validate, connection settings for SMTP server.
		$mgf_domain_pre	= ee('Security/XSS')->clean(ee()->TMPL->fetch_param('domain'));
		$mgf_domain	 	= filter_var($mgf_domain_pre, FILTER_SANITIZE_URL);
		$mgf_key		= ee('Security/XSS')->clean(ee()->TMPL->fetch_param('key'));
		$mgf_user_pre	= ee('Security/XSS')->clean(ee()->TMPL->fetch_param('user'));
		$mgf_user 		= filter_var($mgf_user_pre, FILTER_SANITIZE_EMAIL);
		$mgf_to_pre		= ee('Security/XSS')->clean(ee()->TMPL->fetch_param('to'));
		$mgf_to 		= filter_var($mgf_to_pre, FILTER_SANITIZE_EMAIL);
		$mgf_subject 	= ee('Security/XSS')->clean(ee()->TMPL->fetch_param('subject', 'Contact Form Submission'));
		$mgf_segment	= ee('Security/XSS')->clean(ee()->TMPL->fetch_param('segment'));
		$rc_site		= ee('Security/XSS')->clean(ee()->TMPL->fetch_param('rc_site'));
		$rc_secret		= ee('Security/XSS')->clean(ee()->TMPL->fetch_param('rc_secret'));

		// Check that our params are filled in and valid.
		if ((empty($mgf_domain)) ||
		(empty($mgf_key)) ||
		(empty($mgf_user)) ||
		(empty($mgf_to)) ||
		(! filter_var($mgf_user, FILTER_VALIDATE_EMAIL)) ||
		(! filter_var($mgf_to, FILTER_VALIDATE_EMAIL)) ||
		(empty($mgf_segment)) ||
		(empty($rc_site)) ||
		(empty($rc_secret)))
		{
			// Any hiccups? Initialize the logging library and spit out fatal errors.
			ee()->load->library('logger');
			if (empty($mgf_domain))
			{
				$mgf_domain_log = 'Mailgun Forms error: Parameter "domain" cannot be left blank!';
				ee()->logger->developer($mgf_domain_log, TRUE, 86400);
				ee()->output->fatal_error($mgf_domain_log);
			}
			if (empty($mgf_key))
			{
				$mgf_key_log = 'Mailgun Forms error: Parameter "key" cannot be left blank!';
				ee()->logger->developer($mgf_key_log, TRUE, 86400);
				ee()->output->fatal_error($mgf_key_log);
			}
			if (empty($mgf_user))
			{
				$mgf_user_log = 'Mailgun Forms error: Parameter "user" cannot be left blank!';
				ee()->logger->developer($mgf_user_log, TRUE, 86400);
				ee()->output->fatal_error($mgf_user_log);
			}
			if (empty($mgf_to))
			{
				$mgf_to_log = 'Mailgun Forms error: Parameter "to" cannot be left blank!';
				ee()->logger->developer($mgf_to_log, TRUE, 86400);
				ee()->output->fatal_error($mgf_to_log);
			}
			if (! filter_var($mgf_user, FILTER_VALIDATE_EMAIL))
			{
				$mgf_user_log = 'Mailgun Forms error: Provided parameter "user" contains an invalid email address.';
				ee()->logger->developer($mgf_user_log, TRUE, 86400);
				ee()->output->fatal_error($mgf_user_log);
			}
			if (! filter_var($mgf_to, FILTER_VALIDATE_EMAIL))
			{
				$mgf_to_log = 'Mailgun Forms error: Provided parameter "to" contains an invalid email address.';
				ee()->logger->developer($mgf_to_log, TRUE, 86400);
				ee()->output->fatal_error($mgf_to_log);
			}
			if (empty($mgf_segment))
			{
				$mgf_segment_log = 'Mailgun Forms error: Parameter "segment" cannot be left blank!';
				ee()->logger->developer($mgf_segment_log, TRUE, 86400);
				ee()->output->fatal_error($mgf_segment_log);
			}
			if (empty($rc_site))
			{
				$rc_site_log = 'Pearmail error: Provided parameter "rc_secret" cannot be left blank!';
				ee()->logger->developer($rc_site_log, TRUE, 86400);
				ee()->output->fatal_error($rc_site_log);
			}
			if (empty($rc_secret))
			{
				$rc_secret = 'Pearmail error: Provided parameter "rc_secret" cannot be left blank!';
				ee()->logger->developer($rc_secret, TRUE, 86400);
				ee()->output->fatal_error($rc_secret);
			}
		}

		// Load the form helper and define params for form_declaration.
		ee()->load->helper('form');
		$form_details = array(
			'action'		=> $mgf_segment,
			'name'			=> 'form',
			'id'			=> ee()->TMPL->form_id,
			'class'			=> ee()->TMPL->form_class,
			'hidden_fields'	=> array('new' => 'y'),
			'secure'		=> TRUE,
			'onsubmit'		=> "validate_form(); return false;"
		);

		// Declare and populate form.
		$mgf_form .= '<script src="https://www.google.com/recaptcha/api.js" async defer></script>
			'.ee()->functions->form_declaration($form_details).'
			<div class="form-group row">
			<label for="inputName" class="col-sm-3 col-form-label">Name</label>
			<div class="col-sm-9">'.form_input('msg_name', ee()->input->post('msg_name', TRUE), 'class="form-control" id="inputName"').'</div>
			</div>
			<div class="form-group row">
			<label for="inputEmail" class="col-sm-3 col-form-label">E-mail</label>
			<div class="col-sm-9">'.form_input('msg_email', ee()->input->post('msg_email', TRUE), 'class="form-control" id="inputEmail"').'</div>
			</div>
			<div class="form-group row">
			<label for="inputBody" class="col-sm-3 col-form-label">Message</label>
			<div class="col-sm-9">'.form_textarea('msg_body', ee()->input->post('msg_body', TRUE), 'class="form-control" id="inputBody" rows="6"').'</div>
			</div>
			<div class="form-group row">
			<div class="col-sm-3"></div>
			<div class="col-sm-9"><div class="g-recaptcha" data-sitekey="'.$rc_site.'"></div></div>
			</div>
			<div class="form-group row">
			<div class="col-sm-3"></div>
			<div class="col-sm-9">'.form_submit('msg_submit', 'Submit', 'class="btn btn-primary"').'</div>
			</div>
			</form>';

		// Spit out form in template.
		$this->return_data .= $mgf_form;

		// What to do after form submission.
		if (ee()->input->post('msg_submit', TRUE) === 'Submit')
		{
			// Check to see if the reCAPTCHA was left empty before progressing.
			$rc_response = ee()->input->post('g-recaptcha-response', TRUE);
			if (!empty($rc_response))
			{
				// Verify reCAPTCHA response with Google.
				$rc_verify = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$rc_secret.'&response='.$rc_response);
				$rc_captcha_success = json_decode($rc_verify);
				if ($rc_captcha_success->success == FALSE)
				{
					// Did reCAPTCHA validation fail?
					$this->return_data = '<div class="alert alert-danger" role="alert">CAPTCHA validation failure!</div>';
				}
				else if ($rc_captcha_success->success == TRUE)
				{
					// Continue if it didn't. Fetch and sanitize form input.
					$msg_name		= ee()->input->post('msg_name', TRUE);
					$msg_replyto	= ee()->input->post('msg_email', TRUE);
					$msg_body 		= ee()->input->post('msg_body', TRUE);

					// Fetch visitor data via EE's Input class.
					$mgf_ip 		= ee()->input->ip_address();
					$mgf_agent		= ee()->input->user_agent();

					// Prepare message to be sent.
					$msg_full = $msg_body.'<br>
						<br>
						<br>
						<b>Sender Info:</b><br>'.$msg_name.'<br>'.$msg_replyto.'<br>'.$mgf_ip.'<br>'.$mgf_agent;

					// Instantiate the Mailgun SDK with provided API credentials.
					$mg = Mailgun::create($mgf_key);

					// Make the call to the Mailgun API with prepared data.
					$mg->messages()->send($mgf_domain, [
					  'sender'    	=> $mgf_user,
					  'to'      	=> $mgf_to,
					  'subject' 	=> $mgf_subject,
					  'from'		=> $msg_name.' <'.$msg_replyto.'>',
					  'html'    	=> $msg_full
					]);

				}
			}
			else
			{
				// But was the reCAPTCHA left empty?
				$this->return_data .= '<div class="alert alert-danger" role="alert">Please fill in the CAPTCHA!</div>';
			}
		}
	}
}

/* End of file pi.eemgforms.php */
/* Location: ./system/user/addons/eemgforms/pi.eemgforms.php */
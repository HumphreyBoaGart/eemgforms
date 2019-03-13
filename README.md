# EE Mailgun Forms
An alternative contact form solution using the Mailgun API and reCAPTCHA v2. Include the tag with required parameters in any template to generate a form.

If you wanna edit the form that spits out in the front-end, you'll have to edit `pi.mailgunforms.php` manually. I'll have a better system in place for this (and a simplified installation process) before the 1.0 release.

## Requirements
- ExpressionEngine 5 (might work in versions 3/4)
- [Composer](https://yarnpkg.com/lang/en/docs/install/#windows-stable)
- [Bootstrap](https://getbootstrap.com) (Optional. You can define your own CSS classes, but the ones included with the default form are designed to work with Bootstrap out of the box)

## Installation
Go to the root project directory of your ExpressionEngine in Bash and run the following commands:

1. `mkdir system/user/addons/eemgforms`
2. `cd system/user/addons/eemgforms` 
3. `composer install`

Then install the Mailgun Forms add-on via the ExpressionEngine Add-On Manager.

## Usage

### {exp:eemgforms}
This tag will generate the form in your templates.

#### Example Usage
Simply fill in the tag with your Mailgun API credentials, reCAPTCHA v2 keys, and recipient information.

```
{exp:eemgforms domain="mailgun.domain.com" key="apikey" user="postmaster@mailgun.domain.com" to="recipient@domain.com" subject="Contact Form Submission" rc_site="publickey" rc_secret="privatekey"}
```

#### Parameters

##### domain (*required*)
The address of the domain configured in Mailgun.

##### key (*required*)
Your Mailgun API key.

##### user (*required*)
The default SMTP login of the domain configured in Mailgun

##### to (*required*)
The recipient address form submissions get sent to.

##### segment (*required*)
The last URI segment of the page serving the form.

##### subject
The subject line form submissions have when they arrive at the recipient address. Defaults to `Contact Form Submission` if left undefined.

##### rc_site (*required*)
Your reCAPTCHA v2 public or site key.

##### rc_secret (*required*)
Your reCAPTCHA v2 private or secret key.


## Changelog

### 0.1
- Initial release.
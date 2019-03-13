# Notes
* Don't worry about integration with the built-in EE mailer or contact form functions, that should/will be its own plugin. This is specifically a lightweight bypass.

# Publication
* Simplify + update installation instructions in README.md
* Tags for Github repo

# Bugs
* Have it not spit out errors below the form
* After a successful send, have form replaced by success message

# Code Cleanliness
* Error handling needs more dynamic solution
* Synchronize public static variables with addon.setup.php
* Form submissions should be handled by a separate private function and not the constructor

# Advanced Features
* Make it easier for end users to customize form fields and form html
* Other captcha options, including recaptcha v3 and no captcha
* Set up central configuration point that isn't plaintext tag params
* Pull URL segment dynamically instead of having to declare it in the tag

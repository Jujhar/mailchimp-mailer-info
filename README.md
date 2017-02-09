# MailChimp Mailer Info
Track MailChimp statistics for a specific email account.
View open dates, click dates, or bounced dates from all your campaigns.
Ability to view the exact email sent.
Option store to MySQL database.

### Screenshot
<img src="https://github.com/Jujhar/mailchimp-mailer-info/blob/master/screenshot-1.png" alt="screenshot" style="width: 80%;"/>

### Instructions
For a batch command append desired email address to the url (?email=example@ex.com),
like so site.com/cron/mailchimp-mailer-info.php?email=example@ex.com.

### Installation
Enter configuration options in the top of mailchimp-mailer-info.php file.
If you want to save to a database run the install.sql script and enable the relevant configuration  variable.
You can also enter the email to query from the index.html file as opposed to entering it in the url get parameter.

### Options
**display_activity_open_multiple**
If disabled it will create new Opens column displaying number of opens rather than display each on its own with its open date. It will also display only the first open date.
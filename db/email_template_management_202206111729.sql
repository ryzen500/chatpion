INSERT INTO chatpion.email_template_management (title,template_type,subject,message,icon,tooltip,info) VALUES
	 ('Signup Activation','signup_activation','#APP_NAME# | Account Activation','<p>To activate your account please perform the following steps :</p>
<ol>
<li>Go to this url : #ACTIVATION_URL#</li>
<li>Enter this code : #ACCOUNT_ACTIVATION_CODE#</li>
<li>Activate your account</li>
</ol>','fas fa-skating','#APP_NAME#,#ACTIVATION_URL#,#ACCOUNT_ACTIVATION_CODE#','When a new user open an account'),
	 ('Reset Password','reset_password','#APP_NAME# | Password Recovery','<p>To reset your password please perform the following steps :</p>
<ol>
<li>Go to this url : #PASSWORD_RESET_URL#</li>
<li>Enter this code : #PASSWORD_RESET_CODE#</li>
<li>reset your password.</li>
</ol>
<h4>Link and code will be expired after 24 hours.</h4>','fas fa-retweet','#APP_NAME#,#PASSWORD_RESET_URL#,#PASSWORD_RESET_CODE#','When a user forget login password'),
	 ('Change Password','change_password','Change Password Notification','Dear #USERNAME#,<br/> 
Your <a href="#APP_URL#">#APP_NAME#</a> password has been changed.<br>
Your new password is: #NEW_PASSWORD#.<br/><br/> 
Thank you,<br/>
<a href="#APP_URL#">#APP_NAME#</a> Team','fas fa-key','#APP_NAME#,#APP_URL#,#USERNAME#,#NEW_PASSWORD#','When admin reset password of any user'),
	 ('Subscription Expiring Soon','membership_expiration_10_days_before','Payment Alert','Dear #USERNAME#,
<br/> Your account will expire after 10 days, Please pay your fees.<br/><br/>
Thank you,<br/>
<a href="#APP_URL#">#APP_NAME#</a> Team','fas fa-clock','#APP_NAME#,#APP_URL#,#USERNAME#','10 days before user subscription expires'),
	 ('Subscription Expiring Tomorrow','membership_expiration_1_day_before','Payment Alert','Dear #USERNAME#,<br/>
Your account will expire tomorrow, Please pay your fees.<br/><br/>
Thank you,<br/>
<a href="#APP_URL#">#APP_NAME#</a> Team','fas fa-stopwatch','#APP_NAME#,#APP_URL#,#USERNAME#','1 day before user subscription expires'),
	 ('Subscription Expired','membership_expiration_1_day_after','Subscription Expired','Dear #USERNAME#,<br/>
Your account has been expired, Please pay your fees for continuity.<br/><br/>
Thank you,<br/>
<a href="#APP_URL#">#APP_NAME#</a> Team','fas fa-user-clock','#APP_NAME#,#APP_URL#,#USERNAME#','Subscription is already expired of a user'),
	 ('Paypal Payment Confirmation','paypal_payment','Payment Confirmation','Congratulations,<br/> 
We have received your payment successfully.<br/>
Now you are able to use #PRODUCT_SHORT_NAME# system till #CYCLE_EXPIRED_DATE#.<br/><br/>
Thank you,<br/>
<a href="#SITE_URL#">#APP_NAME#</a> Team','fab fa-paypal','#APP_NAME#,#CYCLE_EXPIRED_DATE#,#PRODUCT_SHORT_NAME#,#SITE_URL#','User pay through Paypal & gets confirmation'),
	 ('Paypal New Payment','paypal_new_payment_made','New Payment Made','New payment has been made by #PAID_USER_NAME#','fab fa-cc-paypal','#PAID_USER_NAME#','User pay through Paypal & admin gets notified'),
	 ('Stripe Payment Confirmation','stripe_payment','Payment Confirmation','Congratulations,<br/>
We have received your payment successfully.<br/>
Now you are able to use #APP_SHORT_NAME# system till #CYCLE_EXPIRED_DATE#.<br/><br/>
Thank you,<br/>
<a href="#APP_URL#">#APP_NAME#</a> Team','fab fa-stripe-s','#APP_NAME#,#CYCLE_EXPIRED_DATE#,#PRODUCT_SHORT_NAME#,#SITE_URL#','User pay through Stripe & gets confirmation'),
	 ('Stripe New Payment','stripe_new_payment_made','New Payment Made','New payment has been made by #PAID_USER_NAME#','fab fa-cc-stripe','#PAID_USER_NAME#','User pay through Stripe & admin gets notified');
INSERT INTO chatpion.email_template_management (title,template_type,subject,message,icon,tooltip,info) VALUES
	 ('Ecommerce Order Received','emcommerce_sale_admin','#STORE_NAME# | A New Order Has Been Submitted','Congratulations,<br/>
You have got an new order on your store #STORE_NAME#.<br>
Invoice : #INVOICE_URL# <br/><br/>

Thank you,<br/>
<a href="#APP_URL#">#APP_NAME#</a> Team','fas fa-dollar-sign','#STORE_NAME#,#APP_URL#,#APP_NAME#,#INVOICE_URL#','You have got an new order on your store'),
	 ('New Webview Form Submission Alert','webview_form_submit_admin','#FORM_TITLE# | #SUBSCRIBER_NAME# Has Submitted Form','#SUBSCRIBER_NAME# has just submitted your form #FORM_TITLE# with below data. <br/><br/>
#FORM_DATA#
<br/><br/>
Thank you,<br/>
<a href="#APP_URL#">#APP_NAME#</a> Team','fas fa-digital-tachograph','#FORM_TITLE#,#SUBSCRIBER_NAME#,#SUBSCRIBER_NAME#,#FORM_TITLE#,#APP_URL#,#APP_NAME#','Subscriber information received');

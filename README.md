# EmailBirthday

## Description

EmailBirthday is a Joomla! plugin used to send an email to all registered users who are on birthday. It works on Joomla 2.5.
To use it you shall to modify the user  profile file (/plugins/user/profile/profiles/profile.xml) and add the next:

<field name="dob" type="calendar" label="PLG_USER_PROFILE_FIELD_DOB_LABEL" description="PLG_USER_PROFILE_FIELD_DOB_DESC" />

Additionally, you can modify the language file adding PLG_USER_PROFILE_FIELD_DOB_LABEL and PLG_USER_PROFILE_FIELD_DOB_DESC.
The language file is in /administrator/language/aa-AA/aa-AA.plg_user_profile.ini.

## Custom message

It can be customized the email message on the plugin parameters:
You can put the user name on the message (adding the label {name}) and add an image on the email signature (adding the image between {image}path/to/image{/image})

## Scheduled task

This plugins send automatic email when it runs over Windows System. An Scheduled Task have to be added to execute the PHP script: /plugins/system/emailbirthday/message.php

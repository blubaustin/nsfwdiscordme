Admins
======
Logging into the admin site requires two-factor authentication using the Google Authenticator app. Available in the [Play Store](https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=en_US) and [iTunes store](https://itunes.apple.com/us/app/google-authenticator/id388497605?mt=8).

### Creating Administrators

* Be sure the user has already logged into the site via the "Log in with Discord" system.
* Use the command `bin/console app:user:role-add`. You will be prompted for the user's Discord email address or ID, and the role to add to their account. Enter "ROLE_ADMIN". The user should now log out of their account and log back in.
* The command line script prints a URL for a QR code the user must scan with their Google Authenticator app.
* The user can log in as an admin from https://nsfwdiscord.me/admin/login. They will be prompted to enter the code displayed on the Google Authenticator app.

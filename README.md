Install
=======

Symfony
-------
```shell script
composer install
cp .env .env.local
```

Google Auth
-----------
### Recommended way
* Create a new Firebase project on the [Firebase console](https://console.firebase.google.com)
* In the `Authentication` menu, choose the `Sign-in method` tab and enable the `Google` provider.
* You will see the associated `Web client id` (`GOOGLE_CLIENT_ID`) and 
`Web client secret` (`GOOGLE_CLIENT_SECRET`), which you need to put into your
`.env.local` file to the corresponding variables.
* After this when you try to sign in with google, an error will arise about the redirect url.
Follow the link to the Google cloud console and add the redirect to the `Authorized redirect URIs` list
for your web client API key. The correct redirect url is in the error message too (`http://yoururl/login-check`)
* On this admin (Google Cloud Console), you will have to create a `Service account` key for Firebase.
In the `Credentials` menu, press the `Create credentials` dropdown button and choose `Service account key`.
Choose a service account (if you created the project from firebase, then you will already have one)
and create a `JSON` key. Put the json into the local `var` folder and name it exactly `firebase-service-account.json`.
Or if you use Google App Engine, you can use the [autodiscovery](https://firebase-php.readthedocs.io/en/4.32.0/setup.html#with-autodiscovery)
feature.

### Separate Firebase and Google projects (advanced)
If you create a separate Firebase and separate Google Cloud project, then you will have to
add your Google project's client ID to the Firebase Google Login client id whitelist on the
Firebase console (Credentials > Sign-In Methods > Google)!

Facebook Auth
-------------
* Create a new app in the [Facebook Developers](https://developers.facebook.com) page. The app can stay in sandbox mode.
* Enable facebook login, and copy the `App ID` (`FACEBOOK_APP_ID`) and `App Secret` (`FACEBOOK_SECRET`)
into the `.env.local` file's corresponding variables
* In the `Facebook Login` admin, add the redirect url: `https://yoursite/facebook-callback`. It must be https, otherwise
it won't work sadly!
* Enable Facebook login in the Firebase console: Authentication > Sign-in method > Facebook.

Where to look
=============

The most interesting part is in the [Security](./src/Security) folder.

There is a common Symfony [security user](./src/Security/FirebaseUser.php), which is the same for
Google and Facebook login. It holds the LinkProviderData and the UserRecord data so they are serialized
to the session and are available on every request.

The login happens in the [Guard](./src/Security/Guard) classes, where `linkProviderThroughAccessToken` and 
`linkProviderThroughIdToken` are called. Before that, the official SDK-s are used for log in.

After a successful login, the [UserProvider](./src/Security/UserProvider/FirebaseUserProvider.php) checks (verifies)
the firebase ID token on every request, which originated from the `linkProviderThrough*` calls.

On logout, we try to revoke all refresh tokens in the [LogoutSuccessHandler](./src/Security/LogoutSuccessHandler.php),
that's all what Firebase can do sadly.

If the login was successful, the logged in User's data is dumped and it is possible to log out.

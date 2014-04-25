> *NOTE:* This package is dead... Instead, check out Laravel's Cashier package at [https://github.com/laravel/cashier](https://github.com/laravel/cashier)

# Saas Frame

[![Build Status](https://travis-ci.org/adamgoose/saasframe.png?branch=master)](https://travis-ci.org/orchdork10159/saasframe)

Saas Frame (saasframe) is a bootstrapped SaaS that manages the interaction between your application and Stripe's payment gateway.

## Installation

Since Saas Frame is available on packagist, it's really easy to get started. To install Saas Frame, include the following line in your `composer.json` file.

    "adamgoose/saasframe": "dev-master"

Then, run `composer update` in your working directory.

Next, we will configure some of the dependencies. Saas Frame integrates with [Sentry](http://docs.cartalyst.com/sentry-2), a powerful user authentication/permission system. Sentry has now already been installed for you, but you need to run its migrations. Once you've configured `/app/config/database.php` with your database credentials, run the following command in your working directory.

    php artisan migrate --package=cartalyst/sentry

Next, we'll make some modifications to Sentry's migrations.

    php artisan migrate --package=adamgoose/saasframe

It'll also be important for you to take a look at some of the configuration options provided by both Sentry and Saas Frame.

    php artisan config:publish --package=cartalyst/sentry
    php artisan config:publish --package=adamgoose/saasframe

Once you've done this, check out `/app/config/packages/cartalyst/sentry/config.php` and `/app/config/packages/adamgoose/saasframe/config.php` to set up Saas Frame. We'll talk about these configuration options in a minute.

You'll also need to add some information to your `/app/config/app.php` file. Simply add the following lines to your `providers` array.

    'Adamgoose\Saasframe\SaasframeServiceProvider',
    'Cartalyst\Sentry\SentryServiceProvider',

You'll also want to be able to access Sentry's class through the Class Alias feature of Laravel. Add the following line to the `aliases` array.

    'Sentry' => 'Cartalyst\Sentry\Facades\Laravel\Sentry',

Now, we just need to make sure that everything's configured properly.

## Configuration

You can configure Sentry however you'd like. Head over to [the Sentry documetation](http://docs.cartalyst.com/sentry-2) for more information. Here, we'll just discuss what's available for you to configure in Saas Frame.

The `route` option lets you decide where you would like the automation of Saas Frame to occur. This is where you will point your Stripe webhooks. For example, if the `route` configuration is set to 'saasframe', you should point your Stripe webhooks to 'http://yourdomain.com/saasframe'.

> Keep in mind that Stripe requires the https:// protocol for production webhooks. However, http:// is just fine for development.

You'll also need to give Saas Frame your Stripe API keys. If you don't know where to get these, head over to [the Stripe Documetation](https://stripe.com/docs/tutorials/dashboard#api-keys) for more information.

The `plans` array is where the magic happens. By configuring this array properly, the automation of your SaaS can really flourish. Here, you will establish a relationship between your (Stripe Plans)[https://stripe.com/docs/subscriptions] with various levels of access throughout your application. When you relate a Stripe Plan with a [Sentry Permission Group](http://docs.cartalyst.com/sentry-2/groups), users who are subscribed to a particular plan are automatically added to that Sentry Group. This allows you to immediatley grant access to paid features of your application to those who subscribe to the plan via Stripe. All you have to do is tell Stripe to subscribe a particular customer to a plan to start the billing process.

> Guess what! We have already loaded the Stripe API for you, AND set your Stripe API keys. All you have to do is call any Stripe API method to access and manage your Stripe account. Read more about the [Stripe API](https://stripe.com/docs/api). Remember, you won't need to call `require_once('./lib/Stripe.php')` or `Stripe::setApiKey()`.

Finally, you can control the email behavior of Saas Frame. Stripe will automatically send a webhook to Saas Frame three days before a user's subscription trial is about to end. Saas Frame will automatically process this webhook to send an email to your user.

That's it! You're all set! Now let's take a look at how Saas Frame can help you build your Software as a Service.

## Usage

Saas Frame is great for managing various levels of subscription in your app. All you have to do is update the `plans` array in `/app/config/packages/adamgoose/saasframe/config.php`, and you'll be up and running.

In order to take advantage of this feature, you'll have to build your application around [Sentry](http://docs.cartalyst.com/sentry-2)'s permission management. The [Sentry Documentation](http://docs.cartalyst.com/sentry-2) can walk you through how to grant and test for various permissions throughout your application.

What Saas Frame has done for you, is automated the granting/revoking of these permissions based on the user's payment plan. When you use the [Stripe API](https://stripe.com/docs/api) to create payment subscriptions for a user, Stripe automatically sends a webhook to Saas Frame. Saas Frame then comes in, handles that request, and grants or revokes a user various permissions in your app. 

For example, if you subscribe a user to a plan using the [Stripe API](https://stripe.com/docs/api), the billing cycle starts for that User. Saas Frame will automatically add said user to that plan's corresponding Sentry Group. This, theoretically, will immediately give your user access to the extended features that particular plan provides.

Likewise, when a user cancelles a subscription, the user is automatically removed from that permission group.

> It is really important that you manage the relationship between Stripe Plan IDs (which are manually defined in Stripe) and Sentry Group IDs correctly in your `/app/config/packages/adamgoose/saasframe/config.php` file.

## Extending Saas Frame

If you'd like to add functionality to Saas Frame, you can extend `Adamgoose\Saasframe\Controllers\WebhookController` and add methods for handling other webhooks that Saas Frame doesn't already handle for you.

Saas Frame is intelligent enough to handle these requests automatically for you, if the methods of the `WebhookController` are defined. Since the [Stripe API](https://stripe.com/docs/api) send pre-defined webhook each time an event is triggerd, you can follow this syntax to extend the `WebhookController`.

If you'd like to add handling of the `customer.discount.updated` webhook, for example, all you'd have to do is add the method to your extension of `WebhookController` like this:

    private function customerDiscountCreated()
    {
      // handle webhook...
    }

The following variables are defined for you at the initialization of the `WebhookController`:

* `$this->body` - the raw HTTP body request sent to the controller
* `$this->event` - the particular object that the webhook is referencing
* `$this->type` - the name of the webhook type

If you need, for any reason, to render the name of the method that handles a particualr webhook, simply run the following method:

    $this->parseMethodName($webhookName);

However, know that this is handeled for you when the `WebhookController` is called.

## Contributing

Since Saas Frame is in active development, it would be amazing if you'd like to contribute to the Saas Frame repository. Feel free to fork it today and submit a pull request when you're done. Please try to keep [psr-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) standards in mind.

## To Do:

* Proof-read this README.md. lol
* Syncronize the local user email and the Stripe customer's email
* Error Logging
* Helper Classes for managing users and subscriptions
* Anything else? [Submit an issue](https://github.com/orchdork10159/saasframe/issues/new).

<?php

return array(

  'route' => 'saasframe',

  'webhook_controller' => 'Adamgoose\Saasframe\Controllers\WebhookController',

  'stripe' => array(

    'secret' => 'sk_test_5fmOjL2x9phXmyeXAn3Ac7S1',
    'publishable' => 'pk_test_JoDZqr2X1tpZ66CyUWcTfNfb',

  ),

  /*
  |--------------------------------------------------------------------------
  | Plans
  |--------------------------------------------------------------------------
  |
  | This array should exist in the following format:
  |   '[stripe_plan_id]' => [sentry_group_id], // [Plan Name] (optional)
  | When a subscription is created for a user, the webhook handler will 
  | automatically assign that particular user (by customer ID) to its 
  | corresponding Sentry group. This will grant them access to only the
  | features enabled in that group.
  |
  | The 'default' key in this array will be used in the case that the Stripe 
  | plan ID is not defined in this array.
  |
  */

  'plans' => array(

    'default' => 1, // Default
    '1' => 2, // Basic

  ),

  /*
  |--------------------------------------------------------------------------
  | Email
  |--------------------------------------------------------------------------
  |
  | These variables will help populate the email sent to users when the 
  | customer.subscription.trial_will_end webhook is sent, which is three
  | days before the end of their trial.
  |
  | The subscription object will be passed to the email view. See a list of 
  | available information at https://stripe.com/docs/api#subscriptions
  |
  */

  'email' => array(

    'method' => 'send', // which Mail:: method to use: 'send' or 'queue'
    'view' => 'saasframe::emails.trialWillEnd',
    'subject' => 'Your Trial Will End in Three Days',
    'from' => array(

      /**
       * Set these to null to use the Laravel defaults.
       */
      'address' => null,
      'name' => null,

    ),

  ),

);
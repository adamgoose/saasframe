<?php namespace Adamgoose\Saasframe\Controllers;

use Config;
use Controller;
use Mail;
use Sentry;
use Stripe_Event;
use Stripe_Customer;

class WebhookController extends Controller
{

  // Request Body
  private $body = '';

  // Request Event
  private $event = '';

  // Event Type
  private $type = '';

  /**
   * Handle and route incoming requests
   *
   * @return string
   */
  public function postIndex()
  {
    $this->body = json_decode(file_get_contents('php://input'));
    
    $this->type = $this->body->type;
    
    if ($this->body->livemode)
    {
      $this->event = Stripe_Event::retrieve(array('id' => $this->body->id, 'expand' => array('customer')))->data->object;
    }
    else
    {
      $this->event = $this->body->data->object;
    }

    $method = $this->parseMethodName($this->type);
    return $this->{$method}();

  }

  /**
   * Parse event type into method name
   *
   * @return string
   */
  private function parseMethodName($string)
  {
    return str_replace(" ", "", lcfirst(ucwords(str_replace(".", " ", $string))));
  }

  /**
   * Handle customer creation. Adds customer_id to users table.
   *
   * @return string
   */
  private function customerCreated()
  {
    // Define pertinent variables
    $email = $this->event->email;
    $customerId = $this->event->id;
    $user = Sentry::getUserProvider()->findByLogin($email);

    // Add User's Customer ID
    $user->customer_id = $customerId;
    $user->save();

    return 'Added customer_id ' . $customerId . ' to User with email ' . $email . '.';
  }

  /**
   * Handle customer deletion. Removes customer_id from users table.
   *
   * @return string
   */
  private function customerDeleted()
  {
    // Define pertinent variables
    $email = $this->event->email;
    $user = Sentry::getUserProvider()->findByLogin($email);
    $oldCustId = $user->customer_id;

    // Remove User's Customer ID
    $user->customer_id = '';
    $user->save();

    return 'Removed customer_id ' . $oldCustId . ' from User with email ' . $email . '.';
  }

  /**
   * Handle subscription initiation. Adds user to specified group.
   *
   * @return string
   */
  private function customerSubscriptionCreated()
  {
    // Define pertinent variables
    $customer = Stripe_Customer::retrieve($this->event->customer);
    $user = Sentry::getUserProvider()->findByLogin($customer->email);
    $sentryGroups = Config::get('saasframe::plans');
    $plan = $this->event->plan->id;
    $groupId = array_key_exists($plan, $sentryGroups) ? $sentryGroups[$plan] : $sentryGroups['default'];

    // Add user to group
    $group = Sentry::getGroupProvider()->findById($groupId);
    $user->addGroup($group);

    return 'Added user ' . $user->id . ' to group ' . $group->id . '.';
  }

  /**
   * Handle subscription cancellation. Removes user from specified group.
   *
   * @return string
   */
  private function customerSubscriptionDeleted()
  {
    // Define pertinent variables
    $customer = Stripe_Customer::retrieve($this->event->customer);
    $user = Sentry::getUserProvider()->findByLogin($customer->email);
    $sentryGroups = Config::get('saasframe::plans');
    $plan = $this->event->plan->id;
    $groupId = array_key_exists($plan, $sentryGroups) ? $sentryGroups[$plan] : $sentryGroups['default'];

    // Remove user from group
    $group = Sentry::getGroupProvider()->findById($groupId);
    $user->removeGroup($group);

    return 'Removed user ' . $user->id . ' from group ' . $group->id . '.';
  }

  /**
   * Handle subscription trial_will_end. Emails user subscription notification
   *
   * @return string
   */
  private function customerSubscriptionTrial_will_end()
  {
    // Define pertinent variables
    $customer = Stripe_Customer::retrieve($this->event->customer);
    $user = Sentry::getUserProvider()->findByLogin($customer->email);
    $subscription = $this->event;
    $mailMethod = Config::get('saasframe::email.method');

    Mail::{$mailMethod}(Config::get('saasframe::email.view'), (array)$subscription, function($message) use ($user)
    {
      $message->to($user->email, $user->first_name . ' ' . $user->last_name);
      if(Config::get('saasframe::email.from.address'))
      {
        $message->from(Config::get('saasframe::email.from.address'), Config::get('saasframe::email.from.name'));
      }
      $message->subject(Config::get('saasframe::email.subject'));
    });
  }

}
# Notify notifications channel for Laravel 5.7+ & 6.x

[![Latest Version on Packagist](https://img.shields.io/packagist/v/notify-eu/notify.svg?style=flat-square)](https://packagist.org/packages/notify-eu/notify)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/notify-eu/notify/master.svg?style=flat-square)](https://travis-ci.org/notify-eu/notify)
[![StyleCI](https://styleci.io/repos/200646016/shield)](https://styleci.io/repos/200646016)
[![Total Downloads](https://img.shields.io/packagist/dt/notify-eu/notify.svg?style=flat-square)](https://packagist.org/packages/notify-eu/notify)

This package makes it easy to send notifications using [Notify](https://notify.eu) with Laravel 5.7+ & 6.x

## Contents

- [Installation](#installation)
	- [Setting up your Notify account](#setting-up-your-notify-account)
- [Usage](#usage)
	- [Available Message methods](#all-available-methods)
- [Changelog](#changelog)
- [Testing](#testing)
- [Security](#security)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)

## Installation

You can install the package via composer:

```bash
$ composer require notify-eu/notify
```

### Setting up your Notify account

Add your ClientId, secret and transport to your `config/services.php`:

`NOTIFY_URL` is not mandatory. Can be used when you want to overwrite the endpoint Notify is calling. (f.e. different url for Staging/production)

```php
// config/services.php
...
''notify' => [
         'clientID' => env('NOTIFY_CLIENT_ID'),
         'secret' => env('NOTIFY_SECRET'),
         'transport' => env('NOTIFY_TRANSPORT'),
         'url' => env('NOTIFY_URL')
],
...
```

Add your Notify credentials to your `.env`:

```php
// .env
...
NOTIFY_CLIENT_ID=
NOTIFY_SECRET=
NOTIFY_TRANSPORT=
NOTIFY_URL=
],
...
```


## Usage

Now you can use the channel in your `via()` method inside the notification:

``` php
use App\User;
use Illuminate\Notifications\Notification;
use NotifyEu\Notify\NotifyChannel;
use NotifyEu\Notify\NotifyMessage;

class InvoicePaid extends Notification
{
    const TYPE = 'buyerContractApproval';
    protected $user;
    private $cc = [];
    private $bcc = [];


    /**
     * InvoicePaid constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @param $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [NotifyChannel::class];
    }

    /**
     * @param $notifiable
     * @return NotifyMessage
     */
    public function toNotify($notifiable)
    {
        return NotifyMessage::create()
            ->setNotificationType(self::TYPE)
            ->setTransport('mail')
            ->setLanguage('en')
            ->setParams($this->getParams())
            ->setCc($this->cc)
            ->setBcc($this->bcc);
    }

    /**
     * @return array
     */
    private function getParams()
    {
        return array('userToken' => $this->user->getRememberToken());
    }

    /**
     * @param array $cc
     * format: array(array('name' => 'John Doe', 'recipient' => 'john@doe.com')
     */
    public function addCc(array $cc)
    {
        $this->cc = $cc;
    }

    /**
     * @param array $bcc
     * format: array(array('name' => 'John Doe', 'recipient' => 'john@doe.com')
     */
    public function addBcc(array $bcc)
    {
        $this->bcc = $bcc;
    }
```

### Notifiable

Make sure the notifiable model has the following method:

``` php
/**
 * Route notifications for the notify channel.
 *
 * @return string
 */
public function routeNotificationForNotify()
{
    return [
        'name' => $this->name,
        'recipient' => $this->email,
    ];
}
```

### All available methods

- `notificationType('')`: Accepts a string value.
- `transport('')`: Accepts a string value. if not set, it will fallback to NOTIFY_TRANSPORT in .env file
- `language('')`: Accepts a string value.
- `params($array)`: Accepts an array of key/value parameters
- `Cc($array)`: Accepts an array of arrays of 'name'/'recipient' keys
- `Bcc($array)`: Accepts an array of arrays of 'name'/'recipient' keys

## Events
Following events are triggered by Notification. By default:
- Illuminate\Notifications\Events\NotificationSending
- Illuminate\Notifications\Events\NotificationSent

and this channel triggers one when a call to Notify fails for any reason:
- Illuminate\Notifications\Events\NotificationFailed

To listen to those events create event listeners in `app/Listeners`:

```php

namespace App\Listeners;
	
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use NotifyEu\Notify\NotifyChannel;
	
class NotificationFailedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Notification failed event handler
     *
     * @param  NotificationFailed  $event
     * @return void
     */
    public function handle(NotificationFailed $event)
    {
        // Handle fail event for Notify
        //
        if($event->channel == NotifyChannel::class) {
	            
            $logData = [
            	'notifiable'    => $event->notifiable->id,
            	'notification'  => get_class($event->notification),
            	'channel'       => $event->channel,
            	'data'      => $event->data
            	];
            	
            Log::error('Notification Failed', $logData);
         }
    }
}
```
 
 
 
Then register listeners in `app/Providers/EventServiceProvider.php`
```php
...
protected $listen = [

	'Illuminate\Notifications\Events\NotificationFailed' => [
		'App\Listeners\NotificationFailedListener',
	],

	'Illuminate\Notifications\Events\NotificationSent' => [
		'App\Listeners\NotificationSentListener',
	],
];
...
```


## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

```bash
$ composer test
```

## Security

If you discover any security related issues, please email info@notify.eu instead of using the issue tracker.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [notify](https://github.com/notify-eu)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

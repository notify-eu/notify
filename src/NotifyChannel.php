<?php

namespace Notify;

use Exception;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Events\Dispatcher;
use Notify\Exceptions\InvalidMessageObject;

class NotifyChannel
{
    /** @var NotifyClient */
    protected $client;

    /** @var Dispatcher */
    protected $events;

    public function __construct(NotifyClient $client, Dispatcher $events)
    {
        $this->client = $client;
        $this->events = $events;
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param Notification $notification
     *
     * @throws \notify\Exceptions\CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification)
    {
        try {
            /** @var $message NotifyMessage */
            $message = $notification->toNotify($notifiable);
            if ($to = $this->getTo($notifiable)) {
                $message->addRecipient($to['name'], $to['recipient']);
            }

            return $this->client->send($message);
        } catch (Exception $exception) {
            $event = new NotificationFailed(
                $notifiable,
                $notification,
                get_class($this),
                ['message' => $exception->getMessage()]
            );

            $this->events->dispatch($event);
        }
    }

    /**
     * Get the address to send a notification to.
     *
     * @param mixed $notifiable
     * @return mixed
     * @throws CouldNotSendNotification
     */
    protected function getTo($notifiable)
    {
        if ($recipient = $notifiable->routeNotificationFor('notify')) {
            if (is_array($recipient) && array_key_exists('name', $recipient) && array_key_exists('recipient', $recipient)) {
                return $recipient;
            }
            throw InvalidMessageObject::misconfiguredRecipient();
        }
        throw InvalidMessageObject::missingRecipient();
    }
}

<?php

// src/Notification/PublishScheduledThreadsNotification.php

namespace App\Notification;

use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\RecipientInterface;

class PublishScheduledThreadsNotification extends Notification
{
    private $subject;
    private $content;
    private $recipient;

    public function __construct(string $subject, string $content, RecipientInterface $recipient)
    {
        parent::__construct('Publish Scheduled Threads Notification');

        $this->subject = $subject;
        $this->content = $content;
        $this->recipient = $recipient;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getRecipient(): RecipientInterface
    {
        return $this->recipient;
    }
}

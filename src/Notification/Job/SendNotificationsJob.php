<?php

/*
 * This file is part of Flarum.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace Flarum\Notification\Job;

use Carbon\Carbon;
use Flarum\Notification\Blueprint\BlueprintInterface;
use Flarum\Notification\Event\Notifying;
use Flarum\Notification\Event\Sending;
use Flarum\Notification\MailableInterface;
use Flarum\Notification\Notification;
use Flarum\Queue\AbstractJob;
use Flarum\User\User;

class SendNotificationsJob extends AbstractJob
{
    /**
     * @var BlueprintInterface
     */
    private $blueprint;
    /**
     * @var User[]
     */
    private $recipients;

    public function __construct(BlueprintInterface $blueprint, array $recipients = [])
    {
        $this->blueprint = $blueprint;
        $this->recipients = $recipients;
    }

    public function handle()
    {
        $now = Carbon::now('utc')->toDateTimeString();
        $recipients = $this->recipients;

        event(new Sending($this->blueprint, $recipients));

        $attributes = $this->blueprint->getAttributes();

        Notification::insert(
            array_map(function (User $user) use ($attributes, $now) {
                return $attributes + [
                    'user_id' => $user->id,
                    'created_at' => $now
                ];
            }, $recipients)
        );

        event(new Notifying($this->blueprint, $recipients));

        if ($this->blueprint instanceof MailableInterface) {
            $this->chain([new SendEmailNotificationJob($this->blueprint, $recipients)]);
        }
    }
}

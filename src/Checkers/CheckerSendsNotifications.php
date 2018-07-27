<?php

namespace Pbmedia\ApiHealth\Checkers;

interface CheckerSendsNotifications
{
    public function resendFailedNotificationAfterMinutes(): int;

    public function failedNotificationClass(): string;

    public function recoveredNotificationClass(): string;
}

<?php

namespace Pbmedia\ApiHealth\Tests;

use Illuminate\Support\Facades\Notification;
use Orchestra\Testbench\TestCase;
use Pbmedia\ApiHealth\Notifications\CheckerHasFailed as CheckerHasFailedNotification;
use Pbmedia\ApiHealth\Runner;
use Pbmedia\ApiHealth\Storage\CheckerState;
use Pbmedia\ApiHealth\Tests\TestCheckers\FailingAtEvenTimesChecker;
use Pbmedia\ApiHealth\Tests\TestCheckers\FailingChecker;
use Pbmedia\ApiHealth\Tests\TestCheckers\NotificationlessChecker;
use Pbmedia\ApiHealth\Tests\TestCheckers\PassingChecker;

class NotificationTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('api-health.checkers', [
            FailingChecker::class,
            PassingChecker::class,
        ]);

        $app['config']->set('api-health.cache_driver', 'array');
    }

    protected function getPackageProviders($app)
    {
        return [
            \Pbmedia\ApiHealth\LaravelServiceProvider::class,
        ];
    }

    /** @test */
    public function it_doesnt_notify_whenever_the_via_config_is_empty()
    {
        config()->set('api-health.notifications.via', []);

        Notification::fake();

        $runner = app(Runner::class)->handle();

        Notification::assertNotSentTo(
            app(config('api-health.notifications.notifiable')),
            CheckerHasFailedNotification::class
        );
    }

    /** @test */
    public function it_doesnt_notify_whenever_the_checker_does_not_implement_the_notification_interface()
    {
        Notification::fake();

        config()->set('api-health.notifications.via', ['mail']);
        config()->set('api-health.checkers', [
            NotificationlessChecker::class,
        ]);

        $runner = app(Runner::class)->handle();

        Notification::assertNotSentTo(
            app(config('api-health.notifications.notifiable')),
            CheckerHasFailedNotification::class
        );
    }

    /** @test */
    public function it_can_notify_whenever_a_checker_fails()
    {
        config()->set('api-health.notifications.via', ['mail']);

        Notification::fake();

        $runner = app(Runner::class)->handle();

        Notification::assertSentTo(
            app(config('api-health.notifications.notifiable')),
            CheckerHasFailedNotification::class,
            function ($notification, $channels) {
                return $channels === ['mail'];
            }
        );
    }

    /** @test */
    public function it_only_notifies_once()
    {
        config()->set('api-health.notifications.via', ['mail']);

        Notification::fake();

        $runner = app(Runner::class)->handle();
        $runner = app(Runner::class)->handle();

        Notification::assertSentToTimes(
            app(config('api-health.notifications.notifiable')),
            CheckerHasFailedNotification::class,
            1
        );
    }

    /** @test */
    public function it_notifies_again_if_it_fails_after_it_has_been_recovered()
    {
        Notification::fake();

        config()->set('api-health.notifications.via', ['mail']);
        config()->set('api-health.checkers', [
            FailingAtEvenTimesChecker::class,
        ]);

        //

        $state  = new CheckerState(FailingAtEvenTimesChecker::create());
        $runner = app(Runner::class);

        $runner->handle();
        $this->assertTrue($state->isFailed());

        $runner->handle();
        $this->assertFalse($state->isFailed());

        $runner->handle();
        $this->assertTrue($state->isFailed());

        Notification::assertSentToTimes(
            app(config('api-health.notifications.notifiable')),
            CheckerHasFailedNotification::class,
            2
        );
    }
}

<?php

namespace App\Providers;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Logins are part of the activity log: who came in, who left, and failed attempts.
        Event::listen(Login::class, fn (Login $event) => ActivityLog::record('login', label: $event->user->email, user: $event->user));
        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                ActivityLog::record('logout', label: $event->user->email, user: $event->user);
            }
        });
        Event::listen(Failed::class, function (Failed $event) {
            ActivityLog::record('login_failed', label: (string) ($event->credentials['email'] ?? 'unknown'), reason: 'Wrong email or password');
        });
    }
}

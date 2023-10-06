<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\File;
use App\Models\User;
use App\Policies\FilePolicy;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        File::class => FilePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $frontEndUrl = env('FRONTEND_URL', 'http://localhost');
        $this->setFrontEndUrlInResetPasswordEmail($frontEndUrl);
    }

    protected function setFrontEndUrlInResetPasswordEmail(string $frontEndUrl = ''): void
    {
        // update url in ResetPassword Email to frontend url
        ResetPassword::createUrlUsing(function (User $notifiable, string $token) {
            // TODO : should be update with front end url
            // return $frontEndUrl.'/api/auth/password/email/reset?token='.$token;

            return url(route('auth.password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));
        });
    }
}

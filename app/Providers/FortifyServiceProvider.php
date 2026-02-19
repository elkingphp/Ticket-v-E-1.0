<?php

namespace App\Providers;

use Modules\Core\Application\Actions\Fortify\CreateNewUser;
use Modules\Core\Application\Actions\Fortify\ResetUserPassword;
use Modules\Core\Application\Actions\Fortify\UpdateUserPassword;
use Modules\Core\Application\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
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
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        Fortify::loginView(function () {
            return view('core::auth.login');
        });

        Fortify::registerView(function () {
            return view('core::auth.register');
        });

        Fortify::requestPasswordResetLinkView(function () {
            return view('core::auth.forgot-password');
        });

        Fortify::twoFactorChallengeView(function () {
            return view('core::auth.two-factor-challenge');
        });

        $this->app->singleton(
            \Laravel\Fortify\Contracts\ConfirmPasswordViewResponse::class ,
            function () {
            return new class implements \Laravel\Fortify\Contracts\ConfirmPasswordViewResponse {
                public function toResponse($request) {
                            return response()->view('core::profile.sudo');
                        }
                    };
                }
        );

        RateLimiter::for ('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for ('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
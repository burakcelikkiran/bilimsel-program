<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;
use Inertia\Inertia;

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

       // ✅ Auth View Customizations
       Fortify::loginView(function () {
           return Inertia::render('Auth/Login', [
               'canResetPassword' => config('app.password_reset_enabled', true) && Route::has('password.request'),
               'canRegister' => config('app.registration_enabled', true) && Route::has('register'),
               'status' => session('status'),
           ]);
       });

       Fortify::registerView(function () {
           return Inertia::render('Auth/Register', [
               'canLogin' => Route::has('login'),
           ]);
       });

       Fortify::requestPasswordResetLinkView(function () {
           return Inertia::render('Auth/ForgotPassword', [
               'status' => session('status'),
           ]);
       });

       Fortify::resetPasswordView(function (Request $request) {
           return Inertia::render('Auth/ResetPassword', [
               'email' => $request->email,
               'token' => $request->route('token'),
           ]);
       });

       // Rate Limiting
       RateLimiter::for('login', function (Request $request) {
           $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

           return Limit::perMinute(5)->by($throttleKey);
       });

       RateLimiter::for('two-factor', function (Request $request) {
           return Limit::perMinute(5)->by($request->session()->get('login.id'));
       });
   }
}
# Laravel Auth Guard for IIS Windows integrated authentication

Provides implementation of integrated authentication when using Windows auth option with IIS and Active Directory.

## Installation

1. Add `JakubKlapka\LaravelWindowsAuth\Providers\ServiceProvider::class` to your app.php.
1. Run `php artisan vendor:publish` to export config file.
1. In config\ad_auth.php set allowed AD domains (or don't)
1. In config\auth.php set guard to `windows`, for example:

```php
'defaults' => [
    'guard' => 'windows',
],
'guards' => [
    'windows' => [
        'driver' => 'windows',
        'provider' => 'users'
    ]
```

Don't forget to implement `\Illuminate\Contracts\Auth\Authenticatable` in your User model, if you are not using default Eloquent one.

<?php

namespace JakubKlapka\LaravelWindowsAuth\Providers;

use Illuminate\Auth\AuthManager;
use Illuminate\Foundation\Application;
use JakubKlapka\LaravelWindowsAuth\Service\ActiveDirectoryAuthService;
use JakubKlapka\LaravelWindowsAuth\Service\WindowsGuard;

/**
 * Register AD Auth logic to Laravel
 *
 * Class ServiceProvider
 * @package JakubKlapka\LaravelWindowsAuth\Providers
 */
class ServiceProvider extends \Illuminate\Support\ServiceProvider {

	/**
	 * Register Services to Container
	 */
	public function register(): void {

		$this->app->singleton( ActiveDirectoryAuthService::class );

	}

	/**
	 * Publish config file
	 */
	public function boot(): void {

		$this->publishes( [ dirname( __DIR__ ) . '/Config/ad_auth.php' => $this->app->configPath() . '/ad_auth.php' ] );

		/** @var AuthManager $auth_manager */
		$auth_manager = $this->app->make( AuthManager::class );
		$auth_manager->extend( 'windows', function ( $app, $name, array $config ) {
			return $app->make( WindowsGuard::class );
		} );

	}

}
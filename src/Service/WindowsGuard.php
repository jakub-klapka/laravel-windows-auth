<?php

namespace JakubKlapka\LaravelWindowsAuth\Service;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\AuthManager;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Foundation\Application;

class WindowsGuard implements Guard {

	/**
	 * Current user
	 *
	 * @var Authenticatable
	 */
	protected $user;

	/**
	 * @var Application
	 */
	private $app;
	/**
	 * @var ActiveDirectoryAuthService
	 */
	private $authService;
	/**
	 * @var Repository
	 */
	private $config;

	public function __construct( Application $app, ActiveDirectoryAuthService $authService, Repository $config ) {
		$this->app = $app;
		$this->authService = $authService;
		$this->config = $config;
	}

	/**
	 * Handle authentication of current user
	 *
	 * @throws AuthenticationException
	 */
	public function authenticate(): void {

		$success = $this->authService->loginUser();
		if( $success === false ) throw new AuthenticationException();

		$user_provider = $this->getProvider();
		$user = $user_provider->retrieveByCredentials( [ $this->authService->getLogin() ] );
		$this->setUser( $user );

	}

	/**
	 * Get default user provider based on auth config
	 *
	 * @return UserProvider
	 */
	public function getProvider(): UserProvider {

		$provider_config_string = $this->config->get( 'auth.guards.' . $this->config->get( 'auth.defaults.guard' ) . '.provider' );
		$user_provider = $this->app->make( AuthManager::class )->createUserProvider( $provider_config_string );
		return $user_provider;

	}

	/**
	 * Blank function to avoid undefined on debugbar
	 */
	public function viaRemember() {
	}

	/**
	 * Determine if the current user is authenticated.
	 *
	 * @return bool
	 */
	public function check(): bool {
		if( $this->user() instanceof Authenticatable ) return true;
		return false;
	}

	/**
	 * Determine if the current user is a guest.
	 *
	 * Will never happen.
	 *
	 * @return bool
	 */
	public function guest(): bool {
		return false;
	}

	/**
	 * Get the currently authenticated user.
	 *
	 * @return \Illuminate\Contracts\Auth\Authenticatable|null
	 */
	public function user(): ?Authenticatable {
		return $this->user;
	}

	/**
	 * Get the ID for the currently authenticated user.
	 *
	 * @return string|int|null
	 */
	public function id() {
		return $this->user->getAuthIdentifier();
	}

	/**
	 * Validate a user's credentials.
	 *
	 * Validation is not needed, Win auth does that for us.
	 *
	 * @param  array $credentials
	 *
	 * @return bool
	 */
	public function validate( array $credentials = [] ): bool {
		try {
			$this->authenticate();
		} catch( \Exception $e ) {
			return false;
		}
		return true;
	}

	/**
	 * Set the current user.
	 *
	 * @param  \Illuminate\Contracts\Auth\Authenticatable $user
	 *
	 * @return void
	 */
	public function setUser( Authenticatable $user ): void {
		$this->user = $user;
	}

}
<?php

namespace JakubKlapka\LaravelWindowsAuth\Service;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;

/**
 * Singleton service to handle ActiveDirectory login
 *
 * Class ActiveDirectoryAuthService
 * @package JakubKlapka\LaravelWindowsAuth\Service
 */
class ActiveDirectoryAuthService {

	/**
	 * @var string|null Only login part of user login
	 */
	protected $userLogin;

	/**
	 * @var Repository
	 */
	private $config;

	/**
	 * @var Application
	 */
	private $app;

	public function __construct( Repository $config, Application $app ) {
		$this->config = $config;
		$this->app = $app;
	}

	/**
	 * Handle logging in of user. Usualy called from middleware stack
	 *
	 * @return bool False on unsucessfull login
	 */
	public function loginUser(): bool {

		// Check for already existing user in Guard
		$guard = $this->app->make( \Illuminate\Auth\AuthManager::class )->guard();
		if( $guard->user() instanceof Authenticatable ) {
			$this->userLogin = $guard->user()->getAuthIdentifier();
			return true;
		}

		// Get user name from IIS. Will be populated only if AD Auth is done.
		$remote_user = isset( $_SERVER[ 'REMOTE_USER' ] ) ? $_SERVER[ 'REMOTE_USER' ] : false;
		if( empty( $remote_user ) ) return false;

		$login_parts = explode( '\\', $remote_user );
		if( count( $login_parts ) !== 2 ) return false;

		if( $this->checkForAllowedDomain( $login_parts[0] ) === false ) return false;

		// All checks passed
		$this->userLogin = $login_parts[1];
		return true;

	}

	/**
	 * Get current user login name
	 *
	 * @return string
	 */
	public function getLogin(): string {
		return $this->userLogin;
	}

	/**
	 * Check, if user login belongs to allowed AD Domain
	 *
	 * @param string $domain
	 *
	 * @return bool
	 */
	private function checkForAllowedDomain( string $domain ): bool {

		$allowed_domains = $this->config->get( 'ad_auth.allowed_domains' );

		if( $allowed_domains === null ) return true;

		$allowed_domains = new Collection( $allowed_domains );
		$allowed_domains = $allowed_domains->map( function( $domain ) {
			return strtolower( $domain );
		} );

		return ( $allowed_domains->search( strtolower( $domain ) ) === false ) ? false : true;

	}

}
<?php

namespace JakubKlapka\LaravelWindowsAuth\Middleware;

use Closure;
use Illuminate\Foundation\Application;
use Illuminate\Http\Response;
use JakubKlapka\LaravelWindowsAuth\Service\ActiveDirectoryAuthService;

class ActiveDirectoryLoginAuth {

	/**
	 * @var Application
	 */
	private $application;

	/**
	 * @var ActiveDirectoryAuthService
	 */
	private $activeDirectoryAuthService;

	public function __construct( Application $application, ActiveDirectoryAuthService $activeDirectoryAuthService ) {
		$this->application = $application;
		$this->activeDirectoryAuthService = $activeDirectoryAuthService;
	}

	/**
	 * Hook to middleware stack and try to login user via integrated Active Directory
	 *
	 * Breaks request cycle on unsuccesfull login
	 *
	 * @param Response $request
	 * @param Closure $next
	 *
	 * @return Response
	 */
	public function handle( $request, Closure $next ): Response {

		if( $this->activeDirectoryAuthService->loginUser() === false ) {
			$this->application->abort( 503, 'Active Directory authentication not enabled.' );
		};

		return $next( $request );

	}

}
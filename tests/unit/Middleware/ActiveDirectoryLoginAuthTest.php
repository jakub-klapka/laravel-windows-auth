<?php

namespace Tests\ActiveDirectoryLoginAuth;

use Illuminate\Foundation\Application;
use Illuminate\Http\Response;
use JakubKlapka\LaravelWindowsAuth\Middleware\ActiveDirectoryLoginAuth;
use JakubKlapka\LaravelWindowsAuth\Service\ActiveDirectoryAuthService;
use Tests\TestCase;

class ActiveDirectoryLoginAuthTest extends TestCase {

	public function testHandle() {

		$request = $this->createMock( Response::class );
		$next = function( Response $response ) { return $response; };

		// Test valid response
		$application_stub = $this->createMock( Application::class );
		$ad_auth_service_stub = $this->createMock( ActiveDirectoryAuthService::class );
		$ad_auth_service_stub->method( 'loginUser' )->willReturn( true );
		$ad_login_auth = new ActiveDirectoryLoginAuth( $application_stub, $ad_auth_service_stub );

		$output = $ad_login_auth->handle( $request, $next );
		$this->assertEquals( $request, $output );

		// Test failed auth
		$ad_auth_service_stub = $this->createMock( ActiveDirectoryAuthService::class );
		$ad_auth_service_stub->method( 'loginUser' )->willReturn( false );
		$application_stub = $this->createMock( Application::class );
		$result_code = null;
		$application_stub->method( 'abort' )->willReturnCallback( function( $code, $message ) use ( &$result_code ) {
			$result_code = $code;
		} );
		$ad_login_auth = new ActiveDirectoryLoginAuth( $application_stub, $ad_auth_service_stub );
		$ad_login_auth->handle( $request, $next );
		$this->assertEquals( 503, $result_code );

	}

}
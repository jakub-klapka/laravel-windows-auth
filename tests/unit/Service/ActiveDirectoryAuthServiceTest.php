<?php

use Illuminate\Config\Repository;
use JakubKlapka\LaravelWindowsAuth\Service\ActiveDirectoryAuthService;
use Tests\TestCase;

class ActiveDirectoryAuthServiceTest extends TestCase {

	public function testLoginUser() {

		// Auth not activated
		$_SERVER[ 'REMOTE_USER' ] = null;
		$config = $this->createMock( Repository::class );
		$service = new ActiveDirectoryAuthService( $config );
		$this->assertFalse( $service->loginUser() );

		// Invalid auth string
		$_SERVER[ 'REMOTE_USER' ] = 'domain1\domain2\user';
		$this->assertFalse( $service->loginUser() );

		$_SERVER[ 'REMOTE_USER' ] = 'user';
		$this->assertFalse( $service->loginUser() );

		// Any domain
		$_SERVER[ 'REMOTE_USER' ] = 'domain\user';
		$config = new class extends Repository {
			public function get( $key, $default = null ) {
				return null;
			}
		};
		$service = new ActiveDirectoryAuthService( $config );
		$this->assertTrue( $service->loginUser() );

		// Allowed domain
		$config = new class extends Repository {
			public function get( $key, $default = null ) {
				return 'domain';
			}
		};
		$service = new ActiveDirectoryAuthService( $config );
		$this->assertTrue( $service->loginUser() );

		// Not allowed domain
		$config = new class extends Repository {
			public function get( $key, $default = null ) {
				return 'another';
			}
		};
		$service = new ActiveDirectoryAuthService( $config );
		$this->assertFalse( $service->loginUser() );

		// Array of domains
		$config = new class extends Repository {
			public function get( $key, $default = null ) {
				return [ 'domain', 'another' ];
			}
		};
		$service = new ActiveDirectoryAuthService( $config );
		$this->assertTrue( $service->loginUser() );

	}

}
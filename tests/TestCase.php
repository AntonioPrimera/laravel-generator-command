<?php

namespace AntonioPrimera\Artisan\Tests;

use AntonioPrimera\Artisan\GenComServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

class TestCase extends \Orchestra\Testbench\TestCase
{
	
	protected function getPackageProviders($app): array
	{
		return [
			GenComServiceProvider::class,
		];
	}
}
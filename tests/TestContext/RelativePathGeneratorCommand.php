<?php

namespace AntonioPrimera\Artisan\Tests\TestContext;

use AntonioPrimera\Artisan\FileGeneratorCommand;

class RelativePathGeneratorCommand extends FileGeneratorCommand
{
	public static array $staticRecipe = [];
	
	protected $signature = 'make:gen-test-relative {name}';
	
	protected function recipe(): array
	{
		return static::$staticRecipe;
	}
}
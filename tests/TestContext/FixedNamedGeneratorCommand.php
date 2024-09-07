<?php
namespace AntonioPrimera\Artisan\Tests\TestContext;

use AntonioPrimera\Artisan\FileGeneratorCommand;
use AntonioPrimera\Artisan\FileRecipes\BladeRecipe;
use AntonioPrimera\Artisan\FileRecipes\ViewComponentRecipe;

/**
 * This command doesn't receive any name and should use the stub file names as the target file names
 */
class FixedNamedGeneratorCommand extends FileGeneratorCommand
{
	protected $signature = 'make:gen-fixed-name';
	
	protected function recipe(): array
	{
		return [
			new ViewComponentRecipe(__DIR__ . '/stubs/OuterLayout.php.stub'),
			new BladeRecipe(__DIR__ . '/stubs/outer.blade.php.stub', 'components/layouts'),
		];
	}
}
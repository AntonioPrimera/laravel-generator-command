<?php
namespace AntonioPrimera\Artisan\Tests\TestContext;

use AntonioPrimera\Artisan\FileGeneratorCommand;
use AntonioPrimera\Artisan\FileRecipe;
use AntonioPrimera\Artisan\FileRecipes\BladeRecipe;
use AntonioPrimera\Artisan\FileRecipes\ViewComponentRecipe;

/**
 * This command doesn't receive any name and should use the stub file names as the target file names
 */
class GeneratorWithBackupCommand extends FileGeneratorCommand
{
	protected $signature = 'make:backup-test {name} {--backup} {--overwrite} {--dry-run}';
	
	protected function recipe(): array
	{
		return [
			FileRecipe::create(__DIR__ . '/stubs/generic-file.stub', 'app')
				->withReplace(['#REPLACE-ME#' => config('test-context.replace-me', 'default-replace')])
				->withExtension('txt')
				->withOverwriteFiles($this->option('overwrite'))
				->withBackupFiles($this->option('backup')),
		];
	}
}
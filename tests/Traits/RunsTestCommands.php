<?php
namespace AntonioPrimera\Artisan\Tests\Traits;

use AntonioPrimera\Artisan\Tests\TestContext\ComplexGeneratorCommand;
use AntonioPrimera\Artisan\Tests\TestContext\FixedNamedGeneratorCommand;
use AntonioPrimera\Artisan\Tests\TestContext\GeneratorWithBackupCommand;
use AntonioPrimera\Artisan\Tests\TestContext\PrebuiltRecipesGeneratorCommand;
use AntonioPrimera\Artisan\Tests\TestContext\RelativePathGeneratorCommand;
use AntonioPrimera\Artisan\Tests\TestHelpers;
use Illuminate\Console\Application;

trait RunsTestCommands
{
	use TestHelpers;
	
	protected function setUp(): void
	{
		parent::setUp();
		
		$commandClasses = [
			ComplexGeneratorCommand::class,			//make:gen-test-complex
			RelativePathGeneratorCommand::class,	//make:gen-test-relative
			PrebuiltRecipesGeneratorCommand::class,	//make:pbr
			FixedNamedGeneratorCommand::class,		//make:gen-fixed-name
			GeneratorWithBackupCommand::class,		//make:backup-test
		];
		
		Application::starting(function ($artisan) use ($commandClasses) {
			foreach ($commandClasses as $commandClass) {
				$artisan->resolveCommands($commandClass);
			}
		});
		
		$this->cleanupFilesAndFolders();
	}
	
	protected function tearDown(): void
	{
		parent::tearDown();
		$this->cleanupFilesAndFolders();
	}
	
	protected function cleanupFilesAndFolders(): void
	{
		$this->rrmdir(realpath(__DIR__ . '/../TestContext/GeneratedFiles'));
		$this->rrmdir(app_path('stubs'));
	}
}
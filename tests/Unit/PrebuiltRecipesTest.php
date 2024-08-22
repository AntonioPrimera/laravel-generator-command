<?php
namespace AntonioPrimera\Artisan\Tests\Unit;

use AntonioPrimera\Artisan\Tests\CustomAssertions;
use AntonioPrimera\Artisan\Tests\TestCase;
use AntonioPrimera\Artisan\Tests\TestHelpers;
use AntonioPrimera\Artisan\Tests\Traits\RunsTestCommands;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;

class PrebuiltRecipesTest extends TestCase
{
	use TestHelpers, CustomAssertions;
	use RunsTestCommands {
		cleanupFilesAndFolders as protected traitCleanupFilesAndFolders;
	}
	
	#[Test]
	public function it_can_create_a_migration_using_the_migration_recipe()
	{
		$migrationFile = $this->findMigration('CreateSiteComponentsTable');
		$this->assertNull($migrationFile);
		
		Artisan::call('make:pbr', ['name' => 'CreateSiteComponentsTable', 'recipe' => 'migration']);
		
		$migrationFile = $this->findMigration('CreateSiteComponentsTable');
		$this->assertNotNull($migrationFile);
		$this->assertFileExists($migrationFile);
		$this->assertFileContainsString('Migration', $migrationFile);
	}
	
	#[Test]
	public function it_can_create_a_view_component_and_a_blade_view_using_the_view_component_recipe()
	{
		$componentFile = app_path('View/Components/HeroSection/BackgroundImage.php');
		$bladeFile = resource_path('views/components/site-components/hero-section/background-image.blade.php');
		
		$this->cleanupFiles($componentFile, $bladeFile);
		$this->assertFileDoesNotExist($componentFile);
		$this->assertFileDoesNotExist($bladeFile);
		
		Artisan::call('make:pbr', ['name' => 'HeroSection/BackgroundImage', 'recipe' => 'viewComponent']);
		
		$this->assertFileExists($componentFile);
		$this->assertFileContainsStrings(
			[
				'namespace App\View\Components\HeroSection;',
				'class BackgroundImage',
				'ViewComponent',
			],
			$componentFile
		);
		
		$this->assertFileExists($bladeFile);
		$this->assertFileContainsString('Blade', $bladeFile);
	}
	
	#[Test]
	public function it_can_create_a_blade_view_using_the_blade_recipe()
	{
		$bladeFile = resource_path('views/background-image.blade.php');
		
		$this->cleanupFiles($bladeFile);
		$this->assertFileDoesNotExist($bladeFile);
		
		Artisan::call('make:pbr', ['name' => 'BackgroundImage', 'recipe' => 'blade']);
		
		$this->assertFileExists($bladeFile);
		$this->assertFileContainsString('Blade', $bladeFile);
	}
	
	#[Test]
	public function it_can_create_a_model_using_the_model_recipe()
	{
		$modelFile = app_path('Models/Technology.php');
		
		$this->cleanupFiles($modelFile);
		$this->assertFileDoesNotExist($modelFile);
		
		Artisan::call('make:pbr', ['name' => 'Technology', 'recipe' => 'model']);
		
		$this->assertFileExists($modelFile);
		$this->assertFileContainsStrings(
			[
				'namespace App\Models;',
				'class Technology',
				'Model',
			],
			$modelFile
		);
	}
	
	#[Test]
	public function it_can_create_a_command_using_the_command_recipe()
	{
		$commandFile = app_path('Console/Commands/GenerateSiteComponents.php');
		
		$this->cleanupFiles($commandFile);
		$this->assertFileDoesNotExist($commandFile);
		
		Artisan::call('make:pbr', ['name' => 'GenerateSiteComponents', 'recipe' => 'command']);
		
		$this->assertFileExists($commandFile);
		$this->assertFileContainsStrings(
			[
				'namespace App\Console\Commands;',
				'class GenerateSiteComponents',
				'Command',
			],
			$commandFile
		);
	}
	
	#[Test]
	public function it_can_create_a_controller_using_the_controller_recipe()
	{
		$controllerFile = app_path('Http/Controllers/SiteComponentsController.php');
		
		$this->cleanupFiles($controllerFile);
		$this->assertFileDoesNotExist($controllerFile);
		
		Artisan::call('make:pbr', ['name' => 'SiteComponentsController', 'recipe' => 'controller']);
		
		$this->assertFileExists($controllerFile);
		$this->assertFileContainsStrings(
			[
				'namespace App\Http\Controllers;',
				'class SiteComponentsController',
				'TheController',
			],
			$controllerFile
		);
	}
	
	#[Test]
	public function it_can_create_a_service_provider_using_the_service_provider_recipe()
	{
		$serviceProviderFile = app_path('Providers/SiteComponentsServiceProvider.php');
		
		$this->cleanupFiles($serviceProviderFile);
		$this->assertFileDoesNotExist($serviceProviderFile);
		
		Artisan::call('make:pbr', ['name' => 'SiteComponentsServiceProvider', 'recipe' => 'serviceProvider']);
		
		$this->assertFileExists($serviceProviderFile);
		$this->assertFileContainsStrings(
			[
				'namespace App\Providers;',
				'class SiteComponentsServiceProvider',
				'The Service Provider',
			],
			$serviceProviderFile
		);
	}
	
	#[Test]
	public function it_can_create_a_configuration_file_using_the_configuration_recipe()
	{
		$configFile = config_path('site-components.php');
		
		$this->cleanupFiles($configFile);
		$this->assertFileDoesNotExist($configFile);
		
		Artisan::call('make:pbr', ['name' => 'site-components', 'recipe' => 'config']);
		
		$this->assertFileExists($configFile);
		$this->assertFileContainsString('My Configuration', $configFile);
	}
	
	#[Test]
	public function it_can_create_a_js_file()
	{
		$jsFile = resource_path('js/site-components.js');
		
		$this->cleanupFiles($jsFile);
		$this->assertFileDoesNotExist($jsFile);
		
		Artisan::call('make:pbr', ['name' => 'site-components', 'recipe' => 'js']);
		
		$this->assertFileExists($jsFile);
		$this->assertFileContainsString('JavaScript', $jsFile);
	}
	
	#[Test]
	public function it_can_create_a_ts_file()
	{
		$tsFile = resource_path('js/site-components.ts');
		
		$this->cleanupFiles($tsFile);
		$this->assertFileDoesNotExist($tsFile);
		
		Artisan::call('make:pbr', ['name' => 'site-components', 'recipe' => 'ts']);
		
		$this->assertFileExists($tsFile);
		$this->assertFileContainsString('TypeScript', $tsFile);
	}
	
	#[Test]
	public function it_can_create_a_json_file()
	{
		$jsonFile = resource_path('js/site-components.json');
		
		$this->cleanupFiles($jsonFile);
		$this->assertFileDoesNotExist($jsonFile);
		
		Artisan::call('make:pbr', ['name' => 'site-components', 'recipe' => 'json']);
		
		$this->assertFileExists($jsonFile);
		$this->assertFileContainsString('JSON', $jsonFile);
	}
	
	#[Test]
	public function it_can_create_a_css_file()
	{
		$cssFile = resource_path('css/site-components.css');
		
		$this->cleanupFiles($cssFile);
		$this->assertFileDoesNotExist($cssFile);
		
		Artisan::call('make:pbr', ['name' => 'site-components', 'recipe' => 'css']);
		
		$this->assertFileExists($cssFile);
		$this->assertFileContainsString('CSS', $cssFile);
	}
	
	#[Test]
	public function it_can_create_a_pcss_file()
	{
		$pcssFile = resource_path('css/site-components.pcss');
		
		$this->cleanupFiles($pcssFile);
		$this->assertFileDoesNotExist($pcssFile);
		
		Artisan::call('make:pbr', ['name' => 'site-components', 'recipe' => 'pcss']);
		
		$this->assertFileExists($pcssFile);
		$this->assertFileContainsString('SCSS', $pcssFile);
	}
	
	//--- Protected helpers -------------------------------------------------------------------------------------------
	
	protected function findMigration(string $name): string|null
	{
		$migrations = scandir(database_path('migrations'));
		$migrationName = Str::snake($name);
		
		foreach ($migrations as $migration)
			if (str_ends_with($migration, "$migrationName.php"))
				return database_path("migrations/$migration");
		
		return null;
	}
	
	protected function cleanupFilesAndFolders(): void
	{
		//generic cleanup
		$this->traitCleanupFilesAndFolders();
		
		//cleanup the migration file
		$migrationFile = $this->findMigration('CreateSiteComponentsTable');
		if ($migrationFile && file_exists($migrationFile))
			unlink($migrationFile);
	}
	
	protected function cleanupFiles(...$files): void
	{
		//cleanup any given files
		foreach ($files as $file)
			if (file_exists($file))
				unlink($file);
	}
}
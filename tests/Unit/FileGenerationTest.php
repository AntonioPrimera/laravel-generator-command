<?php
namespace AntonioPrimera\Artisan\Tests\Unit;

use AntonioPrimera\Artisan\FileRecipe;
use AntonioPrimera\Artisan\Tests\CustomAssertions;
use AntonioPrimera\Artisan\Tests\TestCase;
use AntonioPrimera\Artisan\Tests\TestContext\ComplexGeneratorCommand;
use AntonioPrimera\Artisan\Tests\TestContext\RelativePathGeneratorCommand;
use AntonioPrimera\Artisan\Tests\TestContext\SimpleGeneratorCommand;
use AntonioPrimera\Artisan\Tests\TestHelpers;
use Illuminate\Console\Application;
use Illuminate\Support\Facades\Artisan;

class FileGenerationTest extends TestCase
{
	use TestHelpers, CustomAssertions;
	
	protected function setUp(): void
	{
		parent::setUp();
		
		$commandClasses = [
			ComplexGeneratorCommand::class,			//make:gen-test-complex
			SimpleGeneratorCommand::class,			//make:gen-test-simple
			RelativePathGeneratorCommand::class,	//make:gen-test-relative
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
	
	//--- Actual tests ------------------------------------------------------------------------------------------------
	
	/** @test */
	public function test_context_test_artisan_command_is_set_up_and_can_be_run()
	{
		$this->assertEquals(0, Artisan::call('make:gen-test-simple', ['name' => 'TargetPath/TargetFile']));
	}
	
	/** @test */
	public function it_can_handle_a_simple_recipe_with_one_file()
	{
		$this->assertDirectoryDoesNotExist(__DIR__ . '/../TestContext/GeneratedFiles');
		
		Artisan::call('make:gen-test-simple', ['name' => 'TargetPath/TargetFile']);
		
		//dump(File::directories(__DIR__ . '/../TestContext'));
		$this->assertFoldersExist(__DIR__ . '/../TestContext/GeneratedFiles');
		$this->assertFilesExist(__DIR__ . '/../TestContext/GeneratedFiles/Components/TargetPath/TargetFile.php');
		
		$path = __DIR__ . '/../TestContext/GeneratedFiles/Components/TargetPath/TargetFile.php';
		
		$this->assertFileContainsStrings([
			'namespace AntonioPrimera\My\Namespace\TargetPath;',
			'class TargetFile',
			"return 'my-message';",
		], $path);
		
		$this->assertFileContentsEquals(
			"<?phpnamespace AntonioPrimera\My\Namespace\TargetPath;class TargetFile{public function someMethod(){return 'my-message';}}",
			$path
		);
	}
	
	/** @test */
	public function it_can_handle_a_complex_recipe()
	{
		$this->assertDirectoryDoesNotExist(__DIR__ . '/../TestContext/GeneratedFiles');
		
		Artisan::call('make:gen-test-complex', ['name' => 'TargetPath/TargetFile']);
		
		FileGenerationTest::assertDirectoryExists(__DIR__ . '/../TestContext/GeneratedFiles');
		
		//component files
		$this->assertFilesExist(
			[
				'Components/TargetPath/TargetFile.php',
				'Components/Complex/TargetPath/TargetFile.php',
				'Blades/TargetPath/target-file.bladex.phpx',
				'Blades/Complex/TargetPath/TargetFile.blade.php',
				'TargetPath/TARGETFILE.json',
				'Json/TargetPath/TARGETFILE.JSON'
			],
			__DIR__ . '/../TestContext/GeneratedFiles'
		);
		
		$this->assertFileContainsStrings(
			[
				'"tic": "tac"',
				'"marco": "polo"',
			],
			__DIR__ . '/../TestContext/GeneratedFiles/TargetPath/TARGETFILE.json'
		);
		
		$this->assertFileContentsEquals(
			'<divx>flick</divx>',
			__DIR__ . '/../TestContext/GeneratedFiles/Blades/TargetPath/target-file.bladex.phpx'
		);
	}
	
	/** @test */
	public function if_relative_stub_and_target_paths_are_given_the_paths_will_be_relative_to_the_project_root()
	{
		RelativePathGeneratorCommand::$staticRecipe = [
			'Controller' => [
				'stub' => 'stubs/SampleController.php.stub',
				'target' => 'Http/Controllers',
			],
		];
		
		$targetPath = base_path('Http/Controllers/SomeSpecialController.php');
		$stubPath = base_path('stubs/SampleController.php.stub');
		
		//setup
		if (!is_dir(base_path('stubs')))
			mkdir(base_path('stubs'));
		
		if (!file_exists($stubPath))
			file_put_contents($stubPath, 'Sample Controller');
		
		if (file_exists($targetPath))
			unlink($targetPath);
		
		//actual test
		$this->assertFileDoesNotExist($targetPath);
		
		Artisan::call('make:gen-test-relative', ['name' => 'SomeSpecialController']);
		
		$this->assertFilesExist($targetPath);
		$this->assertFileContentsEquals('Sample Controller', $targetPath);
		
		//cleanup
		unlink($targetPath);
	}
	
	/** @test */
	public function if_the_target_file_already_exists_it_will_not_be_overwritten()
	{
		RelativePathGeneratorCommand::$staticRecipe = [
			'Controller' => [
				'stub' => 'stubs/SampleController.php.stub',
				'target' => 'Http/Controllers',
			],
		];
		
		//setup
		$targetPath = base_path('Http/Controllers/SomeSpecialController.php');
		$stubPath = base_path('stubs/SampleController.php.stub');
		
		if (!is_dir(base_path('stubs')))
			mkdir(base_path('stubs'));
		
		file_put_contents($stubPath, 'Sample Controller');
		file_put_contents($targetPath, 'Existing Controller');
		
		//actual test
		$this->assertFileExists($targetPath);
		$this->assertFileContentsEquals('Existing Controller', $targetPath);
		
		Artisan::call('make:gen-test-relative', ['name' => 'SomeSpecialController']);
		
		$this->assertFileContentsEquals('Existing Controller', $targetPath);
		
		//cleanup
		unlink($targetPath);
	}
	
	//--- Testing some custom scenarios -------------------------------------------------------------------------------
	
	/** @test */
	public function testing_a_failing_scenario_from_package_antonio_primera_laravel_admin_panel()
	{
		//--- Setup ------------------------------------------------
		$stubPath = base_path('stubs/sample.blade.php.stub');
		
		if (!is_dir(dirname($stubPath)))
			mkdir(dirname($stubPath));
		
		if (!file_exists($stubPath))
			file_put_contents($stubPath, 'Sample Blade');
		
		$bladeRecipe = new FileRecipe(
			$stubPath,
			base_path('resources/views/' . trim('livewire/admin-panel', '/\\')),
			fileNameTransformer: 'kebab'
		);
		
		RelativePathGeneratorCommand::$staticRecipe = [
			'Blade File' => $bladeRecipe,
		];
		
		//--- Call the generator command --------------------------
		Artisan::call('make:gen-test-relative', ['name' => 'MySpecialAdminPage']);
		
		$this->assertFilesExist([
			resource_path('views/livewire/admin-panel/my-special-admin-page.blade.php')
		]);
	}
}
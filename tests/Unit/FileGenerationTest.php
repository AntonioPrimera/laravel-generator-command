<?php
namespace AntonioPrimera\Artisan\Tests\Unit;

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
				'path' => 'Http/Controllers',
			],
		];
		
		$targetPath = app_path('Http/Controllers/SomeSpecialController.php');
		$stubPath = app_path('stubs/SampleController.php.stub');
		
		//setup
		if (!is_dir(app_path('stubs')))
			mkdir(app_path('stubs'));
		
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
		unlink($stubPath);
		rmdir(app_path('stubs'));
	}
	
	/** @test */
	public function if_a_callable_target_path_is_given_the_callable_will_return_the_actual_path()
	{
		RelativePathGeneratorCommand::$staticRecipe = [
			'Controller' => [
				'stub' 			 => 'stubs/sampleConfig.php.stub',
				'path' 			 => 'config_path',
				'fileNameFormat' => 'kebab',
			],
		];
		
		$targetPath = config_path('some-special-config.php');
		$stubPath = app_path('stubs/sampleConfig.php.stub');
		
		//setup
		if (!is_dir(app_path('stubs')))
			mkdir(app_path('stubs'));
		
		if (!file_exists($stubPath))
			file_put_contents($stubPath, 'sample-config-file-contents');
		
		if (file_exists($targetPath))
			unlink($targetPath);
		
		//actual test
		$this->assertFileDoesNotExist($targetPath);
		
		Artisan::call('make:gen-test-relative', ['name' => 'SomeSpecialConfig']);
		
		$this->assertFilesExist($targetPath);
		$this->assertFileContentsEquals('sample-config-file-contents', $targetPath);
		
		//cleanup
		unlink($targetPath);
		unlink($stubPath);
		rmdir(app_path('stubs'));
	}
	
	/** @test */
	public function if_a_different_rootPath_is_given_the_relative_path_will_be_appended_to_the_root_path()
	{
		RelativePathGeneratorCommand::$staticRecipe = [
			'Controller' => [
				'stub' => app_path('stubs/SampleController.php.stub'),
				'path' => 'Http/Controllers',
				'rootPath' => resource_path('livewire/controllers'),
			],
		];
		
		$targetPath = resource_path('livewire/controllers/Http/Controllers/SomeSpecialController.php');
		$stubPath = app_path('stubs/SampleController.php.stub');
		
		//setup
		if (!is_dir(dirname($stubPath)))
			mkdir(dirname($stubPath));
		
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
		unlink($stubPath);
		rmdir(dirname($stubPath));
	}
	
	/** @test */
	public function if_a_callable_rootPath_is_given_the_targer_path_will_be_determined_by_the_root_path_callable()
	{
		RelativePathGeneratorCommand::$staticRecipe = [
			'Controller' => [
				'stub' => app_path('stubs/SampleController.php.stub'),
				'path' => 'Http/Controllers',
				'rootPath' => 'resource_path',
			],
		];
		
		$targetPath = resource_path('Http/Controllers/SomeSpecialController.php');
		$stubPath = app_path('stubs/SampleController.php.stub');
		
		//setup
		if (!is_dir(dirname($stubPath)))
			mkdir(dirname($stubPath));
		
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
		unlink($stubPath);
		rmdir(dirname($stubPath));
	}
}
<?php
namespace AntonioPrimera\Artisan\Tests\Unit;

use AntonioPrimera\Artisan\FileRecipe;
use AntonioPrimera\Artisan\Tests\CustomAssertions;
use AntonioPrimera\Artisan\Tests\TestCase;
use AntonioPrimera\Artisan\Tests\TestContext\RelativePathGeneratorCommand;
use AntonioPrimera\Artisan\Tests\TestHelpers;
use AntonioPrimera\Artisan\Tests\Traits\RunsTestCommands;
use AntonioPrimera\FileSystem\File;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;

class FileGenerationTest extends TestCase
{
	use TestHelpers, CustomAssertions, RunsTestCommands;
	
	//--- Actual tests ------------------------------------------------------------------------------------------------
	
	#[Test]
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
	
	#[Test]
	public function if_relative_stub_and_target_paths_are_given_the_paths_will_be_relative_to_the_project_root()
	{
		RelativePathGeneratorCommand::$staticRecipe = [
			'Controller' => [
				'stub' => 'stubs/SampleController.php.stub',
				'target' => 'app/Http/Controllers',
			],
		];
		
		$targetFile = File::instance(base_path('app/Http/Controllers/SomeSpecialController.php'));
		$stubFile = File::instance(base_path('stubs/SampleController.php.stub'));
		
		//setup
		$stubFile->putContents('Sample Controller');
		
		if ($targetFile->exists())
			$targetFile->delete();
		
		//actual test
		$this->assertFileDoesNotExist($targetFile->path);
		
		Artisan::call('make:gen-test-relative', ['name' => 'SomeSpecialController']);
		
		$this->assertFilesExist($targetFile->path);
		$this->assertFileContentsEquals('Sample Controller', $targetFile->path);
		
		//cleanup
		$targetFile->delete();
	}
	
	#[Test]
	public function if_the_target_file_already_exists_it_will_not_be_overwritten()
	{
		RelativePathGeneratorCommand::$staticRecipe = [
			'Controller' => [
				'stub' => 'stubs/SampleController.php.stub',
				'target' => 'Http/Controllers',
			],
		];
		
		//setup
		$stubFile = File::instance(base_path('stubs/SampleController.php.stub'));
		$stubFile->putContents('Sample Controller');
		$targetFile = File::instance(base_path('Http/Controllers/SomeSpecialController.php'));
		$targetFile->putContents('Existing Controller');
		
		//actual test
		$this->assertTrue($targetFile->exists());
		$this->assertFileContentsEquals('Existing Controller', $targetFile->path);
		
		Artisan::call('make:gen-test-relative', ['name' => 'SomeSpecialController']);
		
		$this->assertFileContentsEquals('Existing Controller', $targetFile->path);
		
		//cleanup
		$targetFile->delete();
	}
	
	//--- Testing some custom scenarios -------------------------------------------------------------------------------
	
	#[Test]
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
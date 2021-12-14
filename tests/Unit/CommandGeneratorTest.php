<?php
namespace AntonioPrimera\Artisan\Tests\Unit;

use AntonioPrimera\Artisan\Tests\CustomAssertions;
use AntonioPrimera\Artisan\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class CommandGeneratorTest extends TestCase
{
	use CustomAssertions;
	
	/** @test */
	public function the_generator_command_generator_command_should_generate_a_generator_command()	//awesome test name
	{
		$targetFile = app_path('Console/Commands/Generators/RandomCommand.php');
		$this->cleanup($targetFile);
		
		$this->assertFileDoesNotExist($targetFile);
		
		Artisan::call('make:generator-command', ['name' => 'Generators/RandomCommand']);
		
		$this->assertFileExists($targetFile);
		$this->assertFileContainsStrings([
			'namespace App\\Console\\Commands\\Generators;',
			'class RandomCommand extends FileGeneratorCommand',
		], $targetFile);
		
		$this->cleanup($targetFile);
	}
	
	//--- Protected helpers -------------------------------------------------------------------------------------------
	
	protected function cleanup($targetFile)
	{
		if (file_exists($targetFile))
			unlink($targetFile);
		
		if (is_dir(dirname($targetFile)))
			rmdir(dirname($targetFile));
	}
}
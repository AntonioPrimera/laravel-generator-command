<?php
namespace AntonioPrimera\Artisan\Tests\Unit;

use AntonioPrimera\Artisan\Tests\CustomAssertions;
use AntonioPrimera\Artisan\Tests\TestCase;
use AntonioPrimera\FileSystem\File;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;

class CommandGeneratorTest extends TestCase
{
	use CustomAssertions;
	
	#[Test]
	public function the_command_should_create_a_generator_command()
	{
		$targetFile = File::instance(app_path('Console/Commands/Generators/RandomCommand.php'));
		$targetFolder = $targetFile->folder;
		$targetFolder->delete(true);
		$this->assertFalse($targetFolder->exists());
		
		Artisan::call('make:generator-command', ['name' => 'Generators/RandomCommand']);
		
		$this->assertFileExists($targetFile->path);
		
		$this->assertFileContainsStrings([
			'namespace App\\Console\\Commands\\Generators;',
			'class RandomCommand extends FileGeneratorCommand',
		], $targetFile);
		
		$targetFolder->delete(true);
	}
	
	////--- Protected helpers -------------------------------------------------------------------------------------------
	//
	//protected function cleanup(Folder $targetFolder): void
	//{
	//	if ($targetFolder->exists())
	//		$targetFolder->delete(true);
	//}
}
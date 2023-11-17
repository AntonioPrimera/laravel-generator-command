<?php

namespace AntonioPrimera\Artisan\Tests\Unit;

use AntonioPrimera\Artisan\File;
use AntonioPrimera\Artisan\Tests\TestCase;
use AntonioPrimera\Artisan\Tests\TestHelpers;

class FilePathTest extends TestCase
{
	use TestHelpers;
	
	/** @test */
	public function it_can_handle_a_file()
	{
		$this->cleanupFilesAndFolders();
		
		$filePath = File::create(__DIR__ . '/../TestContext/GeneratedFiles', 'my-file', '.txt');
		$this->assertInstanceOf(File::class, $filePath);
		$this->assertFalse($filePath->folderExists());
		$this->assertFalse($filePath->exists());
		
		$filePath->createFolder();
		$this->assertTrue($filePath->folderExists());
		$this->assertFalse($filePath->exists());
		
		$contents = 'My name is Antonio Primera and I am a developer';
		$filePath->setContents($contents);
		$this->assertTrue($filePath->exists());
		$this->assertEquals($contents, file_get_contents($filePath->getFullPath()));
		$this->assertEquals($contents, $filePath->getContents());
		
		$filePath->replaceInFile(['Antonio Primera' => 'Anthony The First', 'developer' => 'psychologist']);
		$this->assertEquals('My name is Anthony The First and I am a psychologist', $filePath->getContents());
		
		$filePath->delete();
		$this->assertFalse($filePath->exists());
		$this->assertTrue($filePath->folderExists());
		
		$this->cleanupFilesAndFolders();
	}
}
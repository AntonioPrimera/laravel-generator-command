<?php
namespace AntonioPrimera\Artisan\Tests\Unit;

use AntonioPrimera\Artisan\Tests\CustomAssertions;
use AntonioPrimera\Artisan\Tests\TestCase;
use AntonioPrimera\Artisan\Tests\TestHelpers;
use AntonioPrimera\Artisan\Tests\Traits\RunsTestCommands;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;

class BackupAndOverwriteGeneratorTest extends TestCase
{
	use TestHelpers, CustomAssertions;
	use RunsTestCommands {
		cleanupFilesAndFolders as protected traitCleanupFilesAndFolders;
	}
	
	#[Test]
	public function it_can_create_a_simple_file_if_it_does_not_exist()
	{
		$filePath = app_path('simple-file.txt');
		
		$this->cleanupFiles($filePath);
		$this->assertFileDoesNotExist($filePath);
		config(['test-context.replace-me' => 'configured-replace']);
		
		Artisan::call('make:backup-test', ['name' => 'simple-file']);
		
		$this->assertFileExists($filePath);
		$this->assertFileContainsString('configured-replace', $filePath);
		
		//it will not overwrite the file if it already exists
		config(['test-context.replace-me' => 'updated-replace']);
		$this->artisan('make:backup-test', ['name' => 'simple-file'])
			->expectsOutput("Target file $filePath already exists")
			->assertExitCode(0);
		
		$this->assertFileContentsEquals('configured-replace', $filePath);
	}
	
	#[Test]
	public function it_will_overwrite_a_simple_file_if_requested()
	{
		$filePath = app_path('simple-file.txt');
		
		$this->cleanupFiles($filePath);
		$this->assertFileDoesNotExist($filePath);
		config(['test-context.replace-me' => 'configured-replace']);
		
		Artisan::call('make:backup-test', ['name' => 'simple-file']);
		
		$this->assertFileExists($filePath);
		$this->assertFileContentsEquals('configured-replace', $filePath);
		
		//it will overwrite the file if it already exists
		config(['test-context.replace-me' => 'updated-replace']);
		
		$this->artisan('make:backup-test', ['name' => 'simple-file', '--overwrite' => true])
			->assertExitCode(0);
		
		$this->assertFileContentsEquals('updated-replace', $filePath);
	}
	
	#[Test]
	public function it_will_back_up_a_simple_file_if_requested()
	{
		$filePath = app_path('simple-file.txt');
		$backupFilePath = app_path('simple-file.txt.backup');
		
		$this->cleanupFiles($filePath, $backupFilePath);
		$this->assertFileDoesNotExist($filePath);
		$this->assertFileDoesNotExist($backupFilePath);
		config(['test-context.replace-me' => 'configured-replace']);
		
		Artisan::call('make:backup-test', ['name' => 'simple-file']);
		
		$this->assertFileExists($filePath);
		$this->assertFileContentsEquals('configured-replace', $filePath);
		
		//it will back up the file if it already exists
		config(['test-context.replace-me' => 'updated-replace']);
		
		$this->artisan('make:backup-test', ['name' => 'simple-file', '--backup' => true])
			->assertExitCode(0);
		
		$this->assertFileContentsEquals('updated-replace', $filePath);
		$this->assertFileExists($filePath . '.backup');
		$this->assertFileContentsEquals('configured-replace', $filePath . '.backup');
	}
}
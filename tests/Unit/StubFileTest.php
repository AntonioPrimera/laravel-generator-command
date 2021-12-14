<?php
namespace AntonioPrimera\Artisan\Tests\Unit;

use AntonioPrimera\Artisan\Stub;
use AntonioPrimera\Artisan\Tests\TestCase;
use AntonioPrimera\Artisan\Tests\TestHelpers;

class StubFileTest extends TestCase
{
	use TestHelpers;
	
	protected function setUp(): void
	{
		parent::setUp();
		$this->cleanupFilesAndFolders();
	}
	
	protected function tearDown(): void
	{
		parent::tearDown();
		$this->cleanupFilesAndFolders();
	}
	
	/** @test */
	public function a_stub_file_is_created_and_the_content_is_loaded_correctly()
	{
		$file = new Stub(
			__DIR__ . '/../TestContext/stubs/viewStub.blade.php.stub',
			__DIR__ . '/../GeneratedFiles/Blades/TargetPath/MyView.blade.php'
		);
		
		$this->assertEquals(
			file_get_contents(__DIR__ . '/../TestContext/stubs/viewStub.blade.php.stub'),
			$file->getContents()
		);
	}
	
	//--- Target file extension ---------------------------------------------------------------------------------------
	
	/** @test */
	public function by_default_if_no_extension_is_given_no_extension_is_appended()
	{
		$file = new Stub(
			__DIR__ . '/../TestContext/stubs/viewStub.blade.php.stub',
			__DIR__ . '/../GeneratedFiles/Blades/TargetPath/MyView.blade.php'
		);
		
		$this->assertEquals('', $file->getExtension());
		$this->assertEquals(
			$this->realPath(__DIR__ . '/../GeneratedFiles/Blades/TargetPath/MyView.blade.php'),
			$file->getTargetFilePath()
		);
	}
	
	/** @test */
	public function if_a_dedicated_extension_is_given_that_extension_will_be_used()
	{
		$file = new Stub(
			__DIR__ . '/../TestContext/stubs/viewStub.blade.php.stub',
			__DIR__ . '/../GeneratedFiles/Blades/TargetPath/MyView.blade.php'
		);
		
		$file->setExtension('.json');
		
		//the extension is stored without trailing '.'
		$this->assertEquals('json', $file->getExtension());
		
		//the extension will just be added
		$this->assertEquals(
			$this->realPath(__DIR__ . '/../GeneratedFiles/Blades/TargetPath/MyView.blade.php.json'),
			$file->getTargetFilePath()
		);
	}
	
	/** @test */
	public function it_can_be_asked_to_guess_the_extension_from_the_stub_file_name()
	{
		$file = new Stub(
			__DIR__ . '/../TestContext/stubs/view.blade.php',
			__DIR__ . '/../GeneratedFiles/Blades/TargetPath/MyView'
		);
		
		$file->guessExtensionFromStub();
		
		//the extension is stored without trailing '.'
		$this->assertEquals('blade.php', $file->getExtension());
		
		//the extension will just be added
		$this->assertEquals(
			$this->realPath(__DIR__ . '/../GeneratedFiles/Blades/TargetPath/MyView.blade.php'),
			$file->getTargetFilePath()
		);
	}
	
	/** @test */
	public function it_can_be_asked_to_guess_the_extension_from_the_stub_file_name_ignoring_the_stub_suffix()
	{
		$file = new Stub(
			__DIR__ . '/../TestContext/stubs/viewStub.blade.php.stub',
			__DIR__ . '/../GeneratedFiles/Blades/TargetPath/MyView'
		);
		
		$file->guessExtensionFromStub();
		
		//the extension is stored without trailing '.'
		$this->assertEquals('blade.php', $file->getExtension());
		
		//the extension will just be added
		$this->assertEquals(
			$this->realPath(__DIR__ . '/../GeneratedFiles/Blades/TargetPath/MyView.blade.php'),
			$file->getTargetFilePath()
		);
	}
	
	/** @test */
	public function even_if_an_extension_is_guessed_it_will_not_be_added_twice()
	{
		$file = new Stub(
			__DIR__ . '/../TestContext/stubs/viewStub.blade.php.stub',
			__DIR__ . '/../GeneratedFiles/Blades/TargetPath/MyView.blade.php'
		);
		
		$file->guessExtensionFromStub();
		
		//the extension is stored without trailing '.'
		$this->assertEquals('blade.php', $file->getExtension());
		
		//the extension will just be added
		$this->assertEquals(
			$this->realPath(__DIR__ . '/../GeneratedFiles/Blades/TargetPath/MyView.blade.php'),
			$file->getTargetFilePath()
		);
	}
	
	/** @test */
	public function even_if_an_extension_is_set_it_will_not_be_added_twice()
	{
		$file = new Stub(
			__DIR__ . '/../TestContext/stubs/viewStub.blade.php.stub',
			__DIR__ . '/../GeneratedFiles/Blades/TargetPath/MyView.blade.php'
		);
		
		$file->setExtension('blade.php');
		
		//the extension is stored without trailing '.'
		$this->assertEquals('blade.php', $file->getExtension());
		
		//the extension will just be added
		$this->assertEquals(
			$this->realPath(__DIR__ . '/../GeneratedFiles/Blades/TargetPath/MyView.blade.php'),
			$file->getTargetFilePath()
		);
	}
	
	//--- Target file name --------------------------------------------------------------------------------------------
	
	/** @test */
	public function it_can_format_the_file_name_using_a_string_helper()
	{
		$file = new Stub(
			__DIR__ . '/../TestContext/stubs/view.blade.php',
			__DIR__ . '/../GeneratedFiles/Blades/TargetPath/MyView.blade.php'
		);
		
		$file->formatFileName('kebab');
		
		$this->assertEquals(
			$this->realPath(__DIR__ . '/../GeneratedFiles/Blades/TargetPath/my-view.blade.php'),
			$file->getTargetFilePath()
		);
		
		$file->formatFileName('camel');
		
		$this->assertEquals(
			$this->realPath(__DIR__ . '/../GeneratedFiles/Blades/TargetPath/myView.blade.php'),
			$file->getTargetFilePath()
		);
		
		//if the extension is added to the target path, it will also be formatted
		$file->formatFileName('upper');
		
		$this->assertEquals(
			$this->realPath(__DIR__ . '/../GeneratedFiles/Blades/TargetPath/MYVIEW.BLADE.PHP'),
			$file->getTargetFilePath()
		);
		
		//avoid adding the extension to the target path if you need to format the name
		$file = new Stub(
			__DIR__ . '/../TestContext/stubs/view.blade.php',
			__DIR__ . '/../GeneratedFiles/Blades/TargetPath/MyView'
		);
		
		$file->guessExtensionFromStub()
			->formatFileName('upper');
		
		$this->assertEquals(
			$this->realPath(__DIR__ . '/../GeneratedFiles/Blades/TargetPath/MYVIEW.blade.php'),
			$file->getTargetFilePath()
		);
	}
	
	/** @test */
	public function it_can_format_the_file_name_using_a_callable()
	{
		$file = new Stub(
			__DIR__ . '/../TestContext/stubs/view.blade.php',
			__DIR__ . '/../GeneratedFiles/Blades/TargetPath/MyView'
		);
		
		$file->guessExtensionFromStub()
			->formatFileName([$this, 'formatFileName']);
		
		$this->assertEquals(
			$this->realPath(__DIR__ . '/../GeneratedFiles/Blades/TargetPath/MyView_MYVIEW.blade.php'),
			$file->getTargetFilePath()
		);
		
		$file->formatFileName(function ($fileName) {
			return str_replace('My', 'Your', $fileName);
		});
		$this->assertEquals(
			$this->realPath(__DIR__ . '/../GeneratedFiles/Blades/TargetPath/YourView_MYVIEW.blade.php'),
			$file->getTargetFilePath()
		);
	}
	
	//--- File contents -----------------------------------------------------------------------------------------------
	
	/** @test */
	public function it_can_replace_variables_in_the_stub_content()
	{
		$file = new Stub(
			__DIR__ . '/../TestContext/stubs/view.blade.php',
			__DIR__ . '/../GeneratedFiles/Blades/TargetPath/MyView'
		);
		
		$file->replace([
			'DUMMY_TAG' => 'div',
			'DUMMY_SLOT' => 'I am the replacement',
		]);
		
		//the extension will just be added
		$this->assertEquals('<div>I am the replacement</div>', $file->getContents());
	}
	
	//--- Target file generation --------------------------------------------------------------------------------------
	
	/** @test */
	public function it_will_generate_the_target_file_with_the_correct_content()
	{
		$file = new Stub(
			__DIR__ . '/../TestContext/stubs/view.blade.php',
			__DIR__ . '/../TestContext/GeneratedFiles/Blades/TargetPath/MyView'
		);
		
		$file->guessExtensionFromStub()
			->replace([
			'DUMMY_TAG' => 'div',
			'DUMMY_SLOT' => 'I am the replacement',
		]);
		
		$this->assertDirectoryDoesNotExist($this->realPath(__DIR__ . '/../TestContext/GeneratedFiles'));
		
		$file->generate();
		
		$this->assertDirectoryExists($this->realPath(__DIR__ . '/../TestContext/GeneratedFiles/Blades/TargetPath'));
		$this->assertFileExists($this->realPath(__DIR__ . '/../TestContext/GeneratedFiles/Blades/TargetPath/MyView.blade.php'));
		$this->assertEquals('<div>I am the replacement</div>', $file->getContents());
	}
	
	//--- Protected helpers -------------------------------------------------------------------------------------------
	
	public function formatFileName($fileName)
	{
		return $fileName . '_' . strtoupper($fileName);
	}
}
<?php
namespace AntonioPrimera\Artisan\Tests\Unit;

use AntonioPrimera\Artisan\Tests\CustomAssertions;
use AntonioPrimera\Artisan\Tests\TestCase;
use AntonioPrimera\Artisan\Tests\TestHelpers;
use AntonioPrimera\Artisan\Tests\Traits\RunsTestCommands;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;

class FixedNamedGeneratorTest extends TestCase
{
	use TestHelpers, CustomAssertions;
	use RunsTestCommands {
		cleanupFilesAndFolders as protected traitCleanupFilesAndFolders;
	}
	
	#[Test]
	public function it_can_generate_the_files_using_the_stub_names_if_no_name_was_provided_to_the_command()
	{
		$componentFile = app_path('View/Components/OuterLayout.php');
		$bladeFile = resource_path('views/components/layouts/outer.blade.php');
		
		$this->cleanupFiles($componentFile, $bladeFile);
		$this->assertFileDoesNotExist($componentFile);
		$this->assertFileDoesNotExist($bladeFile);
		
		Artisan::call('make:gen-fixed-name');
		
		$this->assertFileExists($componentFile);
		$this->assertFileContainsStrings(
			[
				'namespace App\View\Components;',
				'class OuterLayout extends Component',
			],
			$componentFile
		);
		
		$this->assertFileExists($bladeFile);
		$this->assertFileContainsString("<body class='font-sans text-gray-900 antialiased h-full'>", $bladeFile);
	}
}
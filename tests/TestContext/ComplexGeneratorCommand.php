<?php
namespace AntonioPrimera\Artisan\Tests\TestContext;

use AntonioPrimera\Artisan\FileGeneratorCommand;
use AntonioPrimera\Artisan\FileRecipe;
use AntonioPrimera\FileSystem\Folder;

class ComplexGeneratorCommand extends FileGeneratorCommand
{
	protected $signature = 'make:gen-test-complex {name}';
	
	protected function recipe(): array
	{
		return [
			'ComponentClass' => FileRecipe::create()
				->withTargetFolder(__DIR__ . '/GeneratedFiles/Components')
				->withStub(__DIR__ . '/stubs/Component.php')
				->withRootNamespace('AntonioPrimera\\My\\Namespace')
				->withReplace([
					'DUMMY_MESSAGE' => 'my-message',
				]),
			
			'ComponentClassStub' => FileRecipe::create()
				->withTargetFolder(__DIR__ . '/GeneratedFiles/Components/Complex')
				->withStub(__DIR__ . '/stubs/ComponentStub.php.stub')
				->withRootNamespace('AntonioPrimera\\Complex\\Namespace')
				->withReplace([
					'DUMMY_MESSAGE' => 'my-complex-message',
				]),
			
			'BladeFile' => FileRecipe::create()
				->withStub(__DIR__ . '/stubs/view.blade.php')
				->withTargetFolder(__DIR__ . '/GeneratedFiles/Blades')
				->withExtension('bladex.phpx')
				->withReplace([
					'DUMMY_TAG' => 'divx',
					'DUMMY_SLOT' => 'flick',
				])
				->withFileNameTransformer('kebab'),
			
			'ComplexBladeFile' => FileRecipe::create()
				->withTargetFolder(__DIR__ . '/GeneratedFiles/Blades/Complex')
				->withStub(__DIR__ . '/stubs/viewStub.blade.php.stub')
				->withReplace([
					'DUMMY_TAG' => 'divx',
					'DUMMY_SLOT' => 'flick',
				]),
			
			'JsonFile' => FileRecipe::create()
				->withTargetFolder(Folder::instance(__DIR__ . '/GeneratedFiles'))
				->withStub(__DIR__ . '/stubs/jsonFile.json.stub')
				->withFileNameTransformer('upper')
				->withExtension('.json')
				->withReplace([
					'DUMMY_KEY_1' => 'tic',
					'DUMMY_VALUE_1' => 'tac',
					'DUMMY_KEY_2' => 'marco',
					'DUMMY_VALUE_2' => 'polo',
				]),
			
			'JsonFile2' => FileRecipe::create()
				->withTargetFolder(__DIR__ . '/GeneratedFiles/Json')
				->withStub(__DIR__ . '/stubs/jsonFile.json.stub')
				->withFileNameTransformer('strtoupper')
				->withReplace([
					'DUMMY_KEY_1' => 'tic',
					'DUMMY_VALUE_1' => 'tac',
					'DUMMY_KEY_2' => 'marco',
					'DUMMY_VALUE_2' => 'polo',
				]),
		];
	}
	
}
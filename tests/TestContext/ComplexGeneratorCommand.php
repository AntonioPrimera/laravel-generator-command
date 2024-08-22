<?php
namespace AntonioPrimera\Artisan\Tests\TestContext;

use AntonioPrimera\Artisan\FileRecipe;
use AntonioPrimera\FileSystem\Folder;

class ComplexGeneratorCommand extends \AntonioPrimera\Artisan\FileGeneratorCommand
{
	protected $signature = 'make:gen-test-complex {name}';
	
	protected function recipe(): array
	{
		return [
			//'ComponentClass' => [
			//	'target' => __DIR__ . '/GeneratedFiles/Components',
			//	'stub' => __DIR__ . '/stubs/Component.php',
			//	'rootNamespace' => 'AntonioPrimera\\My\\Namespace',
			//	'replace' => [
			//		'DUMMY_MESSAGE' => 'my-message',
			//	]
			//],
			
			'ComponentClass' => FileRecipe::create()
				->withTargetFolder(__DIR__ . '/GeneratedFiles/Components')
				->withStub(__DIR__ . '/stubs/Component.php')
				->withRootNamespace('AntonioPrimera\\My\\Namespace')
				->withReplace([
					'DUMMY_MESSAGE' => 'my-message',
				]),
			
			//'ComponentClassStub' => [
			//	'target' => __DIR__ . '/GeneratedFiles/Components/Complex',
			//	'stub' => __DIR__ . '/stubs/ComponentStub.php.stub',
			//	'rootNamespace' => 'AntonioPrimera\\Complex\\Namespace',
			//	'replace' => [
			//		'DUMMY_MESSAGE' => 'my-complex-message',
			//	]
			//],
			
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
			//	[
			//	'target' => __DIR__ . '/GeneratedFiles/Blades',
			//	'stub' => __DIR__ . '/stubs/view.blade.php',
			//	'extension' => 'bladex.phpx',
			//	'replace' => [
			//		'DUMMY_TAG' => 'divx',
			//		'DUMMY_SLOT' => 'flick',
			//	],
			//	'fileNameFormat' => 'kebab',
			//],
			
			//'ComplexBladeFile' => [
			//	'target' => __DIR__ . '/GeneratedFiles/Blades/Complex',
			//	'stub' => __DIR__ . '/stubs/viewStub.blade.php.stub',
			//	'replace' => [
			//		'DUMMY_TAG' => 'divx',
			//		'DUMMY_SLOT' => 'flick',
			//	],
			//],
			
			'ComplexBladeFile' => FileRecipe::create()
				->withTargetFolder(__DIR__ . '/GeneratedFiles/Blades/Complex')
				->withStub(__DIR__ . '/stubs/viewStub.blade.php.stub')
				->withReplace([
					'DUMMY_TAG' => 'divx',
					'DUMMY_SLOT' => 'flick',
				]),
			
			//'JsonFile' => [
			//	'target' => Folder::instance(__DIR__ . '/GeneratedFiles'),
			//	'stub' => __DIR__ . '/stubs/jsonFile.json.stub',
			//	'fileNameFormat' => 'upper',
			//	'extension' => '.json',
			//	'replace' => [
			//		'DUMMY_KEY_1' => 'tic',
			//		'DUMMY_VALUE_1' => 'tac',
			//		'DUMMY_KEY_2' => 'marco',
			//		'DUMMY_VALUE_2' => 'polo',
			//	],
			//],
			
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
			
			//'JsonFile2' => [
			//	'target' => __DIR__ . '/GeneratedFiles/Json',
			//	'stub' => __DIR__ . '/stubs/jsonFile.json.stub',
			//	'fileNameTransformer' => 'strtoupper',
			//	'replace' => [
			//		'DUMMY_KEY_1' => 'tic',
			//		'DUMMY_VALUE_1' => 'tac',
			//		'DUMMY_KEY_2' => 'marco',
			//		'DUMMY_VALUE_2' => 'polo',
			//	],
			//],
			
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
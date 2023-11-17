<?php

namespace AntonioPrimera\Artisan\Tests\TestContext;

//use function PHPUnit\TestFixture\func;

use AntonioPrimera\Artisan\File;

class ComplexGeneratorCommand extends \AntonioPrimera\Artisan\FileGeneratorCommand
{
	protected $signature = 'make:gen-test-complex {name}';
	
	protected function recipe(): array
	{
		return [
			'ComponentClass' => [
				'target' => __DIR__ . '/GeneratedFiles/Components',
				'stub' => __DIR__ . '/stubs/Component.php',
				'rootNamespace' => 'AntonioPrimera\\My\\Namespace',
				'replace' => [
					'DUMMY_MESSAGE' => 'my-message',
				]
			],
			
			'ComponentClassStub' => [
				'target' => __DIR__ . '/GeneratedFiles/Components/Complex',
				'stub' => __DIR__ . '/stubs/ComponentStub.php.stub',
				'rootNamespace' => 'AntonioPrimera\\Complex\\Namespace',
				'replace' => [
					'DUMMY_MESSAGE' => 'my-complex-message',
				]
			],
			
			'BladeFile' => [
				'target' => __DIR__ . '/GeneratedFiles/Blades',
				'stub' => __DIR__ . '/stubs/view.blade.php',
				'extension' => 'bladex.phpx',
				'replace' => [
					'DUMMY_TAG' => 'divx',
					'DUMMY_SLOT' => 'flick',
				],
				'fileNameFormat' => 'kebab',
			],
			
			'ComplexBladeFile' => [
				'target' => __DIR__ . '/GeneratedFiles/Blades/Complex',
				'stub' => __DIR__ . '/stubs/viewStub.blade.php.stub',
				'replace' => [
					'DUMMY_TAG' => 'divx',
					'DUMMY_SLOT' => 'flick',
				],
			],
			
			'JsonFile' => [
				'target' => File::create(folder: __DIR__ . '/GeneratedFiles', extension: '.json'),
				'stub' => __DIR__ . '/stubs/jsonFile.json.stub',
				'fileNameFormat' => 'upper',
				'replace' => [
					'DUMMY_KEY_1' => 'tic',
					'DUMMY_VALUE_1' => 'tac',
					'DUMMY_KEY_2' => 'marco',
					'DUMMY_VALUE_2' => 'polo',
				],
			],
			
			'JsonFile2' => [
				'target' => __DIR__ . '/GeneratedFiles/Json',
				'stub' => __DIR__ . '/stubs/jsonFile.json.stub',
				'fileNameTransformer' => 'strtoupper',
				'replace' => [
					'DUMMY_KEY_1' => 'tic',
					'DUMMY_VALUE_1' => 'tac',
					'DUMMY_KEY_2' => 'marco',
					'DUMMY_VALUE_2' => 'polo',
				],
			],
		];
	}
	
}
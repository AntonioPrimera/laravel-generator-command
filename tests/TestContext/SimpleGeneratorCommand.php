<?php

namespace AntonioPrimera\Artisan\Tests\TestContext;

use AntonioPrimera\Artisan\FileGeneratorCommand;

class SimpleGeneratorCommand extends FileGeneratorCommand
{
	protected $signature = 'make:gen-test-simple {name}';
	
	protected array $recipe = [
		'ComponentClass' => [
			'path' => __DIR__ . '/GeneratedFiles/Components',
			'stub' => __DIR__ . '/stubs/Component.php',
			'rootNamespace' => 'AntonioPrimera\\My\\Namespace',
			'replace' => [
				'DUMMY_MESSAGE' => 'my-message',
			]
		],
	];
	
}
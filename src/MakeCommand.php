<?php

namespace AntonioPrimera\Artisan;

class MakeCommand extends FileGeneratorCommand
{
	protected $signature = 'make:generator-command {name}';
	protected $description = 'Create a new File Generator Command';
	
	protected function recipe(): array
	{
		return [
			'Generator Command' => FileRecipe::create(
					__DIR__ . '/stubs/GeneratorCommandStub.php.stub',
					app_path('Console/Commands/'),
					'App\\Console\\Commands'
				),
		];
	}
}
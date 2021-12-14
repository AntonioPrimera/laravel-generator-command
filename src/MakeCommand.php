<?php

namespace AntonioPrimera\Artisan;

class MakeCommand extends FileGeneratorCommand
{
	protected $signature = 'make:generator-command {name}';
	protected $description = 'Create a new File Generator Command';
	
	protected function recipe(): array
	{
		$recipe = new FileRecipe(
			__DIR__ . '/stubs/GeneratorCommandStub.php.stub',
			app_path('Console/Commands')
		);
		
		$recipe->rootNamespace = 'App\\Console\\Commands';
		
		return [
			'Generator Command' => $recipe,
		];
	}
}
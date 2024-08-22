<?php

namespace AntonioPrimera\Artisan;

use AntonioPrimera\Artisan\FileRecipes\CommandRecipe;
use AntonioPrimera\FileSystem\OS;

class MakeCommand extends FileGeneratorCommand
{
	protected $signature = 'make:generator-command {name}';
	protected $description = 'Create a new File Generator Command';
	
	protected function recipe(): CommandRecipe
	{
		return new CommandRecipe(OS::path(__DIR__, 'stubs/GeneratorCommandStub.php.stub'));
	}
}
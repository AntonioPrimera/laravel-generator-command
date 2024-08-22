<?php
namespace AntonioPrimera\Artisan\FileRecipes;

use AntonioPrimera\Artisan\FileRecipe;
use Illuminate\Support\Str;

class CommandRecipe extends FileRecipe
{
	
	public function __construct(string $stub)
	{
		parent::__construct(
			stub: $stub,
			targetFolder: app_path('Console/Commands'),
			rootNamespace: 'App\\Console\\Commands',
			scope: 'Command',
			extension: 'php',
		);
	}
}
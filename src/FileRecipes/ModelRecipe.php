<?php
namespace AntonioPrimera\Artisan\FileRecipes;

use AntonioPrimera\Artisan\FileRecipe;
use Illuminate\Support\Str;

class ModelRecipe extends FileRecipe
{
	
	public function __construct(string $stub)
	{
		parent::__construct(
			stub: $stub,
			targetFolder: app_path('Models'),
			rootNamespace: 'App\\Models',
			scope: 'Model',
			extension: 'php',
		);
	}
}
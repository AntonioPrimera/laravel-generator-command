<?php
namespace AntonioPrimera\Artisan\FileRecipes;

use AntonioPrimera\Artisan\FileRecipe;
use Illuminate\Support\Str;

class ControllerRecipe extends FileRecipe
{
	
	public function __construct(string $stub)
	{
		parent::__construct(
			stub: $stub,
			targetFolder: app_path('Http/Controllers'),
			rootNamespace: 'App\\Http\\Controllers',
			scope: 'Controller',
			extension: 'php',
		);
	}
}
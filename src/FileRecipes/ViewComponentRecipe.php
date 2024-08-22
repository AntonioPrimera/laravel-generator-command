<?php
namespace AntonioPrimera\Artisan\FileRecipes;

use AntonioPrimera\Artisan\FileRecipe;
use Illuminate\Support\Str;

class ViewComponentRecipe extends FileRecipe
{
	
	public function __construct(string $stub)
	{
		parent::__construct(
			stub: $stub,
			targetFolder: app_path('View/Components'),
			rootNamespace: 'App\\View\\Components',
			scope: 'View Component',
			extension: 'php',
			fileNameTransformer: [$this, 'createClassFilename']
		);
	}
	
	protected function createClassFilename(string $fileName): string
	{
		return Str::studly($fileName);
	}
}
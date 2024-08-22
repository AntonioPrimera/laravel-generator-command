<?php
namespace AntonioPrimera\Artisan\FileRecipes;

use AntonioPrimera\Artisan\FileRecipe;
use AntonioPrimera\FileSystem\File;
use AntonioPrimera\FileSystem\OS;
use Illuminate\Support\Str;

class StyleSheetRecipe extends FileRecipe
{
	
	public function __construct(string $stub)
	{
		parent::__construct(
			stub: $stub,
			targetFolder: resource_path('css'),
			scope: 'Stylesheet File',
		);
	}
}
<?php
namespace AntonioPrimera\Artisan\FileRecipes;

use AntonioPrimera\Artisan\FileRecipe;
use AntonioPrimera\FileSystem\File;
use AntonioPrimera\FileSystem\OS;
use Illuminate\Support\Str;

class ConfigRecipe extends FileRecipe
{
	
	public function __construct(string $stub)
	{
		parent::__construct(
			stub: $stub,
			targetFolder: config_path(),
			scope: 'Config File',
			extension: 'php',
		);
	}
}
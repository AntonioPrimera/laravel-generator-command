<?php
namespace AntonioPrimera\Artisan\FileRecipes;

use AntonioPrimera\Artisan\FileRecipe;
use AntonioPrimera\FileSystem\File;
use AntonioPrimera\FileSystem\OS;
use Illuminate\Support\Str;

class LangRecipe extends FileRecipe
{
	
	public function __construct(string $stub, string|null $localeFolder = null)
	{
		parent::__construct(
			stub: $stub,
			targetFolder: lang_path($localeFolder ?? ''),
			scope: 'Language File',
		);
	}
}
<?php
namespace AntonioPrimera\Artisan\FileRecipes;

use AntonioPrimera\Artisan\FileRecipe;
use AntonioPrimera\FileSystem\File;
use AntonioPrimera\FileSystem\OS;
use Illuminate\Support\Str;

class BladeRecipe extends FileRecipe
{
	
	/**
	 * Provide the file stub and the relative path to the views folder (e.g. 'components/site')
	 */
	public function __construct(string $stub, string $viewsRelativePath = '')
	{
		//ray(OS::path(resource_path('views'), $this->relativePath($viewsRelativePath)));
		//ray($this->relativePath($viewsRelativePath));
		parent::__construct(
			stub: $stub,
			targetFolder: OS::path(resource_path('views'), $this->relativePath($viewsRelativePath)),
			scope: 'Blade View',
			extension: 'blade.php',
			fileNameTransformer: [$this, 'createBladeFilename'],
			relativePathTransformer: [$this, 'relativePath']
		);
	}
	
	/**
	 * Determine the relative path to the views folder, by transforming the provided path
	 * to kebab-case and normalizing the path separators
	 * e.g. 'Components/SiteComponents/HeroSection' -> 'components/site-components/hero-section'
	 */
	protected function relativePath($viewsRelativePath): string
	{
		return OS::path(...arrayMap(OS::pathParts($viewsRelativePath), fn ($part) => $part ? Str::kebab($part) : null));
	}
	
	protected function createBladeFilename(string $fileName): string
	{
		return Str::kebab($fileName);
	}
}
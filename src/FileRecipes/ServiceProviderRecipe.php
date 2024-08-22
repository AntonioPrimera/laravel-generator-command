<?php
namespace AntonioPrimera\Artisan\FileRecipes;

use AntonioPrimera\Artisan\FileRecipe;
use Illuminate\Support\Str;

class ServiceProviderRecipe extends FileRecipe
{
	
	public function __construct(string $stub)
	{
		parent::__construct(
			stub: $stub,
			targetFolder: app_path('Providers'),
			rootNamespace: 'App\\Providers',
			scope: 'Service Provider',
			extension: 'php',
		);
	}
}
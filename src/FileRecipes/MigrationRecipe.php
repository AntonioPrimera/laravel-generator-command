<?php
namespace AntonioPrimera\Artisan\FileRecipes;

use AntonioPrimera\Artisan\FileRecipe;
use Illuminate\Support\Str;

class MigrationRecipe extends FileRecipe
{
	
	public function __construct(string $stub)
	{
		parent::__construct(
			stub: $stub,
			targetFolder: database_path('migrations'),
			scope: 'Migration',
			extension: 'php',
			fileNameTransformer: [$this, 'createMigrationFilename']
		);
	}
	
	protected function createMigrationFilename(string $fileName): string
	{
		return date('Y_m_d_His') . '_' . Str::snake($fileName);
	}
}
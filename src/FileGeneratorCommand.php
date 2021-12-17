<?php

namespace AntonioPrimera\Artisan;

use AntonioPrimera\Artisan\Exceptions\InvalidRecipeException;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

abstract class FileGeneratorCommand extends Command
{
	
	/**
	 * The argument name holding the
	 * target file name.
	 *
	 * e.g. $signature = 'make:admin-page {name}'
	 *
	 * @var string
	 */
	protected string $nameArgument = 'name';
	
	///**
	// * @var Collection
	// */
	//protected Collection $nameParts;
	
	//a collection of file recipes
	protected array $recipe = [
		//'Component' => [
		//	'scope' => 'Admin Panel Livewire Component',
		//	'stub' => 'stubPath/relative/to/the/package/root',
		//	'extension' => 'extension', 	//could be derived from the stub name (by removing the last .stub)
		//	'fileNameFormat' => 'kebab',	//or function($fileName) { return Str::kebab($fileName); }
		//	'replace' => [
		//		//'DUMMY_NAMESPACE' 		=> $this->getNamespace(),
		//		//'DUMMY_CLASS' 			=> $this->nameParts->last(),
		//		//'DUMMY_BLADE_REFERENCE' => $this->getBladeReference(),
		//	],
		//],
	];
	
	/**
	 * @throws Exception
	 */
	public function handle()
	{
		////set up the name parts
		//$this->setup();
		
		$recipe = $this->recipe() ?: $this->recipe;
		
		try {
			foreach ($recipe as $key => $fileRecipe) {
				$path = $this->createFileFromRecipe($fileRecipe);
				$fileScope = $fileRecipe instanceof FileRecipe
					? ($fileRecipe->scope ?: $key)
					: ($fileRecipe['scope'] ?? $key);
				
				$this->line("<fg=green;>Created new $fileScope at: $path</>");
			}
		} catch (InvalidRecipeException $exception) {
			$this->line("<fg=red>Error: {$exception->getMessage()}</>");
			return 1;
		}
		
		return 0;
	}
	
	/**
	 * Override this method and return the recipe if you
	 * need a more complex or dynamic recipe, which
	 * can't be specified in $recipe.
	 *
	 * Each recipe must contain the following items:
	 * 	- stub: the absolute path to the stub file
	 * 	- path: the root path for the resulting file
	 *
	 * Each recipe can / should contain the following items (depending on the desired result):
	 * 	- rootNamespace: the root namespace, if the file is a class and should have a namespace
	 * 	- extension: the extension for the resulting file (if it can not be determined from the stub file)
	 * 	- replace: an array of 'search' => 'replace with' key-value pairs
	 * 	- fileNameFormat: optionally, a format (kebab / slug / camel etc.) or a format function
	 *
	 * @return array
	 */
	protected function recipe(): array
	{
		return [];
	}
	
	/**
	 * The default replacements for every file. The $fileRecipe is
	 * the recipe array for the current file, either an entry
	 * in $this->recipe or $this->recipe().
	 *
	 * @param array $fileRecipe
	 *
	 * @return array
	 */
	protected function defaultReplacements(FileRecipe $fileRecipe): array
	{
		$nameParts = $this->nameParts();
		
		return [
			'DUMMY_NAMESPACE' => $this->getPsr4Namespace(
					$nameParts,
					$fileRecipe->rootNamespace ?: 'App'
				),
			'DUMMY_CLASS' 	  => $nameParts->last(),
		];
	}
	
	/**
	 * @throws Exception
	 */
	protected function createFileFromRecipe(array | FileRecipe $fileRecipe): string
	{
		//validate the recipe
		$recipe = $this->createRecipeInstance($fileRecipe);
		
		$stub = new Stub(
			//determine the absolute path to the stub
			$this->determineAbsolutePath(
				$recipe->stub,
				$recipe->rootPath
			),
			//determine the absolute path to the target file
			$this->determineAbsolutePath(
				$recipe->path,
				$recipe->rootPath
			) . DIRECTORY_SEPARATOR . $this->getNameArgument()
		);
		
		$stub->formatFileName($recipe->fileNameFormat)
			->setExtension($recipe->extension)
			->replace($this->determineReplacements($recipe))
			->generate();
		
		return $stub->getTargetFilePath();
	}
	
	/**
	 * Normalize an array recipe, by turning it into a recipe instance
	 *
	 * @param FileRecipe|array $fileRecipe
	 *
	 * @return FileRecipe
	 * @throws Exception
	 */
	protected function createRecipeInstance(FileRecipe | array $fileRecipe): FileRecipe
	{
		if ($fileRecipe instanceof FileRecipe)
			return $fileRecipe;
		
		$this->validateFileRecipe($fileRecipe);
		
		$recipe = new FileRecipe($fileRecipe['stub'], $fileRecipe['path'] ?? '');
		
		$recipe->rootPath = $fileRecipe['rootPath'] ?? null;
		$recipe->extension = $fileRecipe['extension'] ?? null;
		$recipe->replace = $fileRecipe['replace'] ?? [];
		$recipe->rootNamespace = $fileRecipe['rootNamespace'] ?? null;
		$recipe->fileNameFormat = $fileRecipe['fileNameFormat'] ?? null;
		
		return $recipe;
	}
	
	//--- Protected helpers -------------------------------------------------------------------------------------------
	
	/**
	 * Return the absolute path for a given path. If already absolute, it just returns the given path.
	 * If relative, it applies $rootPath if it is callable (e.g. config_path($recipePath)),
	 * otherwise it concatenates the path to the root path (root path must point to
	 * an existing folder). If recipePath is callable, its result is returned.
	 *
	 * @param mixed $recipePath
	 * @param mixed $recipeRootPath
	 *
	 * @return string
	 * @throws InvalidRecipeException
	 */
	protected function determineAbsolutePath(mixed $recipePath, mixed $recipeRootPath): string
	{
		if ($this->isAbsolutePath($recipePath))
			return $recipePath;
		
		if (is_callable($recipePath))
			return call_user_func($recipePath);
		
		//if $rootPath is empty / null, it is considered to be app_path()
		$rootPath = $recipeRootPath ?: 'app_path';
		
		if (is_callable($rootPath))
			return call_user_func($rootPath, $recipePath);
		
		if ($this->isAbsolutePath($rootPath))
			return rtrim($rootPath, '/\\') . DIRECTORY_SEPARATOR . $recipePath;
		
		throw new InvalidRecipeException(
			'The given recipe path must be absolute'
			. " OR the recipe rootPath must be callable or an absolute path (path: $recipePath) (rootPath: $rootPath)"
		);
	}
	
	protected function isAbsolutePath($path): bool
	{
		return in_array($path[0], ['/', '\\']);
	}
	
	protected function getPsr4Namespace(Collection $nameParts, string $rootNamespace): string
	{
		//implode the name parts, except the last part (the last part will be the class name)
		return collect(trim($rootNamespace, '\\'))
			->merge($nameParts->take($nameParts->count() - 1))
			->implode('\\');
	}
	
	protected function determineReplacements(FileRecipe $fileRecipe): array
	{
		return array_merge($this->defaultReplacements($fileRecipe), $fileRecipe->replace);
	}
	
	protected function nameParts(): Collection
	{
		return Str::of($this->getNameArgument())
			->replace('/', DIRECTORY_SEPARATOR)
			->replace('\\', DIRECTORY_SEPARATOR)
			->explode(DIRECTORY_SEPARATOR)
			->filter();
	}
	
	/**
	 * @throws Exception
	 */
	protected function validateFileRecipe($fileRecipe)
	{
		if (!is_array($fileRecipe))
			throw new InvalidRecipeException('Invalid file recipe: must be an array');
		
		if (!is_string($fileRecipe['stub'] ?? null))
			throw new InvalidRecipeException('Invalid file recipe: missing path to stub file');
		
		if (!is_string($fileRecipe['path'] ?? null))
			throw new InvalidRecipeException('Invalid file recipe: missing path for destination file');
		
		if (isset($fileRecipe['replace']) && !is_array($fileRecipe['replace']))
			throw new InvalidRecipeException('Invalid file recipe: replace item must be an associative array');
	}
	
	protected function getNameArgument(): array | null | string
	{
		return $this->argument($this->nameArgument);
	}
}
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
	 * The argument name holding the target file name.
	 * e.g. $signature = 'make:admin-page {name}'
	 * @var string
	 */
	protected string $nameArgument = 'name';
	
	//a collection of file recipes
	protected array $recipe = [];
	
	/**
	 * @throws Exception
	 */
	public function handle()
	{
		if ($this->isDryRun()) {
			$this->warn("Dry run - no files will be created");
			$this->newLine();
		}
		
		$recipe = $this->recipe() ?: $this->recipe;
		$createdFiles = [];
		
		try {
			$this->beforeFileCreation($this->isDryRun(), $recipe);										 //  -->>> Hook
			
			foreach ($recipe as $key => $fileRecipe) {
				$stub = $this->createFileFromRecipe($fileRecipe);
				$fileScope = $fileRecipe instanceof FileRecipe
					? ($fileRecipe->scope ?: $key)
					: ($fileRecipe['scope'] ?? $key);
				
				$createdFiles[] = $stub->getTargetFilePath();
				$this->info("Created new $fileScope at: {$stub->getTargetFilePath()}");
				
				//output the target file contents if in dry-run mode (debug mode)
				if ($this->isDryRun())
					$this->outputStubContentsToConsole($stub);
			}
			
			$this->afterFileCreation($this->isDryRun(), $createdFiles, $recipe);			    		 //  -->>> Hook
		} catch (InvalidRecipeException $exception) {
			$this->line("<fg=red>Error: {$exception->getMessage()}</>");
			$this->cleanupAfterError($this->isDryRun(), $createdFiles, $recipe);					     //  -->>> Hook
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
	
	//--- Hooks ---------------------------------------------------------------------------------------------------->>>
	
	/**
	 * Hook - code to run before file generation
	 */
	protected function beforeFileCreation(bool $isDryRun, array $recipe)
	{
		//add any code which should be run before file generation
	}
	
	/**
	 * Hook - code to run after successful file generation
	 *
	 * @param bool $isDryRun 		- whether this command is run in test mode (Dry Run)
	 * @param array $createdFiles 	- a list of files which were created by this command (absolute paths)
	 * @param array $recipe 		- the recipe used to generate the files
	 */
	protected function afterFileCreation(bool $isDryRun, array $createdFiles, array $recipe)
	{
		//add any code which should be run after file generation
	}
	
	/**
	 * Hook - code to run after a failure during file generation
	 *
	 * @param bool $isDryRun - whether this command is run in test mode (Dry Run)
	 * @param array $createdFiles - a list of files which were created by this command (absolute paths)
	 */
	protected function cleanupAfterError(bool $isDryRun, array $createdFiles, array $recipe)
	{
		$this->info('Starting cleanup procedure...');
		$successfulCleanup = true;
		
		//try to remove all generated files
		foreach ($createdFiles as $createdFile) {
			try {
				unlink($createdFile);
				$this->info("Cleanup - removed generated file: $createdFile");
			} catch (Exception $exception) {
				$this->error("Cleanup - failed to remove generated file: $createdFile");
				$successfulCleanup = false;
			}
		}
		
		if ($successfulCleanup)
			$this->info('Cleanup finished!');
		else
			$this->warn(
				'Cleanup finished with issues! Please check the cleanup log above and remove the files manually'
			);
	}
	
	//--- Protected helpers -------------------------------------------------------------------------------------------
	
	/**
	 * @throws Exception
	 */
	protected function createFileFromRecipe(array | FileRecipe $fileRecipe): Stub
	{
		//validate the recipe
		$recipe = $this->createRecipeInstance($fileRecipe);
		
		//setup the stub
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
		
		//prepare the stub, based on the recipe data
		$stub->formatFileName($recipe->fileNameFormat)
			->setExtension($recipe->extension)
			->replace($this->determineReplacements($recipe));
		
		//create the files if it's not a dry run
		if (!$this->isDryRun())
			$stub->generate();
		
		return $stub;
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
	
	/**
	 * The default replacements for every file. The $fileRecipe is
	 * the recipe array for the current file, either an entry
	 * in $this->recipe or $this->recipe().
	 *
	 * @param FileRecipe $fileRecipe
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
	
	protected function isDryRun(): bool
	{
		return $this->hasOption('dry-run') ? $this->option('dry-run') : false;
	}
	
	protected function outputStubContentsToConsole(Stub $stub)
	{
		$this->newLine();
		$this->warn("Target file contents:");
		$this->newLine();
		
		$lines = Str::of($stub->getContents())->explode("\n");
		
		foreach ($lines->take(30) as $line) {
			$this->line($line);
		}
		
		if ($lines->count() > 30) {
			$this->newLine();
			$this->warn("... (only the first 30 lines are shown) ...");
		}
		
		$this->newLine();
	}
}
<?php

namespace AntonioPrimera\Artisan;

use AntonioPrimera\Artisan\Exceptions\TargetFileExistsException;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

abstract class FileGeneratorCommand extends Command
{
	/**
	 * The argument name holding the target file name.
	 * e.g. $signature = 'make:admin-page {name}'
	 */
	protected string $nameArgument = 'name';
	
	//a collection of file recipes
	protected array $recipe = [];
	
	//the cached recipe for the current command execution (do not use directly, use getRecipe() instead)
	protected array|null $_cachedFileRecipeList = null;
	protected array $createdFiles = [];
	
	public function handle(): int
	{
		if ($this->isDryRun()) {
			$this->warn("Dry run - no files will be created");
			$this->newLine();
		}
		
		$recipe = $this->getRecipe();
		
		try {
			$this->beforeFileCreation();																//  -->>> Hook
			
			foreach ($recipe as $fileRecipe) {
				/* @var FileRecipe $fileRecipe */
				
				try {
					$fileRecipe->run($this->getTargetRelativePath(), $this->getTargetFileName(), $this->isDryRun());
					
					$createdFile = $fileRecipe->target->getFullPath();
					$this->createdFiles[] = $createdFile;
					$this->info("Created new $fileRecipe->scope at: $createdFile");
				} catch (TargetFileExistsException $exception) {
					$this->warn($exception->getMessage());
				}
				
				
				//output the target file contents if in dry-run mode (debug mode)
				//if ($this->isDryRun())
				//	$this->outputStubContentsToConsole($stub);
			}
			
			$this->afterFileCreation();			    													//  -->>> Hook
		} catch (Exception $exception) {
			if (App::environment('testing')) {
				dump('Created files:', $this->createdFiles);
				throw $exception;
			}
			
			$this->error("Error: {$exception->getMessage()}");
			$this->cleanupAfterError();					     											//  -->>> Hook
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
	 * 	- stub: the absolute path to the stub file (string or File instance)
	 * 	- target: the root path for the resulting file (string or File instance)
	 *
	 * Each recipe can / should contain the following items (depending on the desired result):
	 * 	- rootNamespace: the root namespace, if the file is a class and should have a namespace
	 * 	- extension: the extension for the resulting file (if it can not be determined from the stub file)
	 * 	- replace: an array of 'search' => 'replace with' key-value pairs
	 * 	- fileNameFormat: optionally, a callable or a static function name of Illuminate\Support\Str
	 */
	protected function recipe(): array
	{
		return [];
	}
	
	//--- Hooks ---------------------------------------------------------------------------------------------------->>>
	
	/**
	 * Hook - code to run before file generation
	 */
	protected function beforeFileCreation()
	{
		//add any code which should be run before file generation
	}
	
	/**
	 * Hook - code to run after successful file generation
	 */
	protected function afterFileCreation()
	{
		//add any code which should be run after file generation
	}
	
	/**
	 * Hook - code to run after a failure during file generation
	 */
	protected function cleanupAfterError(): void
	{
		$this->info('Starting cleanup procedure...');
		$successfulCleanup = true;
		
		//try to remove all generated files
		foreach ($this->createdFiles as $createdFile) {
			try {
				unlink($createdFile);
				$this->info("Cleanup - removed generated file: $createdFile");
			} catch (Exception $exception) {
				$this->error("Cleanup - failed to remove generated file: $createdFile. Error: {$exception->getMessage()}.");
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
	
	public function getNameArgument(): string
	{
		return $this->argument($this->nameArgument);
	}
	
	/**
	 * Retrieves the path part of the name argument
	 */
	public function getTargetRelativePath(): string
	{
		$path = pathinfo($this->getNameArgument(), PATHINFO_DIRNAME);
		return $path === '.' ? '' : $path;
	}
	
	/**
	 * Retrieves the file name part of the name argument
	 */
	public function getTargetFileName(): string
	{
		return pathinfo($this->getNameArgument(), PATHINFO_FILENAME);
	}
	
	//--- Protected helpers -------------------------------------------------------------------------------------------
	
	protected function getRecipe(): array
	{
		if (!$this->_cachedFileRecipeList) {
			//transform all items in the recipe to FileRecipe instances
			$this->_cachedFileRecipeList = Collection::wrap($this->recipe() ?: $this->recipe)
				->map(fn ($fileRecipe, $key) => $this->fileRecipeInstance($fileRecipe, $key))
				->toArray();
		}
		
		return $this->_cachedFileRecipeList;
	}
	
	///**
	// * Replace DUMMY_NAMESPACE and DUMMY_CLASS with the default values
	// */
	//protected function defaultReplacements(array|FileRecipe $fileRecipe): array
	//{
	//	$rootNamespace = $fileRecipe instanceof FileRecipe
	//		? $fileRecipe->rootNamespace
	//		: ($fileRecipe['rootNamespace'] ?? null);
	//
	//	return [
	//		'DUMMY_NAMESPACE' => $this->getPsr4Namespace($rootNamespace ?: 'App'),
	//		'DUMMY_CLASS' 	  => Str::studly($this->nameParts()->last()),
	//	];
	//}
	//
	//protected function getPsr4Namespace(string $rootNamespace): string
	//{
	//	$nameParts = $this->nameParts();
	//
	//	//implode the name parts, except the last part (the last part will be the class name)
	//	return collect(trim($rootNamespace, '\\'))
	//		->merge($nameParts->take($nameParts->count() - 1))
	//		->implode('\\');
	//}
	//
	//protected function nameParts(): Collection
	//{
	//	return Str::of($this->getNameArgument())
	//		->replace('/', DIRECTORY_SEPARATOR)
	//		->replace('\\', DIRECTORY_SEPARATOR)
	//		->explode(DIRECTORY_SEPARATOR)
	//		->filter();
	//}
	
	protected function isDryRun(): bool
	{
		return $this->hasOption('dry-run') ? $this->option('dry-run') : false;
	}
	
	protected function fileRecipeInstance(FileRecipe|array $fileRecipe, string|int $key): FileRecipe
	{
		return FileRecipe::instance($fileRecipe)
			->withScope($key);
			//->withDefaultReplacements($this->defaultReplacements($fileRecipe));
	}
	
	//protected function outputStubContentsToConsole(Stub $stub)
	//{
	//	$this->newLine();
	//	$this->warn("Target file contents:");
	//	$this->newLine();
	//
	//	$lines = Str::of($stub->getContents())->explode("\n");
	//
	//	foreach ($lines->take(30) as $line) {
	//		$this->line($line);
	//	}
	//
	//	if ($lines->count() > 30) {
	//		$this->newLine();
	//		$this->warn("... (only the first 30 lines are shown) ...");
	//	}
	//
	//	$this->newLine();
	//}
}
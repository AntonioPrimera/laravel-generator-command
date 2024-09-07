<?php
namespace AntonioPrimera\Artisan;

use AntonioPrimera\Artisan\Exceptions\TargetFileExistsException;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use AntonioPrimera\FileSystem\File;

abstract class FileGeneratorCommand extends Command
{
	/**
	 * The argument name holding the target file name.
	 * e.g. $signature = 'make:admin-page {name}'
	 */
	protected string $nameArgument = 'name';
	
	//the cached recipe for the current command execution (do not use directly, use getRecipe() instead)
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
					$createdFile = $fileRecipe->run(
						$this->getTargetRelativePath(),
						$this->getTargetFileName(),
						$this->isDryRun()
					);
					
					$this->createdFiles[] = $createdFile;
					$this->info("Created new $fileRecipe->scope at: $createdFile->path");
				} catch (TargetFileExistsException $exception) {
					$this->warn($exception->getMessage());
				}
			}
			
			$this->afterFileCreation();			    													//  -->>> Hook
		} catch (Exception $exception) {
			if (App::environment('testing')) {
				ray('Created files:', $this->createdFiles);
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
	abstract protected function recipe(): array|FileRecipe;
	
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
			/* @var File $createdFile */
			try {
				$createdFile->delete();
				$this->info("Cleanup - removed generated file: $createdFile->path");
			} catch (Exception $exception) {
				$this->error("Cleanup - failed to remove generated file: $createdFile->path. Error: {$exception->getMessage()}.");
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
	
	//--- Command helpers ---------------------------------------------------------------------------------------------
	
	public function getNameArgument(): string|null
	{
		return $this->hasArgument($this->nameArgument) ? $this->argument($this->nameArgument) : null;
	}
	
	/**
	 * Retrieves the path part of the name argument
	 */
	public function getTargetRelativePath(): string|null
	{
		if (!$this->getNameArgument())
			return null;
		
		$path = pathinfo($this->getNameArgument(), PATHINFO_DIRNAME);
		return $path === '.' ? '' : $path;
	}
	
	/**
	 * Retrieves the file name part of the name argument
	 */
	public function getTargetFileName(): string|null
	{
		if(!$this->getNameArgument())
			return null;
		
		return pathinfo($this->getNameArgument(), PATHINFO_FILENAME);
	}
	
	public function isDryRun(): bool
	{
		return $this->hasOption('dry-run') ? $this->option('dry-run') : false;
	}
	
	//--- Protected helpers -------------------------------------------------------------------------------------------
	
	protected function getRecipe(): array
	{
		return $this->setupRecipe();
	}
	
	protected function setupRecipe(): array
	{
		return Collection::wrap($this->recipe())
			->map(fn ($recipe, $key) => FileRecipe::instance($recipe))
			->map(fn (FileRecipe $recipe, $key) => is_string($key) ? $recipe->withScope($key) : $recipe)
			->toArray();
	}
}
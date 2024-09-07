<?php
namespace AntonioPrimera\Artisan;

use AntonioPrimera\Artisan\Exceptions\InvalidRecipeException;
use AntonioPrimera\FileSystem\Folder;
use AntonioPrimera\FileSystem\OS;
use Illuminate\Support\Str;
use AntonioPrimera\FileSystem\File;

class FileRecipe
{
	public Stub|null $stub = null;
	public Folder|null $target = null;
	public string|null $extension = null;
	public string|null $rootNamespace = null;
	public array $replace = [];
	public string|null $scope = null;				//only used for console messages
	public mixed $fileNameTransformer = null;		//optional: used to transform the target file name
	public mixed $relativePathTransformer = null;	//optional: used to transform the target relative path
	public bool $overwriteFiles = false;			//if true, the target file will be overwritten if it exists
	public bool $backupFiles = false;				//if true, the target file will be backed up if it exists
	
	public function __construct(
		string|File|Stub|null 	$stub = null,
		string|Folder|null    	$targetFolder = null,
		string|null      		$rootNamespace = null,
		array            		$replace = [],
		string|null        		$scope = null,
		string|null        		$extension = null,
		mixed            		$fileNameTransformer = null,
		mixed					$relativePathTransformer = null
	)
	{
		$this->withStub($stub)
			->withTargetFolder($targetFolder)
			->withExtension($extension)
			->withRootNamespace($rootNamespace)
			->withReplace($replace)
			->withScope($scope)
			->withFileNameTransformer($fileNameTransformer)
			->withRelativePathTransformer($relativePathTransformer);
	}
	
	public static function create(
		string|File|Stub|null	$stub = null,
		string|Folder|null    	$targetFolder = null,
		string|null      		$rootNamespace = null,
		array            		$replace = [],
		string|null           	$scope = null,
		string|null        		$extension = null,
		mixed            		$fileNameTransformer = null,
		mixed					$relativePathTransformer = null
	): static
	{
		return new static(
			stub: $stub,
			targetFolder: $targetFolder,
			rootNamespace: $rootNamespace,
			replace: $replace,
			scope: $scope,
			extension: $extension,
			fileNameTransformer: $fileNameTransformer,
			relativePathTransformer: $relativePathTransformer
		);
	}
	
	public static function instance(FileRecipe|array $fileRecipe): static
	{
		if ($fileRecipe instanceof FileRecipe)
			return $fileRecipe;
		
		//the stub must be a string, a File instance or a Stub instance
		if (!(
			isset($fileRecipe['stub'])
			&& (is_string($fileRecipe['stub']) || $fileRecipe['stub'] instanceof File)
		))
			throw new \InvalidArgumentException("FileRecipe array must contain a valid 'stub' key");
		
		//the target must be a string or a Folder instance
		if (!(
			isset($fileRecipe['target'])
			&& (is_string($fileRecipe['target']) || $fileRecipe['target'] instanceof Folder)
		))
			throw new \InvalidArgumentException("FileRecipe array must contain a valid 'target' key");
		
		return new static(
			stub: $fileRecipe['stub'],
			targetFolder: $fileRecipe['target'],
			rootNamespace: $fileRecipe['rootNamespace'] ?? null,
			replace: $fileRecipe['replace'] ?? [],
			scope: $fileRecipe['scope'] ?? null,
			extension: $fileRecipe['extension'] ?? '',
			fileNameTransformer: $fileRecipe['fileNameTransformer'] ?? null,
			relativePathTransformer: $fileRecipe['relativePathTransformer'] ?? null
		);
	}
	
	//--- Recipe cooking ----------------------------------------------------------------------------------------------
	
	/**
	 * Run the recipe and return the resulting file instance
	 */
	public function run(string|null $targetRelativePath, string|null $targetFileName, bool $dryRun = false): File
	{
		//validate the recipe before running it
		$this->validate();
		
		//determine the relative path from the given target relative path
		$relativePath = $targetRelativePath
			? $this->transformString($targetRelativePath, $this->relativePathTransformer)
			: null;
		
		//determine the file name from the given target file name, if not null, otherwise use the stub file name
		$fileName = $targetFileName
			? $this->transformString($targetFileName, $this->fileNameTransformer)
			: $this->stub->getNameWithoutExtension(10);
		
		//determine the file extension from the given extension or from the stub file
		$fileExtension = ltrim($this->extension ?: $this->stub->targetFileExtension, '.');
		
		//set the relative target path and the target file name (also transforms the file name if a transformer is set)
		$targetFile = $this->target
			->subFolder($relativePath ?? '')
			->file("$fileName.$fileExtension");
		
		//now that we have the final file name, set the default replacements (DUMMY_NAMESPACE, DUMMY_CLASS)
		//it is safe to just use '/' as a separator, because the path will be normalized inside the function
		$this->withDefaultReplacements($this->defaultReplacements("$relativePath/$targetFileName"));
		
		//if requested and the target file exists, back it up (just rename it, adding a .backup extension)
		if ($this->backupFiles && $targetFile->exists)
			(clone $targetFile)->rename($targetFile->name . '.backup');
		
		//let the stub generate the target file with the given replacements
		$this->stub->generate($targetFile, $this->replace, $dryRun, $this->overwriteFiles);
		
		return $targetFile;
	}
	
	//--- Fluent interface --------------------------------------------------------------------------------------------
	
	public function withStub(string|File|Stub|null $stub): static
	{
		$this->stub = $stub ? Stub::instance($this->absolutePath($stub)) : null;
		return $this;
	}
	
	public function withTargetFolder(string|Folder|null $target): static
	{
		$this->target = $target ? Folder::instance($this->absolutePath($target)) : null;
		return $this;
	}
	
	public function withRootNamespace(string|null $rootNamespace): static
	{
		$this->rootNamespace = $rootNamespace;
		return $this;
	}
	
	public function withExtension(string|null $extension): static
	{
		$this->extension = $extension;
		return $this;
	}
	
	public function withScope(string|null $scope): static
	{
		$this->scope = $scope;
		return $this;
	}
	
	/**
	 * Add [... 'placeholder' => 'replacement' ...] pairs to the recipe.
	 */
	public function withReplace(array $replace): static
	{
		$this->replace = array_merge($this->replace, $replace);
		return $this;
	}
	
	/**
	 * Add default replacements to the recipe, without overwriting existing ones.
	 */
	public function withDefaultReplacements(array $replace): static
	{
		$this->replace = array_merge($replace, $this->replace);
		return $this;
	}
	
	public function withFileNameTransformer(mixed $transformer): static
	{
		$this->fileNameTransformer = $transformer;
		return $this;
	}
	
	public function withRelativePathTransformer(mixed $transformer): static
	{
		$this->relativePathTransformer = $transformer;
		return $this;
	}
	
	public function withOverwriteFiles(bool $overwrite = true): static
	{
		$this->overwriteFiles = $overwrite;
		return $this;
	}
	
	public function withBackupFiles(bool $backup = true): static
	{
		$this->backupFiles = $backup;
		return $this;
	}
	
	//--- Default replacements ----------------------------------------------------------------------------------------
	
	/**
	 * Determine the default replacements for the recipe, based on
	 * the target file name, given as the command argument
	 */
	protected function defaultReplacements(string $targetName): array
	{
		return [
			'DUMMY_NAMESPACE' => $this->getPsr4Namespace($targetName),
			'DUMMY_CLASS' 	  => Str::studly(basename($targetName)),
		];
	}
	
	protected function getPsr4Namespace(string $targetName): string
	{
		return Str::of($targetName)
			->replace(['/', '\\'], DIRECTORY_SEPARATOR)	//replace slashes and backslashes with DIRECTORY_SEPARATOR
			->explode(DIRECTORY_SEPARATOR)				//explode the target name into parts
			->slice(0, -1)							//remove the last part (the last part will be the class name)
			->filter()											//remove empty parts
			->prepend(trim($this->rootNamespace ?: 'App', '\\'))	//prepend the root namespace
			->implode('\\');								//implode the parts back together
	}
	
	protected function absolutePath(string|File|Folder $file): string
	{
		return OS::isAbsolutePath((string) $file)
			? (string) $file
			: base_path((string) $file);
	}
	
	//--- File name manipulation --------------------------------------------------------------------------------------
	
	protected function transformString(string $string, mixed $transformer): string
	{
		if (is_callable($transformer))
			return $transformer($string);
		
		if (is_string($transformer) && is_callable([Str::class, $transformer]))
			return Str::$transformer($string);
		
		return $string;
	}
	
	//--- Recipe validation -------------------------------------------------------------------------------------------
	
	protected function validate(): void
	{
		if ($this->stub === null)
			throw new InvalidRecipeException("FileRecipe is missing the stub");
		
		if ($this->target === null)
			throw new InvalidRecipeException("FileRecipe is missing the target folder");
	}
}
<?php
namespace AntonioPrimera\Artisan;

use AntonioPrimera\FileSystem\Folder;
use AntonioPrimera\FileSystem\OS;
use Illuminate\Support\Str;
use AntonioPrimera\FileSystem\File;

class FileRecipe
{
	public Stub $stub;
	public Folder $target;
	public string $extension = '';
	public string|null $rootNamespace = null;
	public array $replace = [];
	public string $scope = '';					//only used for console messages
	public mixed $fileNameTransformer = null;	//optional: used to transform the target file name
	
	public function __construct(
		string|File|Stub $stub,
		string|Folder    $targetFolder,
		string|null      $rootNamespace = null,
		array            $replace = [],
		string           $scope = '',
		string           $extension = '',
		mixed            $fileNameTransformer = null
	)
	{
		$this->stub = Stub::instance($this->absolutePath($stub));
		$this->target = Folder::instance($this->absolutePath($targetFolder));
		$this->extension = $extension;
		
		$this->rootNamespace = $rootNamespace;
		$this->replace = $replace;
		$this->scope = $scope;
		$this->fileNameTransformer = $fileNameTransformer;
	}
	
	public static function create(
		string|File|Stub $stub,
		string|Folder    $targetFolder,
		string|null      $rootNamespace = null,
		array            $replace = [],
		string           $scope = '',
		string           $extension = '',
		mixed            $fileNameTransformer = null
	): static
	{
		return new static($stub, $targetFolder, $rootNamespace, $replace, $scope, $extension, $fileNameTransformer);
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
			$fileRecipe['stub'],
			$fileRecipe['target'],
			$fileRecipe['rootNamespace'] ?? null,
			$fileRecipe['replace'] ?? [],
			$fileRecipe['scope'] ?? '',
			$fileRecipe['extension'] ?? '',
			$fileRecipe['fileNameTransformer'] ?? $fileRecipe['fileNameFormat'] ?? null
		);
	}
	
	//--- Recipe cooking ----------------------------------------------------------------------------------------------
	
	/**
	 * Run the recipe and return the resulting file instance
	 */
	public function run(string $targetRelativePath, string $targetFileName, bool $dryRun = false): File
	{
		$fileName = $this->transformFileName($targetFileName, $this->fileNameTransformer);
		$fileExtension = ltrim($this->extension ?: $this->stub->targetFileExtension, '.');
		
		//set the relative target path and the target file name (also transforms the file name if a transformer is set)
		$targetFile = $this->target
			->subFolder($targetRelativePath)
			->file("$fileName.$fileExtension");
		
		//now that we have the final file name, set the default replacements (DUMMY_NAMESPACE, DUMMY_CLASS)
		//it is safe to just use '/' as a separator, because the path will be normalized inside the function
		$this->withDefaultReplacements($this->defaultReplacements("$targetRelativePath/$targetFileName"));
		
		//let the stub generate the target file with the given replacements
		$this->stub->generate($targetFile, $this->replace, $dryRun);
		
		return $targetFile;
	}
	
	//--- Syntactic sugar ---------------------------------------------------------------------------------------------
	
	public function withRootNamespace(string $rootNamespace): static
	{
		$this->rootNamespace = $rootNamespace;
		return $this;
	}
	
	public function withScope(string $scope): static
	{
		$this->scope = $scope;
		return $this;
	}
	
	/**
	 * Add [... 'placeholder' => 'replacement' ...] pairs to the recipe.
	 */
	public function replace(array $replace): static
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
	
	/**
	 * Transform the given file name using the given transformer
	 */
	protected function transformFileName(string $fileName, mixed $transformer): string
	{
		if (is_callable($transformer))
			return $transformer($fileName);
		
		if (is_string($transformer) && is_callable([Str::class, $transformer]))
			return Str::$transformer($fileName);
		
		return $fileName;
	}
}
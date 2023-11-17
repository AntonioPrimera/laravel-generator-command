<?php

namespace AntonioPrimera\Artisan;

class FileRecipe
{
	public File $stub;
	public File $target;
	public string|null $rootNamespace = null;
	public array $replace = [];
	public string $scope = '';					//only used for console messages
	public mixed $fileNameTransformer = null;	//optional: used to transform the target file name
	
	public function __construct(
		string|File $stub,
		string|File $target,
		string|null $rootNamespace = null,
		array $replace = [],
		string $scope = '',
		string $extension = '',
		mixed $fileNameTransformer = null
	)
	{
		$this->stub = is_string($stub)
			? File::createFromPath($stub)
			: $stub;
		
		$this->target = is_string($target)
			? File::create(folder: $target, extension: $extension)
			: $target;
		
		$this->rootNamespace = $rootNamespace;
		$this->replace = $replace;
		$this->scope = $scope;
		$this->fileNameTransformer = $fileNameTransformer;
	}
	
	public static function create(
		string|File $stub,
		string|File $target,
		string|null $rootNamespace = null,
		array $replace = [],
		string $scope = '',
		string $extension = '',
		mixed $fileNameTransformer = null
	): static
	{
		return new static($stub, $target, $rootNamespace, $replace, $scope, $extension, $fileNameTransformer);
	}
	
	public static function instance(FileRecipe|array $fileRecipe): static
	{
		if ($fileRecipe instanceof FileRecipe)
			return $fileRecipe;
		
		//the stub must be a string or a File instance
		if (!(
			isset($fileRecipe['stub'])
			&& (is_string($fileRecipe['stub']) || $fileRecipe['stub'] instanceof File)
		))
			throw new \InvalidArgumentException("FileRecipe array must contain a valid 'stub' key");
		
		//the target must be a string or a File instance
		if (!(
			isset($fileRecipe['target'])
			&& (is_string($fileRecipe['target']) || $fileRecipe['target'] instanceof File)
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
	
	public function run(string $targetRelativePath, string $targetFileName, bool $dryRun = false): static
	{
		//set the relative target path and the target file name (also transforms the file name if a transformer is set)
		$this->target
			->subFolder($targetRelativePath)
			->setFilename($targetFileName)
			->transformFileName($this->fileNameTransformer);
		
		//create the stub instance, which generates the target file and replaces the placeholders
		Stub::create($this->stub, $this->target)
			->generate($this->replace, $dryRun);
		
		return $this;
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
}
<?php

namespace AntonioPrimera\Artisan;

class FileRecipe
{
	
	/**
	 * The path to the stub file
	 * @var string
	 */
	public string $stub;
	
	/**
	 * The target root path
	 * @var string
	 */
	public string $path;
	
	/**
	 * The extension is optional
	 * @var string|null
	 */
	public ?string $extension = null;
	
	/**
	 * Whether to format the file name (string / callable)
	 * @var mixed|null
	 */
	public mixed $fileNameFormat = null;
	
	/**
	 * The root namespace if classes are generated
	 * @var string|null
	 */
	public ?string $rootNamespace = null;
	
	/**
	 * An array of placeholder => replace items
	 * @var array
	 */
	public array $replace = [];
	
	/**
	 * The root path for $stub and $path
	 * if they are relative paths.
	 * @var mixed
	 */
	public mixed $rootPath = 'app_path';
	
	/**
	 * Used just for console messages
	 * @var string
	 */
	public string $scope = '';
	
	public function __construct(string $stub, string $path)
	{
		$this->stub = $stub;
		$this->path = $path;
	}
}
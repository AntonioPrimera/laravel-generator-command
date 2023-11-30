<?php

namespace AntonioPrimera\Artisan;

use AntonioPrimera\Artisan\Exceptions\TargetFileExistsException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use AntonioPrimera\FileSystem\File;

class Stub extends File
{
	/**
	 * The extension of the target file (without the dot), guessed
	 * from the stub file name, by removing the .stub extension
	 */
	public readonly string $targetFileExtension;
	
	public function __construct(string|File $path)
	{
		parent::__construct((string) $path);
		
		if (!$this->exists())
			throw new FileNotFoundException("Stub file {$this->path} could not be found");
		
		$this->targetFileExtension = $this->guessExtension();
	}
	
	//--- File operations ---------------------------------------------------------------------------------------------
	
	public function generate(string|File $targetPath, array $replace = [], bool $dryRun = false): static
	{
		$targetFile = File::instance($targetPath);
		
		if ($targetFile->exists())
			throw new TargetFileExistsException("Target file {$targetFile->path} already exists");
		
		$this->copyContentsToFile($targetFile, $dryRun);
		$targetFile->replaceInFile($replace, $dryRun);
		
		return $this;
	}
	
	//--- Protected helpers -------------------------------------------------------------------------------------------
	
	protected function guessExtension(): string
	{
		//get the stub file name and return everything after the first '.' (excluding .stub)
		return File::instance(basename($this->path, '.stub'))->getExtension(10);
	}
}
<?php

namespace AntonioPrimera\Artisan;

use AntonioPrimera\Artisan\Exceptions\TargetFileExistsException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class Stub
{
	protected File $source;
	protected File $target;
	
	public function __construct(string|File $stubFile, string|File $targetFile)
	{
		$this->source = $stubFile instanceof File ? $stubFile : File::createFromPath($stubFile);
		if (!$this->source->exists())
			throw new FileNotFoundException("Stub file {$this->source->getFullPath()} could not be found");
		
		$this->target = $targetFile instanceof File ? $targetFile : File::createFromPath($targetFile);
		
		//if the target doesn't have an extension, guess it from the stub file name
		if (!$this->target->extension)
			$this->target->extension = $this->guessExtension();
	}
	
	public static function create(string|File $stubFile, string|File $targetFile): static
	{
		return new static($stubFile, $targetFile);
	}
	
	//--- File operations ---------------------------------------------------------------------------------------------
	
	public function generate(array $replace = [], bool $dryRun = false): static
	{
		if ($dryRun)
			return $this;
		
		if ($this->target->exists())
			throw new TargetFileExistsException("Target file {$this->target->getFullPath()} already exists");
		
		$this->target->setContents($this->source->getContents())->replaceInFile($replace);
		return $this;
	}
	
	/**
	 * Replace the keys of the given array with their values
	 * in the stub file contents.
	 */
	public function replace(array $replace): static
	{
		if ($replace)
			$this->target->replaceInFile($replace);
		
		return $this;
	}
	
	//--- Protected helpers -------------------------------------------------------------------------------------------
	
	protected function guessExtension(): string
	{
		//get the stub file name and return everything after the first '.' (excluding .stub)
		return explode('.', basename($this->source->getFullPath(), '.stub'), 2)[1] ?? '';
	}
}
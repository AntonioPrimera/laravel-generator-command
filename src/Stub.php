<?php

namespace AntonioPrimera\Artisan;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use function Webmozart\Assert\Tests\StaticAnalysis\null;

class Stub
{
	protected string $stubPath;
	protected string $contents;
	
	protected string $targetFilePath;
	protected string $targetFolder;
	protected string $targetFileName;
	protected ?string $targetExtension = '';
	
	/**
	 * @throws FileNotFoundException
	 */
	public function __construct(string $stubPath, string $targetPath)
	{
		if (!file_exists($stubPath))
			throw new FileNotFoundException("Stub file $stubPath could not be found");
		
		$this->stubPath = $this->realPath($stubPath);
		$this->contents = file_get_contents($stubPath);
		
		//save the target file path and split it in path + fileName (without extension)
		$this->targetFilePath = $targetPath;
		$this->targetFileName = basename($this->targetFilePath);
		$this->targetFolder = $this->realPath(dirname($this->targetFilePath));
	}
	
	public function generate(): Stub
	{
		$this->createTargetFolder();
		
		//create the folder path if necessary and the target file
		file_put_contents($this->getTargetFilePath(), $this->contents);
		
		return $this;
	}
	
	/**
	 * Replace the keys of the given array with their values
	 * in the stub file contents.
	 *
	 * @param array $replacements
	 *
	 * @return $this
	 */
	public function replace(array $replacements): Stub
	{
		$this->contents = str_replace(array_keys($replacements), array_values($replacements), $this->contents);
		return $this;
	}
	
	/**
	 * If a string format is given, the target file name is transformed by applying the
	 * corresponding method of Illuminate\Support\Str on the file name. If a
	 * Callable is given, the name is transformed using the callable.
	 *
	 * @param $format
	 *
	 * @return $this
	 */
	public function formatFileName($format): Stub
	{
		if (!$format)
			return $this;
		
		//valid string formats are static methods of Illuminate\Support\Str
		// e.g. 'fileNameFormat' => 'kebab' >>> Str::kebab($fileName)
		if (is_string($format))
			$this->targetFileName = call_user_func([Str::of($this->targetFileName), $format]);
		
		//if a callable formatter is given, apply it
		if (is_callable($format))
			$this->targetFileName = call_user_func($format, $this->targetFileName);
		
		return $this;
	}
	
	/**
	 * Resets the targetExtension to null, so it will be guessed
	 * from the stub file name. By default, the extension is
	 * an empty string, so no extension will be added.
	 *
	 * @return $this
	 */
	public function guessExtensionFromStub(): Stub
	{
		$this->targetExtension = null;
		return $this;
	}
	
	//--- Getters and Setters -----------------------------------------------------------------------------------------
	
	public function getTargetFilePath(): string
	{
		$path = Str::of($this->targetFolder . DIRECTORY_SEPARATOR . $this->targetFileName);
		$extension = $this->getExtension();
		
		//if we have an extension, make sure it starts with a '.', and don't add it twice
		if ($extension && !$path->endsWith($extension))
			$path .= Str::of($extension)->start('.');
		
		return $path;
	}
	
	/**
	 * If a string extension is set, this method will return it,
	 * otherwise, if the extension is null, it will guess
	 * the extension from the stub file name.
	 *
	 * @return string
	 */
	public function getExtension(): string
	{
		return is_string($this->targetExtension)
			? $this->targetExtension
			: $this->guessExtension();
	}
	
	/**
	 * Sets the extension of the target file. If null,
	 * the target extension will be guessed,
	 * based on the stub file name.
	 *
	 * @param string|null $extension
	 *
	 * @return $this
	 */
	public function setExtension(?string $extension): Stub
	{
		$this->targetExtension = $extension ? ltrim($extension, '.') : $extension;
		return $this;
	}
	
	public function getContents(): string
	{
		return $this->contents;
	}
	
	//--- Protected helpers -------------------------------------------------------------------------------------------
	
	protected function guessExtension(): string
	{
		//get the stub file name and split it in 2, by the first '.'
		$stubFileName = basename($this->stubPath, '.stub');
		$stubParts = explode('.', $stubFileName, 2);
		
		//the extension is considered to be whatever comes after the first '.'
		//	e.g. 'blade-file.blade.php' >>> '.blade.php'
		return $stubParts[1] ?? '';
	}
	
	/**
	 * Recursively creates the target folder if it doesn't exist
	 *
	 * @param int $permissions
	 *
	 * @return $this
	 */
	protected function createTargetFolder(int $permissions = 0755): Stub
	{
		if (!is_dir($this->targetFolder))
			mkdir($this->targetFolder, $permissions, true);
		
		return $this;
	}
	
	protected function isAbsolutePath($path): bool
	{
		return in_array($path[0], ['/', '\\']);
	}
	
	protected function explodePath($path): Collection
	{
		return Str::of($path)
			->replace('/', DIRECTORY_SEPARATOR)
			->replace('\\', DIRECTORY_SEPARATOR)
			->explode(DIRECTORY_SEPARATOR)
			->filter();
	}
	
	protected function realPath($path): string
	{
		$path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
		$root = $this->isAbsolutePath($path) ? DIRECTORY_SEPARATOR : '';
		
		$parts = array_filter(explode(DIRECTORY_SEPARATOR, $path));
		$absolutes = [];
		
		foreach ($parts as $part) {
			if ($part === '.')
				continue;
			
			if ($part === '..') {
				array_pop($absolutes);
				continue;
			}
			
			$absolutes[] = $part;
		}
		
		return $root . implode(DIRECTORY_SEPARATOR, $absolutes);
	}
}
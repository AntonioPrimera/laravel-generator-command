<?php
namespace AntonioPrimera\Artisan;

use Exception;
use Illuminate\Support\Str;
use Stringable;

class File implements Stringable
{
	public function __construct(public string $folder, public string $fileName = '', public string $extension = '')
	{
		$this->folder = (string) Str::of($this->folder)
			->replace(['\\', '/'], DIRECTORY_SEPARATOR)
			->rtrim(DIRECTORY_SEPARATOR);
		
		//if the folder is relative, prepend the base path (project root path)
		if ($this->isRelativePath())
			$this->folder = base_path($this->folder);
		
		$this->fileName = (string) Str::of($this->fileName)
			->replace(['\\', '/'], DIRECTORY_SEPARATOR)
			->trim(DIRECTORY_SEPARATOR);
	}
	
	//--- Static factories --------------------------------------------------------------------------------------------
	
	public static function create(string $folder, string|null $fileName = '', string $extension = ''): File
	{
		return new static($folder, $fileName, $extension);
	}
	
	public static function createFromPath(string $path): File
	{
		$folder = dirname($path);
		$fileName = pathinfo($path, PATHINFO_FILENAME);
		$extension = pathinfo($path, PATHINFO_EXTENSION);
		
		return new static($folder, $fileName, $extension);
	}
	
	//--- File operations ---------------------------------------------------------------------------------------------
	
	public function isRelativePath(): bool
	{
		return !$this->isAbsolutePath();
	}
	
	public function isAbsolutePath(): bool
	{
		return str_starts_with($this->folder, DIRECTORY_SEPARATOR);
	}
	
	public function getFullPath(): string
	{
		return $this->folder . DIRECTORY_SEPARATOR . $this->fileName . Str::start($this->extension, '.');
	}
	
	public function folderExists(): bool
	{
		return is_dir($this->folder);
	}
	
	public function exists(): bool
	{
		return file_exists($this->getFullPath())
			&& is_file($this->getFullPath());
	}
	
	public function createFolder(): static
	{
		if (!$this->folderExists())
			mkdir($this->folder, 0777, true);
		
		return $this;
	}
	
	public function setContents(string $contents): static
	{
		$this->createFolder();
		file_put_contents($this->getFullPath(), $contents);
		return $this;
	}
	
	public function getContents(): string
	{
		if (!$this->exists())
			throw new Exception("File {$this->getFullPath()} does not exist");
		
		return file_get_contents($this->getFullPath());
	}
	
	public function replaceInFile(array $replace): static
	{
		if (!$this->exists())
			throw new Exception("File {$this->getFullPath()} does not exist. Cannot replace in file.");
		
		$contents = $this->getContents();
		
		foreach ($replace as $search => $replaceWith) {
			$contents = str_replace($search, $replaceWith, $contents);
		}
		
		$this->setContents($contents);
		return $this;
	}
	
	public function delete(): static
	{
		if ($this->exists())
			unlink($this->getFullPath());
		
		return $this;
	}
	
	public function subFolder(string $path): static
	{
		$subFolderPath = (string) Str::of($path)
			->replace(['\\', '/'], DIRECTORY_SEPARATOR)
			->trim(DIRECTORY_SEPARATOR);
		
		if (!$subFolderPath)
			return $this;
		
		$this->folder = $this->folder . DIRECTORY_SEPARATOR . $subFolderPath;
		return $this;
	}
	
	public function setFilename(string $fileName): static
	{
		$this->fileName = $fileName;
		return $this;
	}
	
	public function transformFileName(mixed $transformer): static
	{
		if (!$transformer)
			return $this;
		
		//if the transformer is a callable, call it with the current filename
		if (is_callable($transformer)) {
			$this->fileName = call_user_func($transformer, $this->fileName);
			return $this;
		}
		
		//if the transformer is a string, call that method on Str (e.g. 'kebab' => Str::kebab($this->fileName))
		if (is_callable([Str::class, $transformer])) {
			$this->fileName = Str::$transformer($this->fileName);
			return $this;
		}
		
		return $this;
	}
	
	//--- Interface methods -------------------------------------------------------------------------------------------
	
	public function __toString(): string
	{
		return $this->getFullPath();
	}
}
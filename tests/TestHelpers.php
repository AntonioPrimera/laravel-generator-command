<?php

namespace AntonioPrimera\Artisan\Tests;

use Illuminate\Support\Str;

trait TestHelpers
{
	protected function realPath($path): string
	{
		$path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
		$root = $path[0] === DIRECTORY_SEPARATOR ? DIRECTORY_SEPARATOR : '';
		
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
	
	/**
	 * @throws \Exception
	 */
	protected function rrmdir($dir): void
	{
		if (!is_dir($dir))
			return;
		
		$validPaths = [
			'TestContext' => $this->realPath(__DIR__ . '/TestContext/GeneratedFiles'),
			'AppStubs' => $this->realPath(app_path('stubs'))
		];
		
		//only delete folders and files in one of the $validPaths
		if (!(
			Str::of($dir)->startsWith($validPaths['TestContext'])
			||
			Str::of($dir)->startsWith($validPaths['AppStubs'])
		))
			throw new \Exception("Trying to delete folder outside the safe zone: $dir");
		
		$filesAndFolders = scandir($dir);
		foreach ($filesAndFolders as $fileOrFolder) {
			if (in_array($fileOrFolder, ['.', '..']))
				continue;
		
			$path = $dir. DIRECTORY_SEPARATOR . $fileOrFolder;
			
			if (is_dir($path) && !is_link($path))
				$this->rrmdir($path);
			else
				unlink($path);
		}
		
		rmdir($dir);
	}
	
	protected function cleanupFiles(...$files): void
	{
		//cleanup any given files
		foreach ($files as $file)
			if (file_exists($file))
				unlink($file);
	}
}
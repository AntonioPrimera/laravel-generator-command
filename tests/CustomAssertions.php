<?php

namespace AntonioPrimera\Artisan\Tests;

use Illuminate\Support\Arr;

trait CustomAssertions
{
	protected function assertFileContentsEquals($expected, $path)
	{
		$this->assertEquals($expected, $this->getStrippedFileContents($path));
	}
	
	protected function assertFileContainsString($expected, $path)
	{
		$this->assertStringContainsString($expected, file_get_contents($path));
	}
	
	protected function assertFileContainsStrings($expected, $path)
	{
		foreach (Arr::wrap($expected) as $str) {
			$this->assertFileContainsString($str, $path);
		}
	}
	
	protected function getStrippedFileContents($path): string
	{
		return str_replace(["\t", "\s", "\n"], '', file_get_contents($path));
	}
	
	protected function assertFoldersExist($paths)
	{
		foreach (Arr::wrap($paths) as $path) {
			//$this->assertFoldersExist()
			$this->assertDirectoryExists($this->realPath($path));
		}
	}
	
	protected function assertFilesExist($paths, $rootPath = '')
	{
		foreach (Arr::wrap($paths) as $path) {
			$this->assertFileExists(
				$rootPath
					? rtrim($rootPath, '/') . '/' . ltrim($path, '/')
					: $path
			);
		}
	}
}
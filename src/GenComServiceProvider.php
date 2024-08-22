<?php

namespace AntonioPrimera\Artisan;

class GenComServiceProvider extends \Illuminate\Support\ServiceProvider
{
	public function boot(): void
	{
		if ($this->app->runningInConsole()) {
			$this->commands([
				MakeCommand::class,
			]);
		}
	}
}
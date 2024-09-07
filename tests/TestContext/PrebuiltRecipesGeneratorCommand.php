<?php
namespace AntonioPrimera\Artisan\Tests\TestContext;

use AntonioPrimera\Artisan\FileGeneratorCommand;
use AntonioPrimera\Artisan\FileRecipe;
use AntonioPrimera\Artisan\FileRecipes\BladeRecipe;
use AntonioPrimera\Artisan\FileRecipes\CommandRecipe;
use AntonioPrimera\Artisan\FileRecipes\ConfigRecipe;
use AntonioPrimera\Artisan\FileRecipes\ControllerRecipe;
use AntonioPrimera\Artisan\FileRecipes\JsRecipe;
use AntonioPrimera\Artisan\FileRecipes\MigrationRecipe;
use AntonioPrimera\Artisan\FileRecipes\ModelRecipe;
use AntonioPrimera\Artisan\FileRecipes\ServiceProviderRecipe;
use AntonioPrimera\Artisan\FileRecipes\StyleSheetRecipe;
use AntonioPrimera\Artisan\FileRecipes\ViewComponentRecipe;
use Illuminate\Support\Arr;

class PrebuiltRecipesGeneratorCommand extends FileGeneratorCommand
{
	protected $signature = 'make:pbr {name} {recipe}';
	protected string $genericStubPath = __DIR__ . '/stubs/generic-file.php.stub';
	protected string $genericClassStubPath = __DIR__ . '/stubs/GenericClass.php.stub';
	
	protected function recipe(): array
	{
		/* @var FileRecipe $recipeInstance */
		$recipe = $this->argument('recipe');
		$recipeFactoryMethod = 'recipe' . ucfirst($recipe);
		$recipeInstance = $this->$recipeFactoryMethod();
		//ray($recipeInstance);
		return Arr::wrap($recipeInstance);
	}
	
	
	protected function recipeMigration(): MigrationRecipe
	{
		return (new MigrationRecipe($this->genericStubPath))
			->withReplace(['#REPLACE-ME#' => 'Migration']);
	}
	
	protected function recipeViewComponent(): array
	{
		return [
			(new ViewComponentRecipe($this->genericClassStubPath))->withReplace(['#REPLACE-ME#' => 'ViewComponent']),
			(new BladeRecipe($this->genericStubPath, 'Components/SiteComponents'))->withReplace(['#REPLACE-ME#' => 'Blade'])
		];
	}
	
	protected function recipeBlade(): BladeRecipe
	{
		return (new BladeRecipe($this->genericStubPath))->withReplace(['#REPLACE-ME#' => 'Blade']);
	}
	
	protected function recipeModel(): ModelRecipe
	{
		return (new ModelRecipe($this->genericClassStubPath))
			->withReplace(['#REPLACE-ME#' => 'Model']);
	}
	
	protected function recipeCommand(): CommandRecipe
	{
		return (new CommandRecipe($this->genericClassStubPath))
			->withReplace(['#REPLACE-ME#' => 'Command']);
	}

	protected function recipeController(): ControllerRecipe
	{
		return (new ControllerRecipe($this->genericClassStubPath))
			->withReplace(['#REPLACE-ME#' => 'TheController']);
	}
	
	protected function recipeServiceProvider(): ServiceProviderRecipe
	{
		return (new ServiceProviderRecipe($this->genericClassStubPath))
			->withReplace(['#REPLACE-ME#' => 'The Service Provider']);
	}
	
	protected function recipeConfig(): ConfigRecipe
	{
		return (new ConfigRecipe($this->genericStubPath))
			->withReplace(['#REPLACE-ME#' => 'My Configuration']);
	}
	
	protected function recipeJs(): JsRecipe
	{
		return (new JsRecipe(__DIR__ . '/stubs/js-stub.js.stub'))
			->withReplace(['#REPLACE-ME#' => 'JavaScript']);
	}
	
	protected function recipeTs(): JsRecipe
	{
		return (new JsRecipe(__DIR__ . '/stubs/ts-stub.ts.stub'))
			->withReplace(['#REPLACE-ME#' => 'TypeScript']);
	}
	
	protected function recipeJson(): JsRecipe
	{
		return (new JsRecipe(__DIR__ . '/stubs/json-stub.json.stub'))
			->withReplace(['#REPLACE-ME#' => 'JSON']);
	}
	
	protected function recipeCss(): StyleSheetRecipe
	{
		return (new StyleSheetRecipe(__DIR__ . '/stubs/css-stub.css.stub'))
			->withReplace(['#REPLACE-ME#' => 'CSS']);
	}
	
	protected function recipePcss(): StyleSheetRecipe
	{
		return (new StyleSheetRecipe(__DIR__ . '/stubs/pcss-stub.pcss.stub'))
			->withReplace(['#REPLACE-ME#' => 'SCSS']);
	}
}
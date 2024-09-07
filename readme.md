# Upgraded Laravel Generator Command

This is a very simple package, with one single goal: to create Artisan Commands, which generate files based on stubs.

It allows you to create a single file, or several files in one go. For example when creating a CRUD Controller,
you might want to generate the controller, the model, the migration, the factory, the seeder and all the views in one go.

The Generator Command offered by Laravel is good enough for simple files, but is poorly documented and lacks some
basic options, which I needed in most of my projects. After writing a few generator commands for my packages, I decided
to extract this into a simple package. If you feel like contributing, please fork the repository and submit a PR.

## Installation

Via composer import:

```bash
composer require --dev antonioprimera/laravel-generator-command
```

## Usage

### Step 1: Create the command

The package comes with an artisan generator command to generate a generator command (sounds a bit recursive, and it is)
in your project:

```bash
php artisan make:generator-command <CommandName>
```

This will create a new command in your `app/Console/Commands` folder.

The new command class inherits the abstract `AntonioPrimera\Artisan\FileGeneratorCommand` and contains a ToDo list and
some commented recipe samples to get you inspired and started as fast as possible.

The FileGeneratorCommand extends the default Laravel Console Command, so you need to provide a signature and optionally
a description. The signature should have a `{name}` parameter, to get the target file name from the console input. If
you want to preserve the stub file names, you can omit the `{name}` parameter (this acts like a publish command).

e.g.
```php
protected $signature = 'model:create-vue-assets {name}';
protected $description = 'Generate the vue assets for the given model.';
```

### Step 2: Implement the recipe method in the command

Implement the abstract `recipe()` method in your generator command. This method should return a FileRecipe instance,
a predefined Recipe instance (all of them inherit the FileRecipe) or an array of Recipe instances.

e.g

```php

use AntonioPrimera\Artisan\FileRecipe;
use AntonioPrimera\Artisan\FileRecipes\JsRecipe;

protected function recipe(): array
{
    //a simple file recipe (although you could use the LangRecipe class for this)
    $langRecipe = FileRecipe::create()
        ->withStub('path/to/langFile.php.stub')
        ->withTargetFolder(lang_path('en'))
        ->withFileNameTransformer(fn(string $fileName) => strtolower($fileName));
    
    //a predefined recipe for javascript files
    $jsRecipe = (new JsRecipe('path/to/stubFile.js.stub'))
            ->withFileNameTransformer('snake')
            ->withReplace(['GENERATED_ON', now()->format('d.m.Y H:i:s')]);
    
    return [
        'Lang File' => $langRecipe,
        'JS File'   => $jsRecipe,
    ];
}
```

**Note:** If you want more control over the command, you can take matters into your own hands and override the handle()
method and use the FileRecipe class directly.

## File recipe fluent interface

While in previous versions, recipes were created as associative arrays or simple constructor calls, but this was not
ideal, because it was hard to remember all the available attributes and their names. Now, the fluent interface
allows for a more readable and flexible recipe definition. The only 2 mandatory attributes are the stub and the target
folder. All other attributes are optional.

For example, to create a recipe for a json file, you could use the following code:

```php
$recipe = \AntonioPrimera\Artisan\FileRecipe::create()
    ->withStub('path/to/stubFile.json.stub')
    ->withTargetFolder('path/to/target/root')
    ->withFileNameTransformer(fn(string $fileName) => \Illuminate\Support\Str::kebab($fileName));
```

All methods of the fluent interface are prefixed with `with` and the attribute name in camel case, and are all
chainable.

#### withStub(string|File|Stub $stub)

This is the path pointing to the stub file. If an absolute path is given, the given path is used, without any
modifications. If the given path is relative, it is considered to be a path relative to the project root.

This attribute is **mandatory** for any recipe.

#### withTargetFolder(string|Folder $target)

This is the target root folder for the generated files. If this is an absolute path, the given path is used. If this
is a relative path, it is considered to be a path relative to the project root.

This attribute is **mandatory** for any recipe.

Note: this is not the final folder where the file will be generated, but the root folder for the target file. The
final folder will be determined by this target folder (set using this method) and the relative path to the target file,
which is inferred from the command argument.

e.g. if the target folder is `Http/Controllers/Site` and the command will be called with the argument
`SectionControllers/Guest/HeroSection`, the final target folder will be `app/Http/Site/SectionControllers/Guest`
(and the file name will be `HeroSection.php`).

#### withRootNamespace(string|null $rootNamespace)

This attribute is only relevant for generating php classes from stubs having the placeholder `DUMMY_NAMESPACE`. The
final namespace will be determined by the root namespace and the relative path to the target file.

The default rootNamespace value is `'App'`, but this is in most cases not a relevant setting. Maybe in future versions,
this will be inferred from the composer.json file automatically, but for now, you should set this manually.

e.g. if the root namespace is `App\Http\Controllers\Site`, and the command will be called with the argument
`SectionControllers/Guest/HeroSection`, the final namespace will be `App\Http\Controllers\Site\SectionControllers\Guest`.

#### withExtension(string|null $extension)

By default, the extension is inferred from the stub file. It is recommended that the stub file has a .stub extension
after the desired target extension

(e.g. `sampleFile.blade.php.stub`). The .stub extension is removed when guessing the target extension, so you don't
have to provide an extension in the recipe.

If the target extension shouldn't (or can't) be inferred from the stub file, you can use this recipe attribute to
set it. If a string is given, it will just be appended as an extension to the target file. If you want to generate
a file without any extension, use an empty string.

e.g. `'extension' => 'blade.php';`

#### withScope(string|null $scope)

This attribute is optional and useful just for the console output. It will be displayed in the console output, when
the command is run, to give the user a hint about the scope of the generated file.

e.g. `$recipe->withScope('View Component')`

#### withReplace(array $replace)

This optional attribute should receive an associative array of `placeholder => replace_with` items. This will replace
all placeholders in the stub files with the corresponding values.

By default, the generator command replaces 2 placeholders: DUMMY_CLASS and DUMMY_NAMESPACE. You can override these 2
default items with values of your own, but in most cases, these should work fine. If your generate a class or some file
with a namespace, you should use the `withRootNamespace(...)` method.

e.g. `$replace = ['GENERATED_BY' => 'Antonio Primera', 'GENERATED_ON' => now()->format('d.m.Y H:i:s')]`

#### withDefaultReplacements(array $defaultReplace)

This optional attribute should receive an associative array of `placeholder => replace_with` items. This will be used
as a set of default replacements, which can be overridden by the replacements provided to the `withReplace(...)` method.

This is useful if you want to create reusable recipes, which should have some default replacements, but can be
overridden by the command, which uses the recipe.

#### withFileNameTransformer(mixed $transformer)

This attribute should contain a callable or the name of a static method from the `Illuminate\Support\Str` helper class
(e.g. 'kebab' / 'snake' etc.). The callable will receive the target file name as an argument and should return the
desired file name.

If not provided, this attribute is `null` and will not change the file name in any way.

e.g. if you want to generate a migration file (without using the predefined migration recipe), you could use the following
transformer:

```php
$fileNameTransformer = fn(string $fileName) => date('Y_m_d_His') . '_' . \Illuminate\Support\Str::snake($fileName);
```

#### withRelativePathTransformer(mixed $transformer)

This attribute is optional and should contain a callable, which receives the relative path to the target file as an argument
and returns the desired relative path. This is useful if you want to change the target folder structure based on the target file name.

For example, if you want to generate a blade file, you would probably want to transform the relative path to the target
into kebab case, so that the view can be addressed in a dot notation with kebab case view name and path.

Note: The relative path is the path inferred from the command argument, so for example, if the command argument is
`SiteComponents/Sections/HeroSection`, the relative path will be `SiteComponents/Sections` and the target file name will
be `HeroSection`.

```php
//this will transform the relative path to the target file into kebab case
$relativePathTransformer = fn(string $relativePath) => array_map('Str::kebab', \AntonioPrimera\FileSystem\OS::pathParts($relativePath));

/**
 * For a command argument 'SiteComponents/Sections/HeroSection', the above transformer will return 'site-components/sections'.
 * The OS::pathParts method belongs to the 'antonioprimera/filesystem' package, which is a dependency of this package.
 */
```

## Hooks

#### beforeFileCreation()

You can override this hook in your command, to run any code after the recipe is created, but before generating the
files, according to the recipe.

#### afterFileCreation()

You can override this hook in your command, to run any code after all files from the recipe were successfully generated.
The hook receives a flag, whether the command was run in test mode (Dry Run), a list of absolute paths for all
generated files and the recipe used to generate the files.

#### cleanupAfterError()

This hook will run in case an error occurs during file generation and by default removes all the files generated by
the command until the error occurred (an Exception was thrown).

You can override this hook in your command, to run any code in case an exception was thrown during file generation.


## Advanced usage

### What if I want another command parameter to hold the target file name?

If for any reason, you want to use another command parameter to hold the target file name, you can override the default
`protected string $nameArgument = 'name';` attribute of the command (but in most cases you shouldn't).

## Upgrade Guide

### From 2.* to 3.0

### What changed

- The recipe class received a fluent interface, which allows for a more flexible and readable recipe definition.
Just make sure that each recipe has a stub and a target folder attribute. All other attributes are optional.

```php
//instead of providing all the recipe attributes, you can now chain the setters
$recipe = \AntonioPrimera\Artisan\FileRecipe::create()
    ->withStub('path/to/stubFile.json.stub')
    ->withTargetFolder('path/to/target/root')
    ->withFileNameTransformer(fn(string $fileName) => strtolower($fileName));
```

- prebuilt recipes are now available for the most common files in a Laravel project
  - `BladeRecipe` for blade files
  - `CommandRecipe` for artisan commands
  - `ConfigRecipe` for config files
  - `ControllerRecipe` for controllers
  - `JsRecipe` for javascript files
  - `LangRecipe` for language files
  - `MigrationRecipe` for migration files
  - `ModelRecipe` for Eloquent models
  - `ServiceProviderRecipe` for service providers
  - `StyleSheetRecipe` for css files (css / pcss / sass etc. - just provide the correctly named stub file)
  - `ViewComponentRecipe` for view component classes

```php
//in a generator command, you can define the recipe like this
use AntonioPrimera\Artisan\FileRecipes\MigrationRecipe;

//this will create a migration file recipe, prepending the current timestamp to the file name
protected function recipe(): MigrationRecipe
{
    return (new MigrationRecipe('stubs/migration-file.php.stub'))
        ->withReplace(['GENERATED_ON' => now()->format('d.m.Y H:i:s')]);    //add any additional replace items
}
```

### Breaking changes

- all recipe setters are now prefixed with "with" (e.g. withReplace(...) instead of replace(...))
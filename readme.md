# Antonio Primera's Laravel File Generator Command

This is a very simple package, with one single goal: to support the creation of Artisan Commands, which generate
files based on stubs.

The Generator Command offered by Laravel is good enough for simple files, but is poorly documented and lacks some
basic options, which I needed in most of my projects. After writing a few generator commands for my packages, I decided
to extract this into a simple package. If you feel like contributing, please don't be shy and drop me a line.

## Installation

Via composer import:

`composer require --dev antonioprimera/laravel-generator-command`

## Usage

The package comes with an artisan generator command to generate a generator command (sounds a bit recursive, and it is)
in your project:

`php artisan make:generator-command <CommandName>`

This will create a new generator command in your **app/Console/Commands** folder.

The new command class inherits the abstract `AntonioPrimera\Artisan\FileGeneratorCommand` and contains a ToDo list and
some commented recipe samples to get you inspired and started as fast as possible.

The FileGeneratorCommand extends the default Laravel Console Command, so you need to provide a signature and optionally
a description. The signature should have a `{name}` parameter, to get the target file name from the console input.

e.g.
```php
protected $signature = 'make:an-awesome-file {name}';
protected $description = 'Generate an awesome file.';
```

If for any reason, you want to use another command parameter to hold the target file name, you can override the default
`protected string $nameArgument = 'name';` attribute of the command (but in most cases you shouldn't).

### Command structure

#### 1. Create a simple recipe overriding $recipe

For simple file generation tasks, based on a stub file, this option is the easiest: just override the protected $recipe
attribute of your generator command. First an example, and then we will go into all the options and limitations:

```php
    protected $signature = 'make:special-file {name}';
    
    protected array $recipe = [
        'SpecialFile' => [
            'stub' => 'path/to/your/StubFile.ext.stub',
            'path' => 'target/path/relative/to/project/root',        
        ],
    ];
```

If you call the artisan command via:

`php artisan make:special-file MyTargetFile`

It will create the target file at `app_path('target/path/relative/to/project/root/MyTargetFile.ext')`. By default,
the command will infer the extension from the stub file and if the paths to the stub file or to the target folder are
not absolute paths, it will consider them paths relative to the project root (check the ***File recipe options*** chapter
below for a more detailed explanation).

Please be aware that even if you need to generate a single file, you need to provide the recipe for that file in
an array within the recipe array.

You can use this method of creating file recipes if you don't need to call any methods in your recipe settings, because
you can't call any methods in defining the attribute default values. If you need something more complex, you can use
the other 2 methods described below. Check out the file recipe options for a full description of available recipe
attributes.

#### 2. Create a set of complex recipes overriding recipe()

If you need to use method / function calls in your recipe, you should override the protected `recipe()` method in your
generator command. This method should return an associative array of `recipeName => recipe` items.

The recipe can either be an array, like exemplified above or an instance of `AntonioPrimera\Artisan\FileRecipe`, which
is just a simple class with all available recipe options exposed as public attributes. If you are using an IDE with a
php linter, this should make it easier for you to set the right recipe attributes and not worry about their names.

e.g

```php

use AntonioPrimera\Artisan\FileRecipe;

protected function recipe(): array
{
    //the file recipe constructor will require the only 2 mandatory attributes for a recipe
    $jsonRecipe = new FileRecipe('path/to/stubFile.json.stub', 'path/to/target/root');
    $jsonRecipe->fileNameFormat = function($fileName) { return strtolower($fileName); };
    $jsonRecipe->rootPath = $this->determineJsonRootPath();
    
    $jsRecipe = new FileRecipe('path/to/stubFile.js.stub', public_path('js'));
    $jsRecipe->replace = ['GENERATED_ON' => now()->format('d.m.Y H:i:s')];
    $jsRecipe->fileNameFormat = 'snake';
    
    return [
        'JSON File' => $jsonRecipe,
        'JS File'   => $jsRecipe,
    ];
}
```

#### 3. Take matters into your own hands and override the handle() method

If the previous methods do not offer the necessary flexibility, you can always override the handle method and use
the `AntonioPrimera\Artisan\Stub` class to create stub file instances and generate files. For the moment, the Stub
class is not documented, because I don't think that anybody will use it directly, as the Generator Command class
provides enough flexibility as it is. If you really need more flexibility, drop me a line with your use case and
I will see if / how I can help you.

### Hooks

#### beforeFileCreation

You can override this hook in your command, to run any code after the recipe is created, but before generating the
files, according to the recipe.

```php
protected function beforeFileCreation(bool $isDryRun, array $recipe)
{
   //add any code which should be run before file generation
}
```

#### afterFileCreation

You can override this hook in your command, to run any code after all files from the recipe were successfully generated.
The hook receives a flag, whether the command was run in test mode (Dry Run), a list of absolute paths for all
generated files and the recipe used to generate the files.

```php
protected function afterFileCreation(bool $isDryRun, array $createdFiles, array $recipe)
{
   //add any code which should be run after file generation
}
```

#### cleanupAfterError

This hook will run in case an error occurs during file generation and by default removes all the files generated by
the command until the error occurred (an Exception was thrown).

You can override this hook in your command, to run any code in case an exception was thrown during file generation.
The hook receives a flag, whether the command was run in test mode (Dry Run), a list of absolute paths for all
generated files and the recipe used to generate the files.

```php
protected function cleanupAfterError(bool $isDryRun, array $createdFiles, array $recipe)
{
    //the parent method removes generated files, until the error occurred
    parent::cleanupAfterError($isDryRun, $createdFiles, $recipe);
    
    //add additional logic, or override the method completely
}
```


### File recipe attributes

#### stub

This is the path pointing to the stub file. If an absolute path is given, the given path is used, without any
modifications. If the given path is relative (it doesn't start with '/'), by default it is considered to be a path
relative to the project root.

This attribute is **mandatory** for any recipe.

e.g. an absolute path `__DIR__ . '/stubs/my-stub.php.stub'`
     a relative path `'app/Console/Commands/stubs/my-stub.php.stub'` will be transformed into
`app_path('app/Console/Commands/stubs/my-stub.php.stub')`

For using paths relative to a path other than the project root, see option `rootPath`.

#### path

This is the target root folder for the generated files. If this is an absolute path, the given path is used. If this
is a relative path (it doesn't start with '/'), by default it is considered to be a path relative to the project root.

The path attribute can also contain a callable, which will be executed to retrieve the path. For example, if you want
to generate a config file, you could have `$path = 'config_path';`, which is equivalent to `$path = config_path();`.
You can use any of the Laravel path helper functions or even use a custom public method implemented in your command.
Please make sure that the method is callable from outside the command (this means in general that the method must be
public).

This attribute is **mandatory** for any recipe.

For using paths relative to a path other than the project root, see option `rootPath`.

#### rootPath

This is the default root path to use when the `stub` and target root `path` attributes are relative paths. This can
be a callable, like `app_path` or an absolute path. Although you could also use a relative path, this is not advised,
because the results might be unpredictable.

This attribute is optional, and its default value is `'app_path'`.

#### extension

By default, this attribute is `null`, so the extension is inferred from the stub file. Although not necessary, it is
recommended that the stub file has a .stub extension after the desired target extension
(e.g. `sampleFile.blade.php.stub`). The .stub extension is removed when guessing the target extension.

If the target extension shouldn't (or can't) be inferred from the stub file, you can use this recipe attribute to
set it. If a string is given, it will just be appended as an extension to the target file. If you want to generate
a file without any extension, use an empty string.

e.g. `$extension = 'blade.php';` (with or without a trailing '.')

#### replace

This optional attribute should receive an associative array of `placeholder => replace_with` items. This will replace
all placeholders in the stub files with the corresponding values.

By default, the generator command replaces 2 placeholders: DUMMY_CLASS and DUMMY_NAMESPACE.

You can override these 2 default items with values of your own, but for most cases, these should work fine.

If your generate a class or some file with a namespace, you should set the `rootNamespace` recipe attribute, because
the namespace (in most cases a psr4 namespace) is very hard to guess (see the `rootNamespace` attribute documentation
below).

e.g. `$replace = ['GENERATED_BY' => 'Antonio Primera']`

#### rootNamespace

Set this attribute, if you are generating a file with a namespace, to whatever your root namespace for the generated
file is. This will replace the DUMMY_NAMESPACE placeholder in the stub file with the determined namespace.

If you are generating files without a namespace this attribute is not relevant.

e.g. in order to generate a basic controller, you could use something like:

```php
$recipe = [
    'Awesome Controller' => [
        'stub' => __DIR__ . '/stubs/Controller.php.stub',
        'path' => 'Http/Controllers',
        'rootNamespace' => 'App/Http/Controllers',
    ],
];
```

Calling the artisan command:
`php artisan make:special-file MyModule/MySpecialController`

Will generate a controller `class MySpecialController` with `namespace App\Http\Controllers\MyModule;` in your project
at `app/Http/Controllers/MyModule/MySpecialController.php`.

The default rootNamespace value is `'App'`, but this is in most cases not a relevant setting.

#### fileNameFormat

This attribute should contain either the name of an `Illuminate\Support\Str` static method (e.g. 'kebab' / 'snake' etc.)
or a callable receiving the file name inferred from the artisan command as an attribute and returning the desired
file name.

If not provided, this attribute is `null` and will not change the file name in any way.

#### scope

The scope attribute is optional, and is used in the console to inform the user that a file with this scope was created.

The default success message is: `"Created new $scope at: $path"`. If this attribute is not set, the key of the file
recipe is used.

e.g. for the sample recipe given above (see option `rootNamespace`) the following message will be displayed in the
console: `Created new Awesome Controller at: app/Http/Controllers/MyModule/MySpecialController.php`
# Antonio Primera's Laravel File Generator Command

This is a very simple package, with one single goal: to create Artisan Commands, which generate files based on stubs.

The Generator Command offered by Laravel is good enough for simple files, but is poorly documented and lacks some
basic options, which I needed in most of my projects. After writing a few generator commands for my packages, I decided
to extract this into a simple package. If you feel like contributing, please don't be shy and drop me a line.

## Installation

Via composer import:

```bash
composer require --dev antonioprimera/laravel-generator-command
```

## Usage

The package comes with an artisan generator command to generate a generator command (sounds a bit recursive, and it is)
in your project:

```bash
php artisan make:generator-command <CommandName>
```

This will create a new command in your `app/Console/Commands` folder.

The new command class inherits the abstract `AntonioPrimera\Artisan\FileGeneratorCommand` and contains a ToDo list and
some commented recipe samples to get you inspired and started as fast as possible.

The FileGeneratorCommand extends the default Laravel Console Command, so you need to provide a signature and optionally
a description. The signature should have a `{name}` parameter, to get the target file name from the console input.

e.g.
```php
protected $signature = 'model:create-vue-assets {name}';
protected $description = 'Generate the vue assets for the given model.';
```

### Command structure

#### 1. Create a simple recipe overriding $recipe

For simple file generation tasks, based on a stub file, this option is the easiest: just override the protected $recipe
attribute of your generator command. First an example, and then we will go into all the options and limitations:

```php
    protected $signature = 'model:make-vue-list-item {name}';
    
    protected array $recipe = [
        'Vue List Item' => [
            'stub' => __DIR__ . '/stubs/vue-list-item.vue.stub',
            'target' => 'resources/vue/components/list-items',
        ],
    ];
```

If you call the artisan command via:

```bash
php artisan model:make-vue-list-item User
```

It will create the target file at `base_path('resources/vue/components/list-items/User.vue')`. By default,
the command will infer the extension from the stub file and if the paths to the stub file or to the target folder are
not absolute paths, it will consider them paths relative to the project root (check the ***File recipe options*** chapter
below for a more detailed explanation).

#### 2. Create a set of complex recipes overriding the recipe() method

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
    $jsonRecipe = FileRecipe::create(
             stub: 'path/to/stubFile.json.stub',
             targetFolder: 'path/to/target/root',
             fileNameTransformer: fn(string $fileName) => strtolower($fileName)
        );
    
    $jsRecipe = FileRecipe::create(
             stub: 'path/to/stubFile.js.stub',
             targetFolder: public_path('js'),
             fileNameTransformer: 'snake',
             replace: ['GENERATED_ON', now()->format('d.m.Y H:i:s')]
         );
    
    return [
        'JSON File' => $jsonRecipe,
        'JS File'   => $jsRecipe,
    ];
}
```

#### 3. Take matters into your own hands and override the handle() method

If the previous methods do not offer the necessary flexibility, you can always override the handle method and use
the `AntonioPrimera\Artisan\Stub` class to create stub file instances and generate files. For the moment, the Stub
class is not documented here, but it is fairly easy to understand and use.

### Hooks

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

### File recipe attributes

#### stub

This is the path pointing to the stub file. If an absolute path is given, the given path is used, without any
modifications. If the given path is relative (it doesn't start with '/'), by default it is considered to be a path
relative to the project root.

This attribute is **mandatory** for any recipe.

e.g. 
- an absolute path `__DIR__ . '/stubs/my-stub.php.stub'`
- a relative path `'app/Console/Commands/stubs/my-stub.php.stub'` will be transformed into
`base_path('app/Console/Commands/stubs/my-stub.php.stub')`
- an AntonioPrimera\Artisan\File instance `File::createFromPath('app/Console/Commands/stubs/my-stub.php.stub')`

#### target

This is the target root folder for the generated files. If this is an absolute path, the given path is used. If this
is a relative path (it doesn't start with '/'), by default it is considered to be a path relative to the project root.

This attribute is **mandatory** for any recipe.

e.g.
- an absolute path `public_path('js')`
- a relative path `'public/js'` will be transformed into `base_path('public/js')`
- an AntonioPrimera\Artisan\File instance `File::create(folder: 'public/js')

If you use a File instance, be sure to only set the folder attribute, because the file name will be inferred from the
artisan command input.

#### extension

By default, this attribute is `null`, so the extension is inferred from the stub file. Although not necessary, it is
recommended that the stub file has a .stub extension after the desired target extension
(e.g. `sampleFile.blade.php.stub`). The .stub extension is removed when guessing the target extension.

If the target extension shouldn't (or can't) be inferred from the stub file, you can use this recipe attribute to
set it. If a string is given, it will just be appended as an extension to the target file. If you want to generate
a file without any extension, use an empty string.

e.g. `'extension' => 'blade.php';`

Optionally, you can provide the extension as a parameter to the target File instance, if you are using one.

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
`php artisan make:special-controller-file MyModule/MySpecialController`

Will generate a controller `class MySpecialController` with the namespace `App\Http\Controllers\MyModule;` in your project
at `app/Http/Controllers/MyModule/MySpecialController.php`.

The default rootNamespace value is `'App'`, but this is in most cases not a relevant setting.

#### fileNameFormat or fileNameTransformer

This attribute should contain either the name of an `Illuminate\Support\Str` static method (e.g. 'kebab' / 'snake' etc.)
or a callable receiving the file name inferred from the artisan command as an attribute and returning the desired
file name.

If not provided, this attribute is `null` and will not change the file name in any way.

## Advanced usage

### What if I want another command parameter to hold the target file name?

If for any reason, you want to use another command parameter to hold the target file name, you can override the default
`protected string $nameArgument = 'name';` attribute of the command (but in most cases you shouldn't).

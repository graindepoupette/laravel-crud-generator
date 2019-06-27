# Laravel CRUD Generator
Laravel CRUD Generator

*** Current for internal testing ***

# Dependencies

This package generates CRUD Controllers for `laravel-crud`

# Installation

`composer require imtigger/laravel-crud-generator --dev`

# Usage

## Make your own theme

`php artisan vendor:publish --provider="Imtigger\LaravelCRUD\LaravelCRUDGeneratorServiceProvider"`

Edit `resources/crud-stubs/views/layout.blade.php.stub` to adapt it to your favorite theme!

## Generate CRUD Model + View + Controller + Migration + Form

```
php artisan make:crud --help
Usage:
  make:crud [options] [--] <name>

Arguments:
  name

Options:
      --form            (Re)generate only form
      --model           (Re)generate only model
      --view            (Re)generate only view
      --no-model        Generates no model
      --no-view         Generates no view
      --no-controller   Generates no controller
      --no-form         Generates no form
      --no-migration    Generates no migration
      --no-soft-delete  No soft delete
      --no-ui           Shortcut for --no-view, --no-controller and --no-form
```

Workflow
1. `php artisan make:crud Animal`
2. Edit generated migrations and run `php artisan migrate`
3. `php artisan migrate`
4. (Optional) `php artisan make:crud --[form|model|view]` to regenerate Form/Model/View from actual database

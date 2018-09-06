# Laravel CRUD Generator
Laravel CRUD Generator

- WIP, Current for internal testing

# Dependencies

This package generates CRUD Controllers for `laravel-crud`

# Installation

`composer require imtigger/laravel-crud-generator`

# Usage

CRUD generator

```
php artisan make:crud --help
Usage:
  make:crud [options] [--] <name>

Arguments:
  name

Options:
      --no-model        Generates no model
      --no-view         Generates no view
      --no-controller   Generates no controller
      --no-form         Generates no form
      --no-migration    Generates no migration
      --no-soft-delete  No soft delete
      --no-ui           Shortcut for --no-view, --no-controller and --no-form
```

CRUD index view Datatable header generator

```
php artisan make:crud:header --help
Usage:
  make:crud:header <table>

Arguments:
  table
```

Translation string generator

```
php artisan make:crud:trans --help
Usage:
  make:crud:trans
```


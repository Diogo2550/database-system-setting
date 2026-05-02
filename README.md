<div align="center">

# 🚀 Laravel Database System Settings

[![Latest Version on Packagist](https://img.shields.io/packagist/v/diogo2550/database-system-setting.svg?style=flat-square)](https://packagist.org/packages/diogo2550/database-system-setting)
[![Total Downloads](https://img.shields.io/packagist/dt/diogo2550/database-system-setting.svg?style=flat-square)](https://packagist.org/packages/diogo2550/database-system-setting)
[![License](https://img.shields.io/packagist/l/diogo2550/database-system-setting.svg?style=flat-square)](https://packagist.org/packages/diogo2550/database-system-setting)

</div>

**Dynamic database settings without sacrificing performance or your Developer Experience (DX).**

**Database System Settings** is an elegant Laravel package that solves a classic problem: how to give end-users (or administrators) control over settings without losing the framework's native features.

## 📌 Why use this package?

In traditional Laravel applications, we rely on `.env` or static files in `config/` to manage global variables. But what happens when you need to change the number of items displayed on the homepage, enable/disable an ad network, or change an API key **without needing to deploy or access the server**?

This package brings the best of both worlds: the flexibility of a database with the speed and predictability of native config files. 

It's the perfect tool to integrate with modern admin panels (like **FilamentPHP**), allowing you to build amazing configuration interfaces in minutes.

### ✨ Highlighted Features

*   **👨‍💻 Flawless DX:** Native Autocomplete support in your IDE through the [official Laravel extension](https://marketplace.visualstudio.com/items?itemName=laravel.vscode-laravel). Forget the need to memorize database keys.
*   **⚡ Performance First:** Smart and automatic caching. Your settings are loaded from the database *only once* and kept in cache. The database is only queried again when a setting is changed.
*   **🔄 Elegant Synchronization:** Intuitive Artisan commands to sync, create, and prune orphan settings based on your schema.
*   **🛠️ Zero Friction:** Continue accessing your data exactly as you always have: `config('settings.my_key')`.

## 📦 Installation

Installation is quick and straightforward. Start by requiring the package via Composer:

```bash
composer require diogo2550/database-system-setting
```

Next, publish the configuration files and the migration:

```bash
php artisan vendor:publish --tag=database-system-setting
```

Finally, run the migrations to create the `system_settings` table:

```bash
php artisan migrate
```

## ⚙️ How Configuration Works

The package's architecture is based on two main files published in your `config/` folder:

1.  **`settings.php`**: Where keys are mapped to ensure your IDE (VS Code, PhpStorm) can provide perfect autocomplete via `config()`.
2.  **`settings-schema.php`**: (Optional, but recommended) The heart of your validation. Here you define types, default values, and rules for your variables.

### Building your Schema

The Schema is where you define the behavior and boundaries of your variables. Below is an example focused on a real application that needs to manage content rotation and monetization:

```php
<?php
// config/settings-schema.php

return [
    'schema' => [
        'trending_items_limit' => [
            'default' => 7,
            'description' => 'Number of items displayed in the "Trending" section on the Home page',
            'schema' => [
                'type' => 'integer',
                'required' => true,
                'min' => 1,
                'max' => 20,
            ],
        ],
        'enable_ezoic_ads' => [
            'default' => true,
            'description' => 'Enables or disables ad display globally',
            'schema' => [
                'type' => 'boolean',
                'required' => true,
            ],
        ],
        'contact_email' => [
            'default' => 'contact@mysite.com',
            'description' => 'Official email for receiving feedback',
            'schema' => [
                'type' => 'email',
                'required' => true,
            ],
        ],
    ]
];
```

### Syncing with the Database

After defining your schema, sync the information with the database. Existing values are always preserved!

```bash
php artisan database-system-setting:sync
```

> **Cleanup tip:** Removed a setting from the code and want to delete it from the database? Use the `--prune` flag:
> `php artisan database-system-setting:sync --prune`

## 💻 Practical Usage

You don't need to learn a new syntax. Use the good old `config()` helper anywhere in your code: Controllers, Views (Blade), Middlewares, or Jobs.

```php
// In a Controller to set pagination
$limit = config('settings.trending_items_limit', 7);
$articles = Article::trending()->take($limit)->get();

// In a Blade View to conditionally render blocks
@if(config('settings.enable_ezoic_ads'))
    <div id="ezoic-pub-ad-placeholder-101"></div>
@endif
```

## 🚀 The Cache Engine (Under the Hood)

Reading settings from the database on every *request* is the biggest performance mistake an application can make. **Database System Settings** solves this with a smart invalidatable cache flow:

1.  **Application Boot:** Laravel fetches the data from the database **once** and generates a cache with a 24-hour TTL.
2.  **Subsequent Requests:** Instant responses straight from the Cache (0 database queries).
3.  **Automatic Invalidation:** If you (or your Admin panel) perform an *Update*, *Create*, or *Delete* on the `SystemSetting` model, the package **destroys the cache instantly**. The next request rebuilds the cache with fresh data.

### Customizing the Cache

Need to disable the cache in the local environment or change the expiration time? Just edit the `__internal__` key in the `config/settings.php` file:

```php
'__internal__' => [
    'cache' => [
        'enabled' => env('SETTINGS_CACHE_ENABLED', true), // Easy control via .env
        'key' => 'dsystem_setting',
        'ttl' => 60 * 60 * 24, // 24 hours in seconds
    ],
    'table_name' => 'system_settings',
],
```

---

## 📊 Database Structure

For reference, the `system_settings` table generated by the migration has the following structure:

| Column        | Type        | Functionality                                 |
| :------------ | :---------- | :-------------------------------------------- |
| `id`          | `bigint`    | Primary key.                                  |
| `key`         | `string`    | Unique configuration key (e.g., `site_name`). |
| `value`       | `text`      | Stored value (natively supports JSON).        |
| `description` | `text`      | User-friendly description for admin panels.   |
| `schema`      | `json`      | Validation rules and metadata.                |
| `updated_at`  | `timestamp` | Trigger for cache invalidation.               |

---

## 🤝 Contributing

Contributions are always welcome! If you have ideas to improve the package, feel free to open an *Issue* or submit a *Pull Request*.

## 📄 License

This project is open-source and licensed under the terms of the [MIT License](LICENSE).

---
<div align="center">
  <b>Made with ☕ and clean code by Diogo Alves</b>
</div>
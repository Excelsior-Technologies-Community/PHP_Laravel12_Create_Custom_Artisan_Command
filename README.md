# 🚀 Laravel 12 Custom Artisan Command – Premium README  


<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-f72c1f?style=for-the-badge&logo=laravel" />
  <img src="https://img.shields.io/badge/PHP-8.2-blue?style=for-the-badge&logo=php" />
  <img src="https://img.shields.io/badge/Command-Custom-green?style=for-the-badge" />
</p>

---

# 📌 Overview
This project demonstrates how to create and register a **Custom Artisan Command** in **Laravel 12**.

It includes:

- Custom command execution  
- Writing log output  
- Automatic command setup  
- Kernel registration  
- Running the command manually or with scheduler  

---

# ⭐ Features
- 🛠 Create Custom Artisan Command  
- 📝 Auto-generate users (example use case)  
- 🔧 Works with Laravel Scheduler  
- 📄 Clean folder structure  
- 🚀 Ready-to-run setup  

---

# 📁 Folder Structure

```
app/
├── Console/Commands/
│   └── CreateUsers.php
├── Http/Controllers/
├── Models/
routes/
├── console.php
└── web.php
```

---

# ⚙ Installation

```bash
composer create-project laravel/laravel example-app "12.*"
```

---

# 🗄 Environment Setup

Configure your `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=custome_command
DB_USERNAME=root
DB_PASSWORD=


---

# 🔌 Create Custom Command

```bash
php artisan make:command CreateUsers
```

---

# 📦 Command Logic  

File: `app/Console/Commands/CreateUsers.php`

```php
protected $signature = 'create:users {count}';

public function handle()
{
    $count = $this->argument('count');

    for ($i = 0; $i < $count; $i++) {
        \App\Models\User::factory()->create();
    }

    $this->info("$count users created successfully.");
}
```

---

# 🏗 Register Command  

File: `app/Console/Kernel.php`

```php
protected $commands = [
    Commands\CreateUsers::class,
];
```

---

# ▶ Run Command

```bash
php artisan create:users 10
```

---

# 📝 Console Route (Optional)
Add CLI-only routes inside:

`routes/console.php`

---

# 📸 Screenshots 

<img width="975" height="919" alt="image" src="https://github.com/user-attachments/assets/09761357-cea7-4e19-a3d8-e4416d39864b" />


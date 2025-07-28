# Task 5 – Q&A

---

## **A. Explain this code**

```php
Schedule::command('app:example-command')
    ->withoutOverlapping()
    ->hourly()
    ->onOneServer()
    ->runInBackground();
```

### Explanation:

This is a Laravel scheduled task definition using the `Task Scheduling` API. Here's what each method means:

- `Schedule::command('app:example-command')`: Defines artisan command to be run.
- `->withoutOverlapping()`: Prevents a new instance of the command from running if the previous one is still executing.
- `->hourly()`: Runs the command every hour.
- `->onOneServer()`: Ensures the command runs only on one server in a multi-server setup.
- `->runInBackground()`: Runs the command in the background (non blocking), allowing the scheduler to move on without waiting for this command to finish.

- **Use Case**: Perfect for long running tasks like report generation. 

---

## **B. What is the difference between the Context and Cache Facades? Provide examples to illustrate your explanation.**

### 1. **Context**:

Laravel doesn't have a built-in `Context` facade by default. 

### 2. **Cache** (Core Laravel Facade):

The `Cache` facade provides access to Laravel's caching system.

```php
Cache::put('key', 'value', 300);
$value = Cache::get('key');
```

**Use Case**: To temporarily store and retrieve data to speed up your app by avoiding repeated or costly operations like database queries or API calls.

---

## **C. What's the difference between `$query->update()`, `$model->update()`, and `$model->updateQuietly()` in Laravel, and when would you use each?**

### 1. `$query->update()`

Used on a query builder to update multiple rows without loading models or firing model events.

```php
User::where('is_active', false)->update(['status' => 'inactive']);
```

- **Use when** you want a fast update with no model events (e.g no `saving`) etc. It's also efficient for batch updates.

---

### 2. `$model->update()`

Used on an eloquent model instance. Updates attributes and fires events like `updating` etc.

```php
$user = User::find(1);
$user->update(['name' => 'Christian']);
```

- **Use when** you want model events etc.

---

### 3. `$model->updateQuietly()`

Same as `$model->update()` but **suppresses** model events.

```php
$user->updateQuietly(['name' => 'Christian']);
```

- **Use when** you want to update model data **without** triggering events.
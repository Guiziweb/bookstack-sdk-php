# BookStack PHP SDK

A PHP SDK for the BookStack API with DTOs, validation, and comprehensive documentation.

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-blue.svg)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%208-brightgreen.svg)](https://phpstan.org/)
[![Tests](https://img.shields.io/badge/tests-passing-brightgreen.svg)]()

## 🚀 Installation

```bash
composer require guiziweb/bookstack-sdk-php
```

## ⚡ Quick Usage

### Create the client

```php
<?php

use Guiziweb\BookStackSdk\BookStackClientFactory;

$client = BookStackClientFactory::create(
    'https://your-bookstack-instance.com',
    'your-api-token-id',
    'your-api-token-secret'
);
```

### Basic examples

```php
$booksResult = $client->books()->list();
foreach ($booksResult['data'] as $book) {
    echo $book->name;  
}

$book = $client->books()->create([
    'name' => 'My new book',
    'description' => 'Description'
]);
echo $book->id;

$results = $client->search()->search('my search term');
foreach ($results['data'] as $result) {
    echo $result->name; 
}
```

## 📋 Available Services

The client covers the BookStack API endpoints:

| Service | Description | DTOs                   |
|---------|-------------|-----------------------------|
| **books()** | Books (CRUD + export) | Book, BookContent, Tag, Cover, PageSummary |
| **pages()** | Pages (CRUD + export) | Page                        |
| **chapters()** | Chapters (CRUD + export) | Chapter                     |
| **shelves()** | Bookshelves (CRUD) | Shelf                       |
| **users()** | Users (CRUD) | User, UserRole              |
| **roles()** | Roles and permissions | Role, RoleUser              |
| **search()** | Global search | SearchResult                |
| **images()** | Images | Image, ImageUser            |
| **attachments()** | File attachments | Attachment                  |
| **recycleBin()** | Recycle bin | RecycleBinItem              |
| **contentPermissions()** | Permissions | ContentPermission, RolePermission, etc. |
| **auditLogs()** | Audit logs | AuditLogEntry, AuditUser    |

## 📚 Detailed Examples

### Books management

```php
// List with pagination and sorting
$books = $client->books()->list(50, 0, ['name' => 'asc']);

// Complete CRUD
$book = $client->books()->create(['name' => 'Test']);
$book = $client->books()->show(1);
$book = $client->books()->update(1, ['name' => 'New name']);
$client->books()->delete(1);

// Export PDF/HTML/etc.
$pdf = $client->books()->export(1, 'pdf');
$html = $client->books()->export(1, 'html');
```

### Search

```php
// General search
$results = $client->search()->search('term', 20);

// Specialized search
$books = $client->search()->searchBooks('guide');
$pages = $client->search()->searchPages('installation');
```

### User management

```php
// Create a user
$user = $client->users()->create([
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);

// List users
$users = $client->users()->list();
```

### Content permissions

```php
// Get book permissions
$permissions = $client->contentPermissions()->getPermissions('book', 1);

// Update permissions
$updated = $client->contentPermissions()->set('book', 1, [
    'role_permissions' => [
        2 => ['view' => true, 'update' => false, 'delete' => false]
    ]
]);
```

## 🛡️ Error Handling

```php
use Guiziweb\BookStackClient\Exception\BookStackClientException;
use Guiziweb\BookStackClient\Exception\ValidationException;

try {
    $book = $client->books()->show(999);
} catch (ValidationException $e) {
    // Invalid parameters
    echo "Validation error: " . $e->getMessage();
} catch (BookStackClientException $e) {
    // API error (404, 403, etc.)
    echo "API error: " . $e->getMessage();
}
```

## ⚙️ Advanced Configuration

```php
// With custom options
$client = BookStackClientFactory::createWithOptions(
    'https://your-bookstack-instance.com',
    'your-api-token-id',
    'your-api-token-secret',
    [
        'timeout' => 60,
        'max_redirects' => 5,
        'headers' => ['User-Agent' => 'My-App/1.0']
    ]
);

// With custom HTTP client
use Symfony\Component\HttpClient\HttpClient;

$httpClient = HttpClient::create(['timeout' => 30]);
$client = BookStackClientFactory::createWithHttpClient(
    $httpClient,
    'https://your-bookstack-instance.com',
    'your-api-token-id',
    'your-api-token-secret'
);
```

## 🧪 Tests

### Unit tests (fast)
```bash
# Unit tests only
make test-unit

# With code coverage
make coverage
```

### Integration tests (require BookStack)
```bash
# Start BookStack locally with automatic API token creation
make ci-setup

# Run integration tests
make ci-test

# Stop test BookStack
make ci-down
```

### Complete tests
```bash
# All tests (unit + integration if BookStack available)
make test
```

## 📋 Requirements

- **PHP 8.2+**
- **BookStack v24.05+** (tested with v25.07)
- Valid API keys

**Note:** For ZIP exports with BookStack v25.05+, add `'zip'` to the `EXPORT_FORMATS` array in `src/Validator/ParameterValidator.php`

## 📄 License

MIT License. See [LICENSE](LICENSE) for more details.

## 🔗 Links

- [BookStack API Documentation](https://demo.bookstackapp.com/api/docs)
- [BookStack Official Website](https://www.bookstackapp.com/)
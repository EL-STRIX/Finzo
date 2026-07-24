---
title: "Unit Testing Guidelines"
version: "1.0.0"
status: "Active"
owner: "QA Architecture Team"
category: "Testing Core"
last_updated: "2026-07-24"
---

# Unit Testing Guidelines

> This document outlines the principles, conventions, and mocking strategies for writing highly isolated, deterministic unit tests within the Finzo Clean Architecture structure.

## 1. Purpose and Scope

**Unit Tests** validate the smallest testable parts of the application (classes and methods) in complete isolation. 
The scope of unit testing in Finzo primarily covers:
* Application Services (`App\Application\Services`)
* Domain Models (`App\Domain\Models`)
* Core Validators (`App\Shared\Core\Validation`)
* Helper utilities and Factories

*Note: Database interactions (Repositories) and HTTP request simulations (Controllers) are explicitly excluded from Unit Tests. See [Integration Testing](Integration-Testing.md).*

## 2. Unit Testing Principles

1. **Isolation:** Unit tests must never touch the database, filesystem, or external APIs. 
2. **Speed:** Because they are isolated, unit tests must execute in milliseconds.
3. **Determinism:** A unit test must always yield the same result. Avoid dependencies on `date()`, `time()`, or `rand()` unless strictly mocked.
4. **Single Responsibility:** Each test method should ideally assert one specific behavior or logical path.

## 3. Test Organization and Naming

Unit tests reside in the `tests/Unit/` directory. The folder structure must exactly mirror the `app/` namespace structure.

### 3.1 Naming Conventions
* **Class Names:** Suffix the target class name with `Test` (e.g., `UserServiceTest`).
* **Method Names:** Use descriptive `camelCase` starting with `test` or `snake_case` prefixed with `test_` to clearly define the behavior being asserted.
  * *Example:* `testValidateProfileUpdateFailsOnExistingEmail()`
  * *Example:* `test_age_must_be_at_least_18()`

## 4. Layer-Specific Testing Strategies

### 4.1 Service Testing
Services contain the core business logic of Finzo (e.g., Transfers, KYC limits). 
When testing a Service, **all injected Repositories must be mocked**.

**Best Practice:**
```php
public function testServiceRejectsTransferIfFundsInsufficient() {
    $repoMock = $this->createMock(AccountRepository::class);
    // Mock the repository to return an account with only $10
    $repoMock->method('getAccountByNo')->willReturn(['balance' => 10.00]);
    
    $service = new TransferService($repoMock);
    $result = $service->executeTransfer('ACC1', 'ACC2', 50.00);
    
    $this->assertFalse($result['success']);
    $this->assertEquals('Insufficient funds', $result['message']);
}
```

### 4.2 Validator Testing
Validators should be tested by passing exhaustive arrays of data (both valid and invalid) to ensure edge cases (missing keys, invalid formats) trigger the correct `ValidationException`.

### 4.3 Model Testing
Domain models (Data Transfer Objects) should be tested to ensure that getters, setters, and internal sanitization rules enforce domain invariants.

## 5. Mocking Strategy

Finzo strictly utilizes native **PHPUnit Stubs and Mocks**.
* Use `$this->createMock(ClassName::class)` to generate mock objects.
* Use `$mock->method('func')->willReturn($val)` to stub return data.
* Use `$mock->expects($this->once())->method('func')` if you need to assert that a dependency was actively invoked by the Service.

> [!WARNING]
> **Deprecation Notice:** Do not use `$this->any()` or `$this->expects($this->any())` as it is deprecated in modern PHPUnit 10+. Use native stubs (`$mock->method(...)`) instead.

## 6. Exception Testing

Use PHPUnit's native Exception expectations to verify that your classes throw errors appropriately when boundary conditions are violated.

```php
public function testThrowsExceptionOnMissingCsrf() {
    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage('Missing CSRF Token');
    
    $validator->validate(['email' => 'test@test.com']); // Triggers exception
}
```

## 7. Common Mistakes & Maintenance

* **Mistake:** Hitting the database. If your unit test connects to MySQL, it is an Integration Test. Move it to `tests/Integration/`.
* **Mistake:** Over-mocking. If you are mocking the class you are trying to test, your test is invalid. Only mock the *dependencies* injected into the constructor.
* **Maintenance:** When a Service's constructor changes (e.g., adding a new Mailer dependency), update the `setUp()` method of the corresponding `*Test.php` file immediately to prevent cascading suite failures.

# Architecture: phpunit-bridge (Symfony)

## Purpose

Symfony's PHPUnit Bridge extends PHPUnit with time and DNS mocking, deprecation error
handling, and compatibility shims across PHPUnit versions (v7–v12). It allows tests to
detect and assert deprecated code usage, freeze time for date-sensitive tests, and
intercept DNS lookups.

## Directory Structure

```
Attribute/
  DnsSensitive.php              # Attribute: mark a test class as needing DNS mock
  TimeSensitive.php             # Attribute: mark a test class as needing clock mock

DeprecationErrorHandler/
  Configuration.php             # Parses the SYMFONY_DEPRECATIONS_HELPER env var
  Deprecation.php               # Represents a single deprecation with its origin
  DeprecationGroup.php          # Groups deprecations by category (direct, indirect, etc.)
  DeprecationNotice.php         # Formats and reports deprecation notices
DeprecationErrorHandler.php     # Installs set_error_handler() to capture deprecations

Extension/
  EnableClockMockSubscriber.php   # PHPUnit extension event: enables clock mock per test
  RegisterClockMockSubscriber.php # PHPUnit extension event: registers ClockMock namespaces
  RegisterDnsMockSubscriber.php   # PHPUnit extension event: registers DnsMock namespaces

Legacy/
  CommandForV8.php / V9.php       # TextUI\Command shims for PHPUnit 8/9
  ConstraintTraitForV8.php / V9.php  # Constraint polyfills for older PHPUnit versions
  ExpectDeprecationTraitBeforeV8_4.php / ForV8_4.php  # Version-specific deprecation expectations
  PolyfillAssertTrait.php         # Backports newer PHPUnit assert methods to older versions
  SymfonyTestsListenerTrait.php / ForV7.php  # Legacy test listener for PHPUnit 7

Metadata/
  AttributeReader.php             # Reads #[DnsSensitive] and #[TimeSensitive] attributes

ClockMock.php         # Intercepts PHP time functions (time, microtime, sleep, date, etc.)
ClassExistsMock.php   # Intercepts class_exists() / interface_exists() / trait_exists()
DnsMock.php           # Intercepts DNS functions (gethostbyname, dns_get_record, etc.)
ExpectDeprecationTrait.php          # Trait: assert that a test triggers a specific deprecation
ExpectUserDeprecationMessageTrait.php  # Trait: assert E_USER_DEPRECATED messages
SymfonyExtension.php  # PHPUnit extension: installs clock/DNS mocks via event subscribers
SymfonyTestsListener.php  # Legacy listener (pre-extension API)
CoverageListener.php  # Listener: adds default coverage metadata when @covers is absent

bootstrap.php         # Simple autoloader bootstrap for simple-phpunit
bin/simple-phpunit.php  # Script to download and run PHPUnit from a specified version
TextUI/Command.php    # Entry point for the TextUI command (version-dispatches to Legacy/)
```

## Key Design Decisions

### Namespace-Level Function Overriding via eval()

`ClockMock` and `DnsMock` use `eval()` to define namespace-scoped PHP function overrides.
PHP resolves unqualified function calls to the current namespace first; by injecting
functions like `time()` in the test class's namespace, the mock intercepts calls without
requiring tests to use dependency injection or wrappers. This approach requires that
`register($class)` be called before the test runs.

### Legacy/ Directory for PHPUnit Version Shims

The `Legacy/` directory contains version-specific implementations selected at class
definition time (using `version_compare(\PHPUnit\Runner\Version::id(), ...)` and `if/else`
around trait/class definitions). This avoids complex inheritance hierarchies while
maintaining compatibility across PHPUnit v7–v12.

### DeprecationErrorHandler as a Test-Phase Error Handler

`DeprecationErrorHandler` installs a PHP `E_USER_DEPRECATED` / `E_DEPRECATED` error handler
that collects all deprecations during the test run and reports them at the end according
to the `SYMFONY_DEPRECATIONS_HELPER` configuration. This is orthogonal to assertions and
requires no per-test code.

## Extension Points

- **`#[TimeSensitive]`** — annotate test classes to auto-enable `ClockMock`.
- **`#[DnsSensitive]`** — annotate test classes to auto-enable `DnsMock`.
- **`SYMFONY_DEPRECATIONS_HELPER`** env var — configure deprecation thresholds and modes
  (e.g., `max[direct]=0`, `weak`, `disabled`).

## Dependency Flow

```
SymfonyExtension (PHPUnit extension)
  ├─ RegisterClockMockSubscriber → ClockMock::register()
  ├─ EnableClockMockSubscriber  → ClockMock::withClockMock()
  └─ RegisterDnsMockSubscriber  → DnsMock::register()

DeprecationErrorHandler
  ├─ Configuration (parses env var)
  ├─ Deprecation (per notice)
  └─ DeprecationGroup (aggregation + reporting)

TextUI/Command → Legacy/CommandForV8 or CommandForV9 (version dispatch)
```

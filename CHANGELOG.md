# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- GitHub Actions test workflow and CI coverage for PHP 8.4.
- Support for `afterMaking`, `afterCreating`, `recycle`, and many-to-many relationships.
- Documentation covering feature compatibility, useful patterns, persistence semantics, and why this package is separate from Laravel's official factories.

### Fixed

- Cache the resolved parent entity when creating belongs-to relationships.

### Changed

- Exclude development files from published packages.
- Clarify that `make()` persists entities without flushing, while `create()` persists and flushes them.

## [3.3.1] / [3.3.0] - 2026-04-27

### Changed

- Completed the project transfer to the `stemble` organization and updated package dependencies.

Both tags point to the same commit.

## [3.2.0] - 2026-04-24

### Fixed

- Add Laravel 13 to the supported framework versions.

## [3.1.0] - 2026-02-20

### Added

- Support for Laravel 12.

## [3.0.0] - 2026-02-13

### Changed

- Update the supported `laravel-doctrine/orm` version constraint.

## [2.0.1] - 2025-08-20

### Changed

- Remove the committed Composer lock file.

## [2.0.0] - 2025-08-20

### Changed

- `make()` now persists created entities without flushing the entity manager.

## [1.7.6] - 2025-03-07

### Fixed

- Only make objects when given an object instance; do not create them unexpectedly.

## [1.7.5] - 2025-03-07

### Added

- Support for factories in child relationships.
- Persistence of entities held in a `DoctrineCollection`.

## [1.7.4] - 2025-03-07

### Fixed

- Ignore uninitialized entity properties during factory expansion.

## [1.7.3] - 2025-03-07

### Fixed

- Allow factories to provide default attributes by overriding attribute expansion.

## [1.7.2] - 2025-03-07

### Fixed

- Reliably persist entities without depending on Doctrine metadata factory detection.

## [1.7.1] / [1.7.0] - 2025-03-07

### Added

- Allow a factory to be passed to `create()`.

Both tags point to the same commit.

## [1.6.0] - 2024-10-10

### Added

- Allow `for()` relationships to provide parents required by an entity constructor.

## [1.5.0] - 2024-09-26

### Improved

- Improve error messages for missing required constructor attributes.
- Add coverage for creating belongs-to and has-many relationships.

## [1.4.0] - 2024-09-25

### Added

- Support `has` magic methods for creating child relationships.
- Allow attributes and relationship names to be passed to `has` and belongs-to magic methods.

## [1.3.1] - 2024-09-24

### Fixed

- Respect `null` attributes when instantiating entities.

## [1.3.0] - 2024-09-24

### Added

- Instantiate entities through their constructors.

## [1.2.0] - 2024-09-22

### Added

- Support `for()` relationships, including relationship names that differ from the related class name.
- Support belongs-to relationships through factory magic methods.
- Add entity instantiation, persistence, sequences, and has-many relationship coverage.

## [1.1.0] - 2024-09-13

### Added

- Override `for()` to work with Doctrine relationships.

## [1.0.2] - 2024-09-12

### Changed

- Remove unrelated classes and improve the README.

## [1.0.1] - 2024-09-12

### Fixed

- Correct the package namespace.

## [1.0.0] - 2024-09-12

Initial release.

[Unreleased]: https://github.com/stemble/laravel-doctrine-factory/compare/v3.3.1...HEAD
[3.3.1]: https://github.com/stemble/laravel-doctrine-factory/releases/tag/v3.3.1
[3.3.0]: https://github.com/stemble/laravel-doctrine-factory/releases/tag/v3.3.0
[3.2.0]: https://github.com/stemble/laravel-doctrine-factory/releases/tag/v3.2.0
[3.1.0]: https://github.com/stemble/laravel-doctrine-factory/releases/tag/v3.1.0
[3.0.0]: https://github.com/stemble/laravel-doctrine-factory/releases/tag/v3.0.0
[2.0.1]: https://github.com/stemble/laravel-doctrine-factory/releases/tag/v2.0.1
[2.0.0]: https://github.com/stemble/laravel-doctrine-factory/releases/tag/v2.0.0
[1.7.6]: https://github.com/stemble/laravel-doctrine-factory/releases/tag/v1.7.6
[1.7.5]: https://github.com/stemble/laravel-doctrine-factory/releases/tag/v1.7.5
[1.7.4]: https://github.com/stemble/laravel-doctrine-factory/releases/tag/v1.7.4
[1.7.3]: https://github.com/stemble/laravel-doctrine-factory/releases/tag/v1.7.3
[1.7.2]: https://github.com/stemble/laravel-doctrine-factory/releases/tag/v1.7.2
[1.7.1]: https://github.com/stemble/laravel-doctrine-factory/releases/tag/v1.7.1
[1.7.0]: https://github.com/stemble/laravel-doctrine-factory/releases/tag/v1.7.0
[1.6.0]: https://github.com/stemble/laravel-doctrine-factory/releases/tag/v1.6.0
[1.5.0]: https://github.com/stemble/laravel-doctrine-factory/releases/tag/v1.5.0
[1.4.0]: https://github.com/stemble/laravel-doctrine-factory/releases/tag/v1.4.0
[1.3.1]: https://github.com/stemble/laravel-doctrine-factory/releases/tag/v1.3.1
[1.3.0]: https://github.com/stemble/laravel-doctrine-factory/releases/tag/v1.3.0
[1.2.0]: https://github.com/stemble/laravel-doctrine-factory/releases/tag/v1.2.0
[1.1.0]: https://github.com/stemble/laravel-doctrine-factory/releases/tag/v1.1.0
[1.0.2]: https://github.com/stemble/laravel-doctrine-factory/releases/tag/v1.0.2
[1.0.1]: https://github.com/stemble/laravel-doctrine-factory/releases/tag/v1.0.1
[1.0.0]: https://github.com/stemble/laravel-doctrine-factory/releases/tag/v1.0.0

# Contributing

Thanks for helping improve Laravel Doctrine Factory. Contributions are welcome in the form of bug reports,
documentation improvements, tests, and code changes.

## Development Setup

You will need PHP 8.2 or newer and Composer.

```bash
git clone git@github.com:stemble/laravel-doctrine-factory.git
cd laravel-doctrine-factory
composer install
```

The repository uses Orchestra Testbench and an SQLite in-memory database for its test suite. The `workbench/`
directory contains the example Laravel application used by the tests.

## Running Checks

Run the full test suite with:

```bash
composer test
```

Format PHP files with Laravel Pint:

```bash
composer lint
```

If you change the Testbench workbench or package discovery setup, rebuild or prepare the workbench as needed:

```bash
composer clear
composer prepare
composer build
```

Please run relevant checks before opening a pull request. New behavior should include Pest coverage, especially for
entity construction, persistence, and relationships.

## Making Changes

- Keep changes focused and compatible with the Laravel factory API where practical.
- Add or update tests in `tests/` for behavior changes and regressions.
- Use the existing classes and patterns in `src/` rather than introducing parallel abstractions.
- Keep Doctrine-specific behavior explicit, particularly around `persist()`, `flush()`, reflection-based property
  assignment, and relationship synchronization.
- Update `README.md` when a public feature, limitation, or usage pattern changes.
- Follow the existing PHP style and run `composer lint` on changed PHP files.

## Pull Requests

Before submitting a pull request:

1. Explain what changed and why.
2. Include tests for the change or explain why tests are not applicable.
3. Confirm that `composer test` and `composer lint` pass.
4. Keep unrelated refactoring out of the pull request.

Pull requests should target `main`. Maintainers may request changes to improve coverage, API consistency, or support
across the Laravel and Doctrine versions supported by `composer.json`.

## Releasing

Releases are cut by maintainers with:

```bash
composer release
```

The script requires [git-cliff](https://git-cliff.org/) (`brew install git-cliff`) and a clean working copy. It
determines the next semantic version from the conventional commits since the last tag, generates the matching
`CHANGELOG.md` section from those commits, and — after you confirm the diff — describes the working copy commit and
sets the tag. It then prints the `jj git push` commands to publish the commit and tag.

Because the changelog is generated from commit messages, use [Conventional
Commits](https://www.conventionalcommits.org/) (`feat:`, `fix:`, `docs:`, `test:`, `chore:`, `ci:`, and so on). The
commit type determines both the changelog section a change lands in and whether the release is a major, minor, or
patch bump.

## Reporting Issues

Search existing issues before opening a new one. Include the PHP, Laravel, and Doctrine versions involved, a minimal
reproduction, and the exception or failing behavior. Security vulnerabilities should not be reported in a public
issue; use the repository's private security reporting channel instead.

## License

By contributing, you agree that your contributions will be licensed under the project's [MIT License](LICENSE).

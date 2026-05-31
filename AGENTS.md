# Agent Instructions

These instructions apply to the whole repository.

## Project Context

- This is a Symfony 8.0 application on PHP 8.4.
- Production code lives in `src/`, Twig templates in `templates/`, CSS and JavaScript in `assets/`, and tests in `tests/`.
- The living frontend style reference is `templates/styleguide/index.html.twig` with styles in `assets/styles/app.css`.

## General Rules

- Declare strict types in every PHP file: `declare(strict_types=1);`.
- Prefer explicit, typed structures over raw arrays. Use value objects, DTOs, enums, collections, or dedicated classes for domain data. Use arrays only where Symfony, Doctrine, PHPUnit, Twig, or configuration APIs make them the clearest option, and document element types when static analysis needs help.
- Keep code readable and direct. Prefer small named methods and clear control flow over clever abstractions.
- Avoid over-engineering. Add abstractions only when they reduce real duplication or clarify a domain concept already present in the codebase.
- Respect the Symfony framework and apply its core features when they fit the problem. Refrain from custom solutions for behavior Symfony already provides.
- Follow existing Symfony, Doctrine, Twig, and test patterns before introducing new ones.
- Keep commits atomic when committing: one coherent behavior or refactor per commit, with its matching tests and documentation updates.
- Do not mix unrelated cleanup with feature work.

## Static Analysis And Linting

- Keep the configured rulesets passing before handoff.
- Run `composer lint` for the full static analysis suite.
- `composer lint` runs:
  - `composer lint:phpcs`, which uses `phpcs.xml`.
  - `composer lint:phpstan`, which uses `phpstan.neon` at PHPStan max level.
- Respect the PHPCS rules in `phpcs.xml`, including PSR-12, required strict types, no short open tags, no silenced errors, no commented-out code, no forbidden debug/termination functions, and whitespace rules.
- Respect the PHPStan rules in `phpstan.neon`, including complete type hints, callable signatures, missing `@var` type checks, explicit mixed checks, and override attributes.

## Tests

- Treat tests as executable documentation. Prefer clear test names and simple AAA structure over large explanatory comments in production code.
- Use Arrange, Act, Assert in each test. Separate the phases with blank lines when it improves readability.
- Every concrete test class must use PHPUnit coverage metadata. Prefer one narrow `#[CoversClass(...)]` for the class or boundary directly under test.
- Keep `#[CoversClass]` restrictive. Do not mark collaborators as covered just because they are exercised by a workflow; use `#[UsesClass(...)]` for supporting DTOs, entities, services, or value objects when needed.
- For integration tests, cover the controller or primary integration boundary being tested and keep service/domain collaborators out of `#[CoversClass]` unless that test class is intentionally and directly testing them.
- Maintain full test coverage for new and changed behavior.
- Add or update integration tests for controller, persistence, form, service wiring, or user-facing workflow changes.
- Keep unit tests focused on domain and service behavior. Use integration tests when the behavior depends on Symfony, Doctrine, routing, forms, or HTTP.
- Run `composer test` for the normal test suite.
- Run `composer test:cov` when coverage is relevant to the change or before handoff for test-heavy work.

## Frontend

- Keep frontend changes reusable and simple.
- Refer to the styleguide before changing UI: `templates/styleguide/index.html.twig` and `assets/styles/app.css`.
- Update the styleguide whenever a change introduces or materially changes a reusable UI pattern, token, component state, or visual convention.
- Do not introduce rounded corners. Keep border radii at `0`, do not add `rounded-*` utility classes, and preserve the square arcade-style visual language.
- Prefer existing Bootstrap utilities and local classes before adding new CSS.
- Keep components and templates readable. Extract reusable Twig fragments only when reuse is real and immediate.
- Avoid decorative complexity that does not support the task workflow.

## Documentation

- Keep production-code comments scarce and purposeful.
- Prefer documenting behavior through tests, type names, method names, and the styleguide.
- Update `README.md` only when commands, setup, or developer workflow changes.

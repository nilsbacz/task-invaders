# Copilot Instructions

Follow the repository rules in `AGENTS.md`.

Key points:

- Always add `declare(strict_types=1);` to PHP files.
- Prefer typed objects, DTOs, value objects, enums, and collections over raw arrays.
- Keep `composer lint` passing, including PHPCS and PHPStan max-level analysis.
- Respect Symfony conventions and use Symfony core features where applicable instead of custom replacements.
- Keep tests as documentation, use Arrange/Act/Assert, and maintain full coverage.
- Add integration tests for Symfony, Doctrine, forms, controllers, and user-facing workflows.
- Refer to `templates/styleguide/index.html.twig` and `assets/styles/app.css` for frontend work, and update the styleguide when reusable UI changes.
- Do not introduce rounded corners or rounded Bootstrap utilities.
- Favor readability, simple designs, and atomic commits.

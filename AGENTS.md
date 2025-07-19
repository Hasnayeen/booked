# Laravel + Filament Project Guidelines

## Commands
- **Tests**: `php artisan test --parallel` (all tests), `php artisan test --filter TestName` (single test)
- **Lint**: `./vendor/bin/pint` (PHP CS Fixer with Laravel preset)  
- **Type check**: `./vendor/bin/rector --dry-run` (static analysis with Rector)
- **Dev server**: `composer run dev` (starts Laravel, queue, logs, and Vite)
- **Frontend**: `npm run dev` (Vite dev server), `npm run build` (production build)

## Code Style

[code-style.instructions](/.vscode/instructions/code-style.instructions.md)

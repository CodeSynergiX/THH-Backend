# THH-Backend

Backend API and web application for THH, built with Laravel 13, Inertia.js, React 19, and PHP 8.3.

## Quality & Linting Commands

- `composer lint`: Automatically fix PHP code style with Laravel Pint.
- `composer lint:check`: Check PHP code style with Laravel Pint.
- `composer types:check`: Run Larastan / PHPStan static analysis.
- `npm run check`: Check TypeScript and React frontend formatting & linting with Vite Plus (`vp`).
- `npm run check:fix`: Automatically format and fix frontend issues.
- `npm run types:check`: Run TypeScript compiler check (`tsc --noEmit`).
- `composer quality:check`: Run full backend and frontend linting and static analysis suite.
- **Pre-Push Git Hook**: Automatically triggers `composer quality:check` before any `git push` to GitHub.

## Development

```bash
# Install dependencies
composer install
npm install

# Setup environment & database
cp .env.example .env
php artisan key:generate
php artisan migrate

# Start dev server
composer dev
```

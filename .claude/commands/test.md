# Claude Command: Test

Run the appropriate test suite based on what has changed.

## Usage

```
/test
```

## What This Command Does

1. Run `git diff --name-only HEAD` (or `git status`) to identify changed files
2. Determine which test suites apply:
   - **PHP files changed** (`.php`) → run PHPCS lint + PHPUnit
   - **E2E files changed** (`e2e/`, `playwright.config.ts`, `*.spec.ts`) → run `npm run test:e2e`
   - **Both** → run both suites
   - **Neither** → run all suites (safe default)
3. Run each applicable suite and report pass/fail
4. Do NOT proceed with any commit action until all applicable suites pass

## Test Commands

| Suite | Command |
|-------|---------|
| PHP lint (PHPCS) | `./vendor/bin/phpcs --standard=WordPress .` |
| PHP unit tests | `./vendor/bin/phpunit` |
| E2E (all sites) | `npm run test:e2e` |
| E2E (specific site) | `npm run test:<sitename>` (e.g. `npm run test:cannaclear`) |
| E2E with video | `npm run test:e2e:video` |
| Playwright UI mode | `npm run test:e2e:ui` |

## Setup (first time)

```bash
composer install
composer run install-wp-tests  # requires local WP test DB
npm install
```

## Pass/Fail Reporting

After each suite, output one of:
- `✓ PHPCS: passed`
- `✗ PHPCS: failed — N errors, M warnings`
- `✓ PHPUnit: N tests, 0 failures`
- `✗ PHPUnit: N tests, M failures`
- `✓ E2E: N passed`
- `✗ E2E: N failed`

If any suite fails, report the failures and stop — do not commit.

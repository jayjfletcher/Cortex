# Contribution Guide

Thank you for considering contributing to Cortex! Please review the following guidelines before submitting a pull request.

For significant changes, please open an issue first so we can discuss the approach.

## Process

1. Fork the project
2. Create a new branch
3. Code, test, commit, and push
4. Open a pull request detailing your changes

## Guidelines

- Ensure the coding style passes by running `composer lint`.
- Send a coherent commit history, making sure each commit in your pull request is meaningful.
- You may need to [rebase](https://git-scm.com/book/en/v2/Git-Branching-Rebasing) to avoid merge conflicts.
- Please remember that we follow [SemVer](http://semver.org/).

## Setup

Clone your fork, then install the dev dependencies:

```bash
composer install
```

## Dashboard Assets

The dashboard source lives in `resources/js` and `resources/css`; the compiled bundle (`public/app.js`, `public/app.css`) is committed and shipped to consumers via the `cortex-assets` publish tag. After changing dashboard source, rebuild and commit the bundle:

```bash
npm install
npm run build
```

While iterating, run `npm run watch` alongside `composer serve`. The workbench copies package assets at build time, so either re-run `composer serve` after a rebuild, or symlink once so rebuilds are live on refresh:

```bash
ln -sfn "$(pwd)/public" vendor/orchestra/testbench-core/laravel/public/vendor/cortex
```

## TypeScript SDK

The dashboard consumes `@jayi/cortex-sdk` (an npm workspace in `sdk/`), a typed client generated from the API's OpenAPI spec. After changing routes, request rules, or resources, regenerate and rebuild it:

```bash
npm run sdk:build
```

That exports `sdk/openapi.json` via Scramble (`testbench scramble:export`), regenerates `sdk/src/schema.d.ts`, and compiles `sdk/dist/`. Commit the regenerated spec, schema, and dist output, then rebuild the dashboard bundle (`npm run build`) so it picks up the new types.

## Lint

Lint your code:

```bash
composer lint
```

## Tests

Run all tests:

```bash
composer test
```

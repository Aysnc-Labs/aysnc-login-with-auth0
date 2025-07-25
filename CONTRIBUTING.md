# Contributing to this repository

Thank you for your interest in contributing!

> **⚠️ Note:** This plugin repository is **not meant to be developed in isolation**.
All development and testing must happen in the [Aysnc-Labs/wordpress-plugins](https://github.com/Aysnc-Labs/wordpress-plugins) repository, which provides the complete local development environment and test suite.

## Getting Started

To contribute:

1. **Fork and clone** the [main development repo](https://github.com/Aysnc-Labs/wordpress-plugins).
2. Follow the setup instructions in that repository to spin up the development environment
3. Make changes inside the `plugins/<your-plugin-name>` folder.
4. Run the full test suite from the repo root.
5. Commit and open a PR **against this plugin repo**, not the development repo.

## Why this is required

Although this plugin contains its own tests, they depend on infrastructure provided by the main monorepo (e.g. PHPUnit config, bootstrapping, shared utilities, etc.).
Running tests directly in this repo will likely fail or produce incomplete results.

## Run all linters and tests

Please run the following locally before raising a Pull Request:

```bash
npm run lint-test

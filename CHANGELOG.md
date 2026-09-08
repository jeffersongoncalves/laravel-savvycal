# Changelog

All notable changes to this project will be documented in this file.

## 1.0.0 - 2026-09-08

Initial release.

- `SavvyCalClient::me()` for the authenticated user
- Scheduling links: list, get, create, update, delete, duplicate, toggle, slots
- Events: list, get, create, cancel
- Webhooks: list, create, delete
- Config file with `SAVVYCAL_API_KEY` / `SAVVYCAL_TIMEOUT`, falling back to `services.savvycal.token`
- Null-safe: every call returns `null`/`false` on network failure or non-2xx instead of throwing

## [Unreleased]

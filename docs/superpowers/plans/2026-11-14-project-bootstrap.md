# GamestackrTCG Bootstrap Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox syntax for tracking.

**Goal:** Deliver the requested Laravel/React/Filament starter running in Docker through Make.

**Architecture:** Official React starter kit with Fortify authentication; Filament shares the user model and session guard. Dedicated Compose services provide PHP, Vite, queues, PostgreSQL and development mail.

**Tech Stack:** Laravel 13, Filament 5, Inertia, React, TypeScript, shadcn/ui, PHP 8.4, Node 24, PostgreSQL 18.

**Spec:** docs/superpowers/specs/2026-11-14-project-bootstrap-design.md

## Global Constraints

- Run all project tooling in Docker; no host PHP or Composer required.
- Preserve PostgreSQL data across ordinary shutdowns.
- Never grant admin access through public registration or mass assignment.
- Use stable releases and keep Composer/npm lockfiles.
- Work directly in the supplied empty project directory, as authorized.

## Review Focus

- Fresh installation without .env, vendor or node_modules must work.
- Repeated initialization must preserve APP_KEY and existing data.
- Unauthenticated and ordinary registered users must not enter Filament.
- Vite/Wayfinder must work without host PHP and with browser-accessible HMR.
- Tests must never reset the development database.

### Task 1: Starter and reproducible Docker runtime

**Files:** Official Laravel React starter files; docker/php/Dockerfile; compose.yaml; .env.example; Makefile; docker/postgres/init-testing.sql.

**Interfaces:** Produces app service with PHP/Composer/Node, postgres database service, make init/up/down/artisan/composer/npm.

- [x] Download current official starter source and record its commit.
- [x] Configure Docker image with PHP extensions, Composer and Node.
- [x] Configure Compose health checks, persistent PostgreSQL, separate test database, Vite and mail.
- [x] Implement repeatable Make initialization preserving keys and data.
- [x] Run make init; expect healthy application and all dependencies installed.

### Task 2: Restricted Filament administration

**Files:** app/Models/User.php; app/Providers/Filament/AdminPanelProvider.php; database/migrations/*add_is_admin_to_users_table.php; app/Console/Commands/CreateAdmin.php; tests/Feature/AdminAccessTest.php; tests/Feature/CreateAdminTest.php.

**Interfaces:** Consumes Task 1 runtime. Produces canAccessPanel(Panel $panel): bool, default false is_admin, interactive app:create-admin command exposed as make admin.

- [x] Install Filament panel builder and register the admin panel.
- [x] Write and run failing access tests for ordinary users, and admin creation command tests.
- [x] Implement admin flag, restricted access and interactive admin creation without public privilege assignment.
- [x] Run targeted tests, then full existing authentication suite using the separate PostgreSQL test database.

### Task 3: Verification and handoff

**Files:** README.md; Makefile; docs/superpowers/verification.md.

**Interfaces:** Uses all previous commands and documents how to operate them.

- [x] Run PHP formatting/static checks, frontend lint/format/types and production build.
- [x] Run complete test suite against PostgreSQL; expect zero failures.
- [x] Smoke test HTTP pages, registration/login, Filament restriction and Vite.
- [x] Verify repeated init preserves key and data, and stop/start works.
- [x] Review final changes with one fresh reviewer and fix substantive findings.
- [x] Document commands, URLs, exact installed versions and verification results.

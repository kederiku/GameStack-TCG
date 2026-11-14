# Auth Game Marquee Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [x]`) syntax for tracking.

**Goal:** Replace authentication artwork with auto-scrolling tiles of the site's real games.

**Architecture:** Extract the existing public catalogue to a shared support class. A Fortify view helper supplies that data to the shared auth layout. A focused React component owns the visual rows and pause state; CSS handles movement and reduced motion.

**Tech Stack:** Laravel/Fortify, Inertia, React, Tailwind, existing shadcn Button and GameImage.

**Spec:** `docs/superpowers/specs/2026-11-14-auth-game-marquee-design.md`

## Global Constraints

- All seven authentication pages; existing form contracts unchanged.
- Real public games only; reserved slugs excluded; no new dependency.
- Desktop animated panel, mobile form only; reduced-motion static layout.
- Current `codex/immersive-landing` checkout, no push/merge or developer database writes.

## Review Focus

- Very small or empty catalogue must not produce blank loops or fictional games.
- Missing/broken images and long names must remain contained and recognizable.
- Loop seams must have the same spacing and no blank interval.
- Keyboard pause and reduced motion must stop decorative movement.
- Every auth entry point receives games; account pages do not incur catalogue queries.

### Task 1: Shared catalogue and authentication marquee

**Files:** Create `app/Support/PublicGames.php`, `resources/js/components/auth-game-marquee.tsx`, `tests/Feature/Auth/AuthGamesTest.php`; modify `PublicGameController.php`, `FortifyServiceProvider.php`, `auth-immersive-layout.tsx`, `resources/css/app.css`, README and verification notes.

**Interfaces:** `PublicGames::all()` returns a collection of existing PublicGame payloads; `PublicGames::present(Game $game)` returns one payload. `FortifyServiceProvider::authView(string $component, array $props = []): Response` adds lazy `games`. `AuthGameMarquee({games}: {games: PublicGame[]})` owns the right panel.

- [x] Add feature tests for the catalogue on all seven auth routes, sorted images/nullable images/reserved filtering, empty catalogue and account-page omission. Run `docker compose exec -T app php artisan test --compact tests/Feature/Auth/AuthGamesTest.php`; expect missing games failures before implementation.
- [x] Extract catalogue and use the auth view helper in all seven Fortify closures; rerun the new tests plus PublicGameTest; expect pass.
- [x] Build three repeated CSS rows with fixed-width tiles, alternate direction, negative starting offsets and exact half-track loop. Use shadcn pause/resume, existing GameImage, a static one-game tile, empty card motif and reduced-motion static grid. Replace the right aside and remove its obsolete background CSS.
- [x] Browser-check actual images/motion/pause, 320px and desktop layouts, empty/single/broken fixtures, static reduced-motion styles. Keep temporary QA code isolated and remove it afterward.
- [x] Run `docker compose exec -T app composer ci:check` and `docker compose exec -T app npm run build`; expect all checks green. Update README and verification notes, commit, and request one independent final review.

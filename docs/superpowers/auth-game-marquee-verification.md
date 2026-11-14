# Auth game marquee — verification

Date: 2026-11-14. Branch: `codex/immersive-landing`. Scope starts at `3f4ace1`.

## Implementation

The shared authentication layout now displays three continuous horizontal rows of actual public games. The middle row moves in the opposite direction. Each tile contains a game image and name; images retain their aspect ratio. The panel retains its collection caption and remains hidden below the desktop breakpoint.

`PublicGames` centralizes the existing public catalogue query and payload. All seven Fortify views supply the same games lazily; no global middleware/catalogue query was added to account pages. Reserved slugs stay excluded.

The shadcn pause button is keyboard accessible. Hovering rows also pauses them. Reduced-motion CSS replaces the marquee with a static grid. Zero games shows a decorative card, one game a static tile, and small catalogues repeat complete sequences long enough to cover the panel. Missing or failed images fall back to initials. Repeated visual tiles are hidden from assistive technology; names appear once in accessible text.

## Automated checks

- RED: new AuthGamesTest had four expected `Property [games] does not exist` failures; account-page omission already passed.
- GREEN: AuthGamesTest + PublicGameTest: 15 tests, 292 assertions.
- `docker compose exec -T app composer ci:check`: passed formatting/lint, TypeScript, Pint, PHPStan and **116 tests / 718 assertions**.
- `docker compose exec -T app npm run build`: passed, 1.86 seconds.

## Browser checks

- Actual `/login`: all seven configured game image sources loaded successfully. No console warnings/errors and no horizontal overflow (1265px content/client width).
- Three tracks measured normal/reverse/normal movement with a 56-second linear cycle. Each group was 1456px, exactly half of its 2912px track, including trailing spacing.
- Pause stopped all three transforms across separate observations. Enter on resume restarted all three animations.
- Temporary QA fixture: empty catalogue (no tiles/control), single game (one static tile), pair (832px group covering a 580.5px panel), broken image and long names remained contained.
- All seven auth pages at 320px iframe width: form visible, panel hidden, scroll width equals client width. Light theme also checked.
- Reduced-motion styles were tested by loading the actual compiled stylesheet into the isolated QA frame and making its reduced-motion media conditions unconditional: rows hidden, animation `none`, static grid visible, pause hidden. This verifies the CSS branch; the operating-system preference was not changed.
- Screenshot: `gamestackr-auth-carousels.jpg` in the current Codex visualization output folder.

Temporary QA used only the test environment/database on localhost:8001 and did not create users or alter developer data. Its server and fixture are removed after review. No changes to authentication forms/actions, no external dependency, no merge or push.

## Independent review

Pending final review of the completed change.

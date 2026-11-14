# Auth game marquee

## Intent and scope

Replace the right-hand illustration on all seven split authentication pages with the user's chosen CardCodex-style multi-row scrolling tiles. Keep the existing forms, French copy, shadcn/ui and mobile form-only layout. Use every public game and its configured image from GameStackr; never copy reference-site logos or invent games.

## Approach

Use three horizontal CSS marquee rows with alternating directions, rounded slate tiles and faded edges. This matches the supplied screenshot and the continuous motion observed at https://cardcodex.com/. A slide-by-slide carousel would interrupt that motion; a JavaScript carousel dependency is unnecessary here. This implementation choice follows the user's explicit reference rather than introducing another design approval round.

Share the public game's existing presentation/query in `App\Support\PublicGames`. Fortify's view factory supplies `games` lazily only to authentication pages. The public controller uses the same source. Keep reserved-slug filtering and the public data contract unchanged.

## Behavior and constraints

- Desktop: three seamless, slow rows; duplicate visual sequences only to complete the loop. All games appear, with different starting positions per row.
- shadcn pause/resume button; hover pauses the moving tiles. Reduced-motion preference shows a static grid with no animation or pause control.
- Images remain contained, without cropping. Existing GameImage provides initials when an image is absent or fails; each tile also shows the game's name.
- Zero games: quiet decorative card motif and existing collection caption. One game: single static tile. No fabricated content or empty animation.
- Marquee copies are decorative; game names are exposed once to assistive technology. The pause control remains accessible.
- No horizontal page overflow, no form behavior changes, no new dependencies, no developer database mutations.
- Keep work on `codex/immersive-landing`, unmerged and unpushed.

## Verification

Feature tests exercise all seven auth responses, empty catalogue, optional images, ordering, reserved slugs and absence of auth-specific catalogue props on account pages. Browser checks cover real game logos, movement in both directions, pause/resume, narrow screens, image failure, small catalogues and reduced-motion CSS. Run project CI and production build, then one independent code review.

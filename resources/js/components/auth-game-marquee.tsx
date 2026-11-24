import { useLocale } from '@/hooks/use-locale';
import { Layers3, Pause, Play } from 'lucide-react';
import { useState } from 'react';
import { GameImage } from '@/components/games/game-image';
import { Button } from '@/components/ui/button';
import type { PublicGame } from '@/types/game';

function GameTile({ game }: { game: PublicGame }) {
    return (
        <div className="auth-game-tile">
            <GameImage
                game={game}
                className="h-16 w-full rounded-none border-0 bg-transparent p-0 text-3xl text-slate-100"
            />
            <span
                lang={game.nameLanguage ?? ''}
                className="line-clamp-2 text-center text-xs leading-snug [overflow-wrap:anywhere] text-slate-300"
            >
                {game.name}
            </span>
        </div>
    );
}

export function AuthGameMarquee({ games }: { games: PublicGame[] }) {
    const { t } = useLocale();
    const [paused, setPaused] = useState(false);
    // A full group must cover the panel, even with only two or three games.
    const itemCount = games.length * Math.ceil(4 / Math.max(1, games.length));

    return (
        <aside
            aria-label={t('common.auth-game-marquee.the-gamestackr-worlds')}
            className="auth-game-panel sticky top-8 hidden h-[calc(100svh-4rem)] max-h-[56rem] min-h-[48rem] min-w-0 flex-col overflow-hidden rounded-3xl border border-white/10 text-white lg:flex"
        >
            <div className="flex min-h-24 items-center justify-between gap-3 px-7 py-6">
                <p className="text-xs font-medium tracking-[0.18em] text-slate-300 uppercase">
                    {t('common.auth-game-marquee.all-your-tcg-worlds')}
                </p>
                {games.length > 1 && (
                    <Button
                        type="button"
                        variant="outline"
                        size="icon"
                        aria-label={
                            paused
                                ? t('common.auth-game-marquee.resume-scrolling')
                                : t('common.auth-game-marquee.pause-scrolling')
                        }
                        onClick={() => setPaused(!paused)}
                        className="size-10 shrink-0 rounded-full border-white/20 bg-white/5 text-white hover:bg-white/10 hover:text-white focus-visible:ring-white/60 motion-reduce:hidden"
                    >
                        {paused ? <Play /> : <Pause />}
                    </Button>
                )}
            </div>

            {games.length > 0 && (
                <p className="sr-only">
                    {t('common.auth-game-marquee.available-games')}{' '}
                    {games.map((game, index) => (
                        <span key={game.id}>
                            {index > 0 && ', '}
                            <span lang={game.nameLanguage ?? ''}>
                                {game.name}
                            </span>
                        </span>
                    ))}
                    .
                </p>
            )}

            <div className="flex min-h-0 flex-1 flex-col justify-center">
                {games.length > 1 ? (
                    <>
                        <div
                            aria-hidden="true"
                            data-paused={paused}
                            className="auth-game-rows space-y-4 motion-reduce:hidden"
                        >
                            {[0, 1, 2].map((row) => {
                                const offset = Math.floor(
                                    (row * games.length) / 3,
                                );
                                const items = Array.from(
                                    { length: itemCount },
                                    (_, index) =>
                                        games[(index + offset) % games.length],
                                );

                                return (
                                    <div
                                        key={row}
                                        className="auth-game-track"
                                        style={{
                                            animationDuration: `${itemCount * 8}s`,
                                            animationDelay: `${-row * 5 - 3}s`,
                                            animationDirection:
                                                row === 1
                                                    ? 'reverse'
                                                    : 'normal',
                                        }}
                                    >
                                        {[0, 1].map((copy) => (
                                            <div
                                                key={copy}
                                                className="auth-game-group"
                                            >
                                                {items.map((game, index) => (
                                                    <GameTile
                                                        key={`${game.id}-${index}`}
                                                        game={game}
                                                    />
                                                ))}
                                            </div>
                                        ))}
                                    </div>
                                );
                            })}
                        </div>
                        <div
                            aria-hidden="true"
                            className="auth-game-static mx-7 hidden min-h-0 grid-cols-2 gap-4 overflow-y-auto motion-reduce:grid"
                        >
                            {games.map((game) => (
                                <GameTile key={game.id} game={game} />
                            ))}
                        </div>
                    </>
                ) : games.length === 1 ? (
                    <div
                        aria-hidden="true"
                        className="flex justify-center px-7"
                    >
                        <GameTile game={games[0]} />
                    </div>
                ) : (
                    <div
                        aria-hidden="true"
                        className="mx-auto flex h-52 w-40 rotate-[-8deg] items-center justify-center rounded-2xl border border-white/15 bg-white/5 shadow-2xl"
                    >
                        <Layers3
                            className="size-16 text-slate-500"
                            strokeWidth={1}
                        />
                    </div>
                )}
            </div>

            <div className="px-8 pt-8 pb-10 text-center">
                <p className="font-display text-4xl font-extrabold uppercase xl:text-5xl">
                    {t('common.auth-game-marquee.tagline')}
                </p>
                <p className="mt-3 text-sm text-slate-300">
                    {t('common.auth-game-marquee.your-collections-across-tcgs')}
                </p>
            </div>
        </aside>
    );
}

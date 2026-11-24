import type { TranslatedName } from './catalog';

export type PublicGame = TranslatedName & {
    id: number;
    slug: string;
    imageUrl: string | null;
    url: string;
};

export type PublicGamePageProps = {
    games: PublicGame[];
    currentGame: PublicGame | null;
    canRegister: boolean;
};

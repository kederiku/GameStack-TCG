export type PublicGame = {
    id: number;
    name: string;
    slug: string;
    imageUrl: string | null;
    url: string;
};

export type PublicGamePageProps = {
    games: PublicGame[];
    currentGame: PublicGame | null;
    canRegister: boolean;
};

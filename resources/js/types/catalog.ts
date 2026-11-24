export type CatalogLanguage = { code: string; name: string };
export type TranslatedName = {
    name: string;
    nameLanguage: string | null;
    nameFallback: boolean;
};
export type TranslatedDescription = {
    descriptionLanguage: string | null;
    descriptionFallback: boolean;
    description: string | null;
};
export type CatalogName = TranslatedName & TranslatedDescription;
export type CatalogSummary = CatalogName & {
    id: number;
    key: string;
    count: number;
};
export type CatalogSeries = CatalogName & {
    id: number;
    key: string;
    url: string;
};
export type ExpansionType = 'main' | 'special' | 'promo' | 'deck' | 'reprint';
export type PublicationStatus = 'announced' | 'released' | 'cancelled';
export type EditionRelease = {
    id: number;
    market: TranslatedName & { key: string };
    channel: 'general' | 'specialist' | 'prerelease' | 'crowdfunding' | 'event';
    releasedOn: string | null;
    announcedPeriod: string | null;
    announcedPeriodLanguage: string | null;
    announcedPeriodFallback: boolean;
    status: PublicationStatus;
};
export type PublicEdition = {
    id: number;
    name: string;
    language: CatalogLanguage;
    officialCode: string | null;
    imageUrl: string | null;
    status: PublicationStatus;
    releases: EditionRelease[];
};
export type PublicExpansion = CatalogName & {
    id: number;
    key: string;
    type: ExpansionType;
    url: string;
    catalog: Omit<CatalogSummary, 'count'>;
    series: CatalogSeries | null;
    languages: CatalogLanguage[];
    edition: PublicEdition | null;
    firstRelease: (EditionRelease & { language: string }) | null;
};
export type ExpansionDetail = PublicExpansion & {
    editions: PublicEdition[];
    selectedEdition: PublicEdition | null;
    editionFallback: boolean;
    related: PublicExpansion[];
};
export type CatalogFilters = {
    catalog: string;
    language: string;
    type: string;
    sort: string;
    series: string;
    q: string;
};
export type CatalogData = {
    catalogs: CatalogSummary[];
    languages: CatalogLanguage[];
    filters: CatalogFilters;
    total: number;
    expansions: {
        data: PublicExpansion[];
        current_page: number;
        last_page: number;
        total: number;
        from: number | null;
        to: number | null;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
};
export type GameDetails = TranslatedDescription & {
    publisher: string | null;
    startYear: number | null;
    endYear: number | null;
};

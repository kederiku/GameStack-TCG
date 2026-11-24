import { useLocale } from '@/hooks/use-locale';
import type {
    EditionRelease,
    ExpansionType,
    PublicationStatus,
} from '@/types/catalog';

export function useCatalogLabels() {
    const { t } = useLocale();
    const types: Record<ExpansionType, string> = {
        main: t('catalog.use-catalog-labels.main-expansion'),
        special: t('catalog.use-catalog-labels.special-expansion'),
        promo: t('catalog.use-catalog-labels.promo'),
        deck: t('catalog.use-catalog-labels.deck'),
        reprint: t('catalog.use-catalog-labels.reprint'),
    };
    const statuses: Record<PublicationStatus, string> = {
        announced: t('catalog.use-catalog-labels.announced'),
        released: t('catalog.use-catalog-labels.released'),
        cancelled: t('catalog.use-catalog-labels.cancelled'),
    };
    const channels: Record<EditionRelease['channel'], string> = {
        general: t('catalog.use-catalog-labels.general-release'),
        specialist: t('catalog.use-catalog-labels.specialist-stores'),
        prerelease: t('catalog.use-catalog-labels.prerelease'),
        crowdfunding: t('catalog.use-catalog-labels.crowdfunding'),
        event: t('catalog.use-catalog-labels.event'),
    };
    return { types, statuses, channels };
}

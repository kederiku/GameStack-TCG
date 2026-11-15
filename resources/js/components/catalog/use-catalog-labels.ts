import { useLocale } from '@/hooks/use-locale';
import type {
    EditionRelease,
    ExpansionType,
    PublicationStatus,
} from '@/types/catalog';

export function useCatalogLabels() {
    const { t } = useLocale();
    const types: Record<ExpansionType, string> = {
        main: t('Extension principale', 'Main expansion'),
        special: t('Extension spéciale', 'Special expansion'),
        promo: t('Promotion', 'Promo'),
        deck: t('Deck', 'Deck'),
        reprint: t('Réédition', 'Reprint'),
    };
    const statuses: Record<PublicationStatus, string> = {
        announced: t('Annoncée', 'Announced'),
        released: t('Publiée', 'Released'),
        cancelled: t('Annulée', 'Cancelled'),
    };
    const channels: Record<EditionRelease['channel'], string> = {
        general: t('Sortie générale', 'General release'),
        specialist: t('Boutiques spécialisées', 'Specialist stores'),
        prerelease: t('Avant-première', 'Prerelease'),
        crowdfunding: t('Financement participatif', 'Crowdfunding'),
        event: t('Événement', 'Event'),
    };
    return { types, statuses, channels };
}

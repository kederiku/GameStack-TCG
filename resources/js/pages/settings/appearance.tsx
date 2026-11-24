import { useLocale } from '@/hooks/use-locale';
import { Head } from '@inertiajs/react';
import { Palette } from 'lucide-react';
import SettingsSection from '@/components/settings-section';

export default function Appearance() {
    const { t } = useLocale();
    return (
        <>
            <Head title={t('account.appearance.appearance')} />
            <SettingsSection
                id="appearance"
                title={t('account.appearance.the-gamestackr-appearance')}
                description={t('account.appearance.description')}
                icon={Palette}
            >
                <p className="mt-6 border-t border-border pt-5 text-xs leading-6 text-muted-foreground">
                    {t('account.appearance.dark-mode')}
                </p>
            </SettingsSection>
        </>
    );
}
Appearance.layout = {
    breadcrumbs: [
        { title: 'account.appearance.appearance', route: 'appearance.edit' },
    ],
};

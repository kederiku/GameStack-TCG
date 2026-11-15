import { Head } from '@inertiajs/react';
import { Palette } from 'lucide-react';
import SettingsSection from '@/components/settings-section';
import { edit as editAppearance } from '@/routes/appearance';

export default function Appearance() {
    return (
        <>
            <Head title="Apparence" />
            <SettingsSection
                id="appearance"
                title="L’apparence GameStackr"
                description="Un thème sombre commun à tout votre espace."
                icon={Palette}
            >
                <p className="mt-6 border-t border-border pt-5 text-xs leading-6 text-muted-foreground">
                    Le mode sombre est activé sur l’ensemble du site.
                </p>
            </SettingsSection>
        </>
    );
}
Appearance.layout = {
    breadcrumbs: [{ title: 'Apparence', href: editAppearance() }],
};

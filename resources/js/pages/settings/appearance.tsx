import { Head } from '@inertiajs/react';
import { Palette } from 'lucide-react';
import AppearanceTabs from '@/components/appearance-tabs';
import SettingsSection from '@/components/settings-section';
import { edit as editAppearance } from '@/routes/appearance';

export default function Appearance() {
    return (
        <>
            <Head title="Apparence" />
            <SettingsSection
                id="appearance"
                title="Choisissez votre ambiance"
                description="Un thème clair, sombre ou adapté aux réglages de votre appareil."
                icon={Palette}
            >
                <AppearanceTabs preview />
                <p className="mt-6 border-t border-border pt-5 text-xs leading-6 text-muted-foreground">
                    Le thème s’applique immédiatement et reste mémorisé sur ce
                    navigateur. Vous pouvez aussi le changer depuis la barre de
                    navigation.
                </p>
            </SettingsSection>
        </>
    );
}
Appearance.layout = {
    breadcrumbs: [{ title: 'Apparence', href: editAppearance() }],
};

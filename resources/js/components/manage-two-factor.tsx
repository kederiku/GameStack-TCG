import { Form } from '@inertiajs/react';
import { ShieldCheck } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import SettingsSection from '@/components/settings-section';
import { Badge } from '@/components/ui/badge';
import TwoFactorRecoveryCodes from '@/components/two-factor-recovery-codes';
import TwoFactorSetupModal from '@/components/two-factor-setup-modal';
import { Button } from '@/components/ui/button';
import { useTwoFactorAuth } from '@/hooks/use-two-factor-auth';
import { disable, enable } from '@/routes/two-factor';

export type Props = {
    canManageTwoFactor?: boolean;
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
};

export default function ManageTwoFactor(props: Props) {
    const requiresConfirmation = props.requiresConfirmation ?? false;
    const twoFactorEnabled = props.twoFactorEnabled ?? false;

    const {
        qrCodeSvg,
        hasSetupData,
        manualSetupKey,
        clearSetupData,
        clearTwoFactorAuthData,
        fetchSetupData,
        recoveryCodesList,
        fetchRecoveryCodes,
        errors,
    } = useTwoFactorAuth();
    const [showSetupModal, setShowSetupModal] = useState<boolean>(false);
    const prevTwoFactorEnabled = useRef(twoFactorEnabled);
    const setupButtonRef = useRef<HTMLButtonElement>(null);

    useEffect(() => {
        if (prevTwoFactorEnabled.current && !twoFactorEnabled) {
            clearTwoFactorAuthData();
        }

        prevTwoFactorEnabled.current = twoFactorEnabled;
    }, [twoFactorEnabled, clearTwoFactorAuthData]);

    if (!(props.canManageTwoFactor ?? false)) {
        return null;
    }

    return (
        <SettingsSection
            id="two-factor"
            title="Double authentification"
            description="Ajoutez une vérification supplémentaire à la connexion."
            icon={ShieldCheck}
            action={
                <Badge
                    variant="secondary"
                    className={
                        twoFactorEnabled
                            ? 'bg-primary/10 text-brand-highlight'
                            : ''
                    }
                >
                    {twoFactorEnabled ? 'Activée' : 'Non activée'}
                </Badge>
            }
        >
            {twoFactorEnabled ? (
                <div className="flex w-full flex-col items-start justify-start gap-5">
                    <p className="max-w-2xl text-sm leading-6 text-muted-foreground">
                        À chaque connexion, un code généré par votre application
                        d’authentification vous sera demandé.
                    </p>

                    <div className="relative inline">
                        <Form {...disable.form()}>
                            {({ processing }) => (
                                <Button
                                    ref={setupButtonRef}
                                    variant="destructive"
                                    type="submit"
                                    disabled={processing}
                                >
                                    {processing
                                        ? 'Désactivation…'
                                        : 'Désactiver la double authentification'}
                                </Button>
                            )}
                        </Form>
                    </div>

                    <TwoFactorRecoveryCodes
                        recoveryCodesList={recoveryCodesList}
                        fetchRecoveryCodes={fetchRecoveryCodes}
                        errors={errors}
                    />
                </div>
            ) : (
                <div className="flex w-full flex-col items-start justify-start gap-5">
                    <p className="max-w-2xl text-sm leading-6 text-muted-foreground">
                        Protégez votre compte avec un code temporaire généré sur
                        votre téléphone, en complément du mot de passe.
                    </p>

                    <div>
                        {hasSetupData ? (
                            <Button
                                ref={setupButtonRef}
                                onClick={() => setShowSetupModal(true)}
                            >
                                <ShieldCheck />
                                Poursuivre la configuration
                            </Button>
                        ) : (
                            <Form
                                {...enable.form()}
                                onSuccess={() => setShowSetupModal(true)}
                            >
                                {({ processing }) => (
                                    <Button
                                        ref={setupButtonRef}
                                        type="submit"
                                        disabled={processing}
                                    >
                                        {processing
                                            ? 'Activation…'
                                            : 'Activer la double authentification'}
                                    </Button>
                                )}
                            </Form>
                        )}
                    </div>
                </div>
            )}

            <TwoFactorSetupModal
                isOpen={showSetupModal}
                onClose={() => setShowSetupModal(false)}
                onClosed={() => setupButtonRef.current?.focus()}
                requiresConfirmation={requiresConfirmation}
                twoFactorEnabled={twoFactorEnabled}
                qrCodeSvg={qrCodeSvg}
                manualSetupKey={manualSetupKey}
                clearSetupData={clearSetupData}
                fetchSetupData={fetchSetupData}
                errors={errors}
            />
        </SettingsSection>
    );
}

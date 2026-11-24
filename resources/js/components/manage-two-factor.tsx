import { useLocale } from '@/hooks/use-locale';
import { useLocalizedRoutes } from '@/hooks/use-localized-routes';
import { Form } from '@inertiajs/react';
import { ShieldCheck } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import SettingsSection from '@/components/settings-section';
import { Badge } from '@/components/ui/badge';
import TwoFactorRecoveryCodes from '@/components/two-factor-recovery-codes';
import TwoFactorSetupModal from '@/components/two-factor-setup-modal';
import { Button } from '@/components/ui/button';
import { useTwoFactorAuth } from '@/hooks/use-two-factor-auth';

export type Props = {
    canManageTwoFactor?: boolean;
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
};

export default function ManageTwoFactor(props: Props) {
    const { t } = useLocale();
    const localized = useLocalizedRoutes();
    const disable = localized['two-factor.disable'];
    const enable = localized['two-factor.enable'];

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
            title={t('auth.manage-two-factor.twofactor-authentication')}
            description={t('auth.manage-two-factor.description')}
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
                    {twoFactorEnabled
                        ? t('auth.manage-two-factor.enabled')
                        : t('auth.manage-two-factor.not-enabled')}
                </Badge>
            }
        >
            {twoFactorEnabled ? (
                <div className="flex w-full flex-col items-start justify-start gap-5">
                    <p className="max-w-2xl text-sm leading-6 text-muted-foreground">
                        {t('auth.manage-two-factor.enabled-description')}
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
                                        ? t('auth.disabling')
                                        : t(
                                              'auth.manage-two-factor.disable-twofactor-authentication',
                                          )}
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
                        {t('auth.manage-two-factor.disabled-description')}
                    </p>

                    <div>
                        {hasSetupData ? (
                            <Button
                                ref={setupButtonRef}
                                onClick={() => setShowSetupModal(true)}
                            >
                                <ShieldCheck />
                                {t('auth.manage-two-factor.continue-setup')}
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
                                            ? t('auth.enabling')
                                            : t(
                                                  'auth.manage-two-factor.enable-twofactor-authentication',
                                              )}
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

import { useLocale } from '@/hooks/use-locale';
import { useLocalizedRoutes } from '@/hooks/use-localized-routes';
import { router } from '@inertiajs/react';
import { KeyRound } from 'lucide-react';
import SettingsSection from '@/components/settings-section';
import { EmptyState } from '@/components/empty-state';
import { Badge } from '@/components/ui/badge';
import PasskeyItem from '@/components/passkey-item';
import PasskeyRegistration from '@/components/passkey-register';
import type { Passkey } from '@/types/auth';

export type Props = {
    canManagePasskeys?: boolean;
    passkeys?: Passkey[];
};

export default function ManagePasskeys(props: Props) {
    const { t, tChoice } = useLocale();
    const localized = useLocalizedRoutes();
    const destroy = localized['passkey.destroy'];

    const passkeys = props.passkeys ?? [];

    const handleDelete = (id: number, onError: () => void) => {
        router.delete(destroy.url(id), {
            preserveScroll: true,
            onError,
        });
    };

    const handleRegisterSuccess = () => {
        router.reload();
    };

    if (!(props.canManagePasskeys ?? false)) {
        return null;
    }

    return (
        <SettingsSection
            id="passkeys"
            title={t('auth.manage-passkeys.passkeys')}
            description={t('auth.manage-passkeys.description')}
            icon={KeyRound}
            action={
                <Badge variant="secondary">
                    {tChoice('auth.passkeys.count', passkeys.length)}
                </Badge>
            }
        >
            <div className="space-y-5">
                <div className="min-w-0 overflow-hidden rounded-xl border border-border bg-background/50">
                    {passkeys.length > 0 ? (
                        passkeys.map((passkey) => (
                            <PasskeyItem
                                key={passkey.id}
                                passkey={passkey}
                                onDelete={handleDelete}
                            />
                        ))
                    ) : (
                        <EmptyState
                            icon={KeyRound}
                            title={t('auth.manage-passkeys.your-next-passkey')}
                            description={t(
                                'auth.manage-passkeys.empty-description',
                            )}
                            className="border-0 bg-transparent py-8"
                        />
                    )}
                </div>

                <PasskeyRegistration onSuccess={handleRegisterSuccess} />
            </div>
        </SettingsSection>
    );
}

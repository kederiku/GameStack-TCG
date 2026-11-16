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
            title="Clés d’accès"
            description="Connectez-vous avec votre appareil, sans saisir de mot de passe."
            icon={KeyRound}
            action={
                <Badge variant="secondary">
                    {passkeys.length}{' '}
                    {passkeys.length > 1
                        ? 'clés enregistrées'
                        : 'clé enregistrée'}
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
                            title="Votre prochaine clé"
                            description="Ajoutez une clé d’accès pour retrouver votre espace avec votre empreinte, votre visage ou le verrouillage de votre appareil."
                            className="border-0 bg-transparent py-8"
                        />
                    )}
                </div>

                <PasskeyRegistration onSuccess={handleRegisterSuccess} />
            </div>
        </SettingsSection>
    );
}

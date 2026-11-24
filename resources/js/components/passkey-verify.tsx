import { passkeyErrorMessage } from '@/lib/passkey-error';
import { useLocale } from '@/hooks/use-locale';
import { useLocalizedRoutes } from '@/hooks/use-localized-routes';
import { usePasskeyVerify } from '@laravel/passkeys/react';
import { KeyRound } from 'lucide-react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Spinner } from '@/components/ui/spinner';

type Props = {
    routes: {
        options: string;
        submit: string;
    };
    label?: string;
    loadingLabel?: string;
    separator?: string;
};

export default function PasskeyVerify({
    routes,
    label,
    loadingLabel,
    separator,
}: Props) {
    const { t } = useLocale();
    const localized = useLocalizedRoutes();
    const { verify, isLoading, errorInstance, isSupported } = usePasskeyVerify({
        routes,
        onSuccess: (response) => {
            window.location.assign(
                response.redirect ?? localized.dashboard.url(),
            );
        },
    });

    const error = passkeyErrorMessage(errorInstance, t);

    if (!isSupported) {
        return null;
    }

    return (
        <>
            <div className="grid gap-2">
                <Button
                    type="button"
                    variant="outline"
                    className="w-full border-transparent bg-foreground text-background hover:bg-foreground/90 hover:text-background"
                    onClick={verify}
                    disabled={isLoading}
                >
                    {isLoading ? <Spinner /> : <KeyRound className="h-4 w-4" />}
                    {isLoading
                        ? (loadingLabel ?? t('auth.loading'))
                        : (label ?? t('auth.passkey-verify.login'))}
                </Button>
                {error && (
                    <InputError message={error} className="text-center" />
                )}
            </div>

            <div className="relative my-6">
                <div className="absolute inset-0 flex items-center">
                    <Separator className="w-full" />
                </div>
                <div className="relative flex justify-center text-xs uppercase">
                    <span className="bg-card px-2 text-center text-muted-foreground">
                        {separator ?? t('auth.passkey-verify.separator')}
                    </span>
                </div>
            </div>
        </>
    );
}

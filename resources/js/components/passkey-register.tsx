import { passkeyErrorMessage } from '@/lib/passkey-error';
import { useLocale } from '@/hooks/use-locale';
import { useLocalizedRoutes } from '@/hooks/use-localized-routes';
import { usePasskeyRegister } from '@laravel/passkeys/react';
import { Plus } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Props = {
    onSuccess: () => void;
};

export default function PasskeyRegistration({ onSuccess }: Props) {
    const { t } = useLocale();
    const localized = useLocalizedRoutes();
    const [name, setName] = useState(() => {
        const ua = navigator.userAgent;

        const browser = [
            { pattern: /Edg|Edge/, name: 'Edge' },
            { pattern: /OPR|Opera|OPiOS/, name: 'Opera' },
            { pattern: /Firefox|FxiOS/, name: 'Firefox' },
            { pattern: /Chrome|CriOS/, name: 'Chrome' },
            { pattern: /Safari/, name: 'Safari' },
        ].find(({ pattern }) => pattern.test(ua))?.name;

        const os = [
            { pattern: /iPhone/, name: 'iPhone' },
            { pattern: /iPad|Macintosh(?=.*Mobile)/, name: 'iPad' },
            { pattern: /Android/, name: 'Android' },
            { pattern: /Mac/, name: 'Mac' },
            { pattern: /Windows/, name: 'Windows' },
        ].find(({ pattern }) => pattern.test(ua))?.name;

        return (
            [browser, os].filter(Boolean).join(t('auth.passkey-register.on')) ||
            ''
        );
    });

    const [showForm, setShowForm] = useState(false);
    const { register, isLoading, errorInstance, isSupported } =
        usePasskeyRegister({
            routes: {
                options: localized['passkey.registration-options'].url(),
                submit: localized['passkey.store'].url(),
            },
            onSuccess: () => {
                setName('');
                setShowForm(false);
                onSuccess();
            },
        });

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();

        if (!name.trim()) {
            return;
        }

        await register(name);
    };

    const handleCancel = () => {
        setShowForm(false);
        setName('');
    };

    const error = passkeyErrorMessage(errorInstance, t);

    if (!isSupported) {
        return (
            <div className="text-sm text-muted-foreground">
                {t('auth.passkey-register.unsupported')}
            </div>
        );
    }

    if (!showForm) {
        return (
            <Button variant="outline" onClick={() => setShowForm(true)}>
                <Plus className="size-4" aria-hidden="true" />
                {t('auth.passkey-register.add-a-passkey')}
            </Button>
        );
    }

    return (
        <form
            onSubmit={handleSubmit}
            className="space-y-4 rounded-xl border border-border bg-background/50 p-4"
        >
            <div className="grid gap-2">
                <Label htmlFor="passkey-name">
                    {t('auth.passkey-register.passkey-name')}
                </Label>
                <Input
                    id="passkey-name"
                    type="text"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                    placeholder={t('auth.passkey-register.name-placeholder')}
                    className="mt-1 block w-full border-foreground/20"
                    autoFocus
                />
                <p className="text-xs text-muted-foreground">
                    {t('auth.passkey-register.name-help')}
                </p>
            </div>

            {error && <InputError message={error} />}

            <div className="flex flex-wrap gap-2">
                <Button type="submit" disabled={isLoading || !name.trim()}>
                    {isLoading
                        ? t('auth.passkey-register.saving')
                        : t('auth.passkey-register.save-passkey')}
                </Button>
                <Button type="button" variant="ghost" onClick={handleCancel}>
                    {t('auth.passkey-register.cancel')}
                </Button>
            </div>
        </form>
    );
}

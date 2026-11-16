import { useLocalizedRoutes } from '@/hooks/use-localized-routes';
import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import PasskeyVerify from '@/components/passkey-verify';

export default function ConfirmPassword() {
    const localized = useLocalizedRoutes();
    const store = localized['password.confirm.store'];
    const confirmOptions = localized['passkey.confirm-options'];
    const confirmStore = localized['passkey.confirm'];

    return (
        <>
            <Head title="Confirmer votre identité" />

            <PasskeyVerify
                routes={{
                    options: confirmOptions(),
                    submit: confirmStore(),
                }}
                label="Confirmer avec une clé d’accès"
                loadingLabel="Vérification en cours…"
                separator="Ou avec votre mot de passe"
            />

            <Form {...store.form()} resetOnSuccess={['password']}>
                {({ processing, errors }) => (
                    <div className="space-y-6">
                        <div className="grid gap-2">
                            <Label htmlFor="password">Mot de passe</Label>
                            <PasswordInput
                                showLabel="Afficher le mot de passe"
                                hideLabel="Masquer le mot de passe"
                                id="password"
                                aria-invalid={Boolean(errors.password)}
                                name="password"
                                placeholder="Votre mot de passe"
                                autoComplete="current-password"
                                autoFocus
                            />

                            <InputError
                                role="alert"
                                message={errors.password}
                            />
                        </div>

                        <div className="flex items-center">
                            <Button
                                className="w-full"
                                disabled={processing}
                                data-test="confirm-password-button"
                            >
                                {processing && <Spinner />}
                                Confirmer mon identité
                            </Button>
                        </div>
                    </div>
                )}
            </Form>
        </>
    );
}

ConfirmPassword.layout = {
    title: 'Confirmez votre identité',
    description:
        'Pour accéder à cette page, confirmez votre identité avec votre mot de passe ou une clé d’accès.',
};

import { useLocale } from '@/hooks/use-locale';
import { useLocalizedRoutes } from '@/hooks/use-localized-routes';
import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import PasskeyVerify from '@/components/passkey-verify';

export default function ConfirmPassword() {
    const { t } = useLocale();
    const localized = useLocalizedRoutes();
    const store = localized['password.confirm.store'];
    const confirmOptions = localized['passkey.confirm-options'];
    const confirmStore = localized['passkey.confirm'];

    return (
        <>
            <Head title={t('auth.confirm-password.confirm-your-identity')} />

            <PasskeyVerify
                routes={{
                    options: confirmOptions(),
                    submit: confirmStore(),
                }}
                label={t('auth.confirm-password.confirm-with-a-passkey')}
                loadingLabel={t('auth.confirm-password.verifying')}
                separator={t('auth.confirm-password.or-with-your-password')}
            />

            <Form {...store.form()} resetOnSuccess={['password']}>
                {({ processing, errors }) => (
                    <div className="space-y-6">
                        <div className="grid gap-2">
                            <Label htmlFor="password">
                                {t('auth.confirm-password.password')}
                            </Label>
                            <PasswordInput
                                showLabel={t(
                                    'auth.confirm-password.show-password',
                                )}
                                hideLabel={t(
                                    'auth.confirm-password.hide-password',
                                )}
                                id="password"
                                aria-invalid={Boolean(errors.password)}
                                name="password"
                                placeholder={t(
                                    'auth.confirm-password.your-password',
                                )}
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
                                {t('auth.confirm-password.confirm-my-identity')}
                            </Button>
                        </div>
                    </div>
                )}
            </Form>
        </>
    );
}

ConfirmPassword.layout = {
    titleKey: 'auth.confirm-password.title',
    descriptionKey: 'auth.confirm-password.description',
};

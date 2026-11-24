import { useLocale } from '@/hooks/use-locale';
import { useLocalizedRoutes } from '@/hooks/use-localized-routes';
import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import PasskeyVerify from '@/components/passkey-verify';

type Props = {
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
};

export default function Login({
    status,
    canResetPassword,
    canRegister,
}: Props) {
    const { t } = useLocale();
    const localized = useLocalizedRoutes();
    const register = localized['register'];
    const store = localized['login.store'];
    const request = localized['password.request'];
    const loginOptions = localized['passkey.login-options'];
    const loginSubmit = localized['passkey.login'];

    return (
        <>
            <Head title={t('auth.login.log-in')} />

            {status && (
                <div role="status" className="auth-status">
                    {status}
                </div>
            )}

            {loginOptions && loginSubmit && (
                <PasskeyVerify
                    routes={{
                        options: loginOptions.url(),
                        submit: loginSubmit.url(),
                    }}
                    label={t('auth.login.passkey-login')}
                    loadingLabel={t('auth.login.logging-in')}
                    separator={t('auth.login.or-with-your-email')}
                />
            )}

            <Form
                {...store.form()}
                resetOnSuccess={['password']}
                className="flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-6">
                            <div className="grid gap-2">
                                <Label htmlFor="email">
                                    {t('auth.login.email-address')}
                                </Label>
                                <Input
                                    id="email"
                                    aria-invalid={Boolean(errors.email)}
                                    type="email"
                                    name="email"
                                    required
                                    autoFocus
                                    autoComplete="email"
                                    placeholder={t('auth.login.youexamplecom')}
                                />
                                <InputError
                                    role="alert"
                                    message={errors.email}
                                />
                            </div>

                            <div className="grid gap-2">
                                <div className="flex flex-wrap items-center justify-between gap-2">
                                    <Label htmlFor="password">
                                        {t('auth.login.password')}
                                    </Label>
                                    {canResetPassword && (
                                        <TextLink
                                            href={request()}
                                            className="text-xs"
                                        >
                                            {t(
                                                'auth.login.forgot-your-password',
                                            )}
                                        </TextLink>
                                    )}
                                </div>
                                <PasswordInput
                                    showLabel={t('auth.login.show-password')}
                                    hideLabel={t('auth.login.hide-password')}
                                    id="password"
                                    aria-invalid={Boolean(errors.password)}
                                    name="password"
                                    required
                                    autoComplete="current-password"
                                    placeholder={t('auth.login.your-password')}
                                />
                                <InputError
                                    role="alert"
                                    message={errors.password}
                                />
                            </div>

                            <div className="flex items-center space-x-3">
                                <Checkbox id="remember" name="remember" />
                                <Label htmlFor="remember">
                                    {t('auth.login.remember-me')}
                                </Label>
                            </div>

                            <Button
                                type="submit"
                                className="mt-4 w-full"
                                disabled={processing}
                                data-test="login-button"
                            >
                                {processing && <Spinner />}
                                {t('auth.login.submit')}
                            </Button>
                        </div>

                        {canRegister && (
                            <p className="text-center text-sm text-muted-foreground">
                                {t('auth.login.dont-have-an-account')}{' '}
                                <TextLink href={register()}>
                                    {t('auth.login.create-an-account')}
                                </TextLink>
                            </p>
                        )}
                    </>
                )}
            </Form>
        </>
    );
}

Login.layout = {
    titleKey: 'auth.login.log-in',
    descriptionKey: 'auth.login.description',
};

import { useLocale } from '@/hooks/use-locale';
import { useLocalizedRoutes } from '@/hooks/use-localized-routes';
import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

type Props = {
    token: string;
    email: string;
    passwordRules: string;
};

export default function ResetPassword({ token, email, passwordRules }: Props) {
    const { t } = useLocale();
    const localized = useLocalizedRoutes();
    const update = localized['password.update'];

    return (
        <>
            <Head title={t('auth.reset-password.new-password')} />

            <Form
                {...update.form()}
                transform={(data) => ({ ...data, token, email })}
                resetOnSuccess={['password', 'password_confirmation']}
            >
                {({ processing, errors }) => (
                    <div className="grid gap-6">
                        <div className="grid gap-2">
                            <Label htmlFor="email">
                                {t('auth.reset-password.email-address')}
                            </Label>
                            <Input
                                id="email"
                                aria-invalid={Boolean(errors.email)}
                                type="email"
                                name="email"
                                autoComplete="email"
                                value={email}
                                className="mt-1 block w-full"
                                readOnly
                            />
                            <InputError
                                role="alert"
                                message={errors.email}
                                className="mt-2"
                            />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="password">
                                {t('auth.reset-password.new-password')}
                            </Label>
                            <PasswordInput
                                showLabel={t(
                                    'auth.reset-password.show-password',
                                )}
                                hideLabel={t(
                                    'auth.reset-password.hide-password',
                                )}
                                id="password"
                                aria-invalid={Boolean(errors.password)}
                                name="password"
                                autoComplete="new-password"
                                className="mt-1 block w-full"
                                autoFocus
                                placeholder={t(
                                    'auth.reset-password.your-password',
                                )}
                                passwordrules={passwordRules}
                            />
                            <InputError
                                role="alert"
                                message={errors.password}
                            />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="password_confirmation">
                                {t('auth.reset-password.confirm-password')}
                            </Label>
                            <PasswordInput
                                showLabel={t(
                                    'auth.reset-password.show-password',
                                )}
                                hideLabel={t(
                                    'auth.reset-password.hide-password',
                                )}
                                id="password_confirmation"
                                aria-invalid={Boolean(
                                    errors.password_confirmation,
                                )}
                                name="password_confirmation"
                                autoComplete="new-password"
                                className="mt-1 block w-full"
                                placeholder={t(
                                    'auth.reset-password.confirm-password',
                                )}
                                passwordrules={passwordRules}
                            />
                            <InputError
                                role="alert"
                                message={errors.password_confirmation}
                                className="mt-2"
                            />
                        </div>

                        <Button
                            type="submit"
                            className="mt-4 w-full"
                            disabled={processing}
                            data-test="reset-password-button"
                        >
                            {processing && <Spinner />}
                            {t('auth.reset-password.save-password')}
                        </Button>
                    </div>
                )}
            </Form>
        </>
    );
}

ResetPassword.layout = {
    titleKey: 'auth.reset-password.a-new-password',
    descriptionKey: 'auth.reset-password.description',
};

import { useLocale } from '@/hooks/use-locale';
import { useLocalizedRoutes } from '@/hooks/use-localized-routes';
import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

type Props = {
    passwordRules: string;
};

export default function Register({ passwordRules }: Props) {
    const { t } = useLocale();
    const localized = useLocalizedRoutes();
    const login = localized['login'];
    const store = localized['register.store'];

    return (
        <>
            <Head title={t('auth.register.create-an-account')} />
            <Form
                {...store.form()}
                resetOnSuccess={['password', 'password_confirmation']}
                disableWhileProcessing
                className="flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-6">
                            <div className="grid gap-2">
                                <Label htmlFor="name">
                                    {t('auth.register.name')}
                                </Label>
                                <Input
                                    id="name"
                                    aria-invalid={Boolean(errors.name)}
                                    type="text"
                                    required
                                    autoFocus
                                    autoComplete="name"
                                    name="name"
                                    placeholder={t('auth.register.your-name')}
                                />
                                <InputError
                                    role="alert"
                                    message={errors.name}
                                    className="mt-2"
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">
                                    {t('auth.register.email-address')}
                                </Label>
                                <Input
                                    id="email"
                                    aria-invalid={Boolean(errors.email)}
                                    type="email"
                                    required
                                    autoComplete="email"
                                    name="email"
                                    placeholder={t(
                                        'auth.register.youexamplecom',
                                    )}
                                />
                                <InputError
                                    role="alert"
                                    message={errors.email}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password">
                                    {t('auth.register.password')}
                                </Label>
                                <PasswordInput
                                    showLabel={t('auth.register.show-password')}
                                    hideLabel={t('auth.register.hide-password')}
                                    id="password"
                                    aria-invalid={Boolean(errors.password)}
                                    required
                                    autoComplete="new-password"
                                    name="password"
                                    placeholder={t(
                                        'auth.register.your-password',
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
                                    {t('auth.register.confirm-password')}
                                </Label>
                                <PasswordInput
                                    showLabel={t('auth.register.show-password')}
                                    hideLabel={t('auth.register.hide-password')}
                                    id="password_confirmation"
                                    aria-invalid={Boolean(
                                        errors.password_confirmation,
                                    )}
                                    required
                                    autoComplete="new-password"
                                    name="password_confirmation"
                                    placeholder={t(
                                        'auth.register.confirm-password',
                                    )}
                                    passwordrules={passwordRules}
                                />
                                <InputError
                                    role="alert"
                                    message={errors.password_confirmation}
                                />
                            </div>

                            <Button
                                type="submit"
                                className="mt-2 w-full"
                                data-test="register-user-button"
                            >
                                {processing && <Spinner />}
                                {t('auth.register.create-my-account')}
                            </Button>
                        </div>

                        <div className="text-center text-sm text-muted-foreground">
                            {t('auth.register.already-have-an-account')}{' '}
                            <TextLink href={login()}>
                                {t('auth.register.log-in')}
                            </TextLink>
                        </div>
                    </>
                )}
            </Form>
        </>
    );
}

Register.layout = {
    titleKey: 'auth.register.create-your-account',
    descriptionKey: 'auth.register.description',
};

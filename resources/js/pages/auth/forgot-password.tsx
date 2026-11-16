import { useLocale } from '@/hooks/use-locale';
import { useLocalizedRoutes } from '@/hooks/use-localized-routes';
import { Form, Head } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export default function ForgotPassword({ status }: { status?: string }) {
    const { t } = useLocale();
    const localized = useLocalizedRoutes();
    const login = localized['login'];
    const email = localized['password.email'];

    return (
        <>
            <Head title={t('auth.forgot-password.forgot-password')} />

            {status && (
                <div role="status" className="auth-status">
                    {status}
                </div>
            )}

            <div className="space-y-6">
                <Form {...email.form()}>
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="email">
                                    {t('auth.forgot-password.email-address')}
                                </Label>
                                <Input
                                    id="email"
                                    aria-invalid={Boolean(errors.email)}
                                    type="email"
                                    name="email"
                                    autoComplete="email"
                                    required
                                    autoFocus
                                    placeholder={t(
                                        'auth.forgot-password.youexamplecom',
                                    )}
                                />

                                <InputError
                                    role="alert"
                                    message={errors.email}
                                />
                            </div>

                            <div className="my-6 flex items-center justify-start">
                                <Button
                                    className="w-full"
                                    disabled={processing}
                                    data-test="email-password-reset-link-button"
                                >
                                    {processing && (
                                        <LoaderCircle className="h-4 w-4 animate-spin" />
                                    )}
                                    {t('auth.forgot-password.send-link')}
                                </Button>
                            </div>
                        </>
                    )}
                </Form>

                <div className="space-x-1 text-center text-sm text-muted-foreground">
                    <span>{t('auth.forgot-password.return-to')}</span>
                    <TextLink href={login()}>
                        {t('auth.forgot-password.login')}
                    </TextLink>
                </div>
            </div>
        </>
    );
}

ForgotPassword.layout = {
    titleKey: 'auth.forgot-password.forgot-your-password',
    descriptionKey: 'auth.forgot-password.description',
};

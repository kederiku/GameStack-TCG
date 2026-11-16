import { useLocale } from '@/hooks/use-locale';
import { useLocalizedRoutes } from '@/hooks/use-localized-routes';
import { Form, Head } from '@inertiajs/react';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';

export default function VerifyEmail({ status }: { status?: string }) {
    const { t } = useLocale();
    const localized = useLocalizedRoutes();
    const logout = localized['logout'];
    const send = localized['verification.send'];

    return (
        <>
            <Head title={t('auth.verify-email.email-verification')} />

            {status === 'verification-link-sent' && (
                <div role="status" className="auth-status">
                    {t('auth.verify-email.link-sent')}
                </div>
            )}

            <Form {...send.form()} className="space-y-6 text-center">
                {({ processing }) => (
                    <>
                        <Button disabled={processing} className="w-full">
                            {processing && <Spinner />}
                            {t('auth.verify-email.resend-verification-link')}
                        </Button>

                        <TextLink
                            href={logout()}
                            className="mx-auto block text-sm"
                        >
                            {t('auth.verify-email.log-out')}
                        </TextLink>
                    </>
                )}
            </Form>
        </>
    );
}

VerifyEmail.layout = {
    titleKey: 'auth.verify-email.verify-your-email',
    descriptionKey: 'auth.verify-email.description',
};

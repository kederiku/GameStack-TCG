import { Form, Head } from '@inertiajs/react';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

export default function VerifyEmail({ status }: { status?: string }) {
    return (
        <>
            <Head title="Vérification e-mail" />

            {status === 'verification-link-sent' && (
                <div role="status" className="auth-status">
                    Un nouveau lien de vérification a été envoyé à l’adresse
                    e-mail utilisée lors de votre inscription.
                </div>
            )}

            <Form {...send.form()} className="space-y-6 text-center">
                {({ processing }) => (
                    <>
                        <Button disabled={processing} className="w-full">
                            {processing && <Spinner />}
                            Renvoyer le lien de vérification
                        </Button>

                        <TextLink
                            href={logout()}
                            className="mx-auto block text-sm"
                        >
                            Se déconnecter
                        </TextLink>
                    </>
                )}
            </Form>
        </>
    );
}

VerifyEmail.layout = {
    title: 'Vérifiez votre e-mail',
    description:
        'Encore une étape : cliquez sur le lien envoyé par e-mail pour activer votre compte.',
};

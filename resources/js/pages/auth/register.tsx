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
    const localized = useLocalizedRoutes();
    const login = localized['login'];
    const store = localized['register.store'];

    return (
        <>
            <Head title="Créer un compte" />
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
                                <Label htmlFor="name">Nom</Label>
                                <Input
                                    id="name"
                                    aria-invalid={Boolean(errors.name)}
                                    type="text"
                                    required
                                    autoFocus
                                    autoComplete="name"
                                    name="name"
                                    placeholder="Votre nom"
                                />
                                <InputError
                                    role="alert"
                                    message={errors.name}
                                    className="mt-2"
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">Adresse e-mail</Label>
                                <Input
                                    id="email"
                                    aria-invalid={Boolean(errors.email)}
                                    type="email"
                                    required
                                    autoComplete="email"
                                    name="email"
                                    placeholder="vous@exemple.fr"
                                />
                                <InputError
                                    role="alert"
                                    message={errors.email}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password">Mot de passe</Label>
                                <PasswordInput
                                    showLabel="Afficher le mot de passe"
                                    hideLabel="Masquer le mot de passe"
                                    id="password"
                                    aria-invalid={Boolean(errors.password)}
                                    required
                                    autoComplete="new-password"
                                    name="password"
                                    placeholder="Votre mot de passe"
                                    passwordrules={passwordRules}
                                />
                                <InputError
                                    role="alert"
                                    message={errors.password}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password_confirmation">
                                    Confirmer le mot de passe
                                </Label>
                                <PasswordInput
                                    showLabel="Afficher le mot de passe"
                                    hideLabel="Masquer le mot de passe"
                                    id="password_confirmation"
                                    aria-invalid={Boolean(
                                        errors.password_confirmation,
                                    )}
                                    required
                                    autoComplete="new-password"
                                    name="password_confirmation"
                                    placeholder="Confirmer le mot de passe"
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
                                Créer mon compte
                            </Button>
                        </div>

                        <div className="text-center text-sm text-muted-foreground">
                            Déjà un compte ?{' '}
                            <TextLink href={login()}>Se connecter</TextLink>
                        </div>
                    </>
                )}
            </Form>
        </>
    );
}

Register.layout = {
    title: 'Créez votre compte',
    description: 'Rejoignez GameStackr et retrouvez vos univers TCG.',
};

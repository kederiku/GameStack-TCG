import { Form, Head, setLayoutProps } from '@inertiajs/react';
import { REGEXP_ONLY_DIGITS } from 'input-otp';
import { useMemo, useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import { OTP_MAX_LENGTH } from '@/hooks/use-two-factor-auth';
import { store } from '@/routes/two-factor/login';

export default function TwoFactorChallenge() {
    const [showRecoveryInput, setShowRecoveryInput] = useState<boolean>(false);
    const [code, setCode] = useState<string>('');

    const authConfigContent = useMemo<{
        title: string;
        description: string;
        toggleText: string;
    }>(() => {
        if (showRecoveryInput) {
            return {
                title: 'Code de récupération',
                description:
                    'Saisissez l’un des codes de récupération conservés lors de l’activation de la double authentification.',
                toggleText: 'Utiliser un code d’authentification',
            };
        }

        return {
            title: 'Vérification en deux étapes',
            description:
                'Saisissez le code à six chiffres de votre application d’authentification.',
            toggleText: 'Utiliser un code de récupération',
        };
    }, [showRecoveryInput]);

    setLayoutProps({
        title: authConfigContent.title,
        description: authConfigContent.description,
    });

    const toggleRecoveryMode = (clearErrors: () => void): void => {
        setShowRecoveryInput(!showRecoveryInput);
        clearErrors();
        setCode('');
    };

    return (
        <>
            <Head title="Double authentification" />

            <div className="space-y-6">
                <Form
                    {...store.form()}
                    className="space-y-4"
                    resetOnError
                    resetOnSuccess={!showRecoveryInput}
                >
                    {({ errors, processing, clearErrors }) => (
                        <>
                            {showRecoveryInput ? (
                                <>
                                    <Label htmlFor="recovery_code">
                                        Code de récupération
                                    </Label>
                                    <Input
                                        id="recovery_code"
                                        aria-invalid={Boolean(
                                            errors.recovery_code,
                                        )}
                                        name="recovery_code"
                                        type="text"
                                        placeholder="Votre code de récupération"
                                        autoComplete="off"
                                        autoFocus={showRecoveryInput}
                                        required
                                    />
                                    <InputError
                                        role="alert"
                                        message={errors.recovery_code}
                                    />
                                </>
                            ) : (
                                <div className="flex flex-col items-center justify-center space-y-3 text-center">
                                    <Label htmlFor="code">
                                        Code d’authentification
                                    </Label>
                                    <div className="flex w-full items-center justify-center">
                                        <InputOTP
                                            id="code"
                                            aria-invalid={Boolean(errors.code)}
                                            name="code"
                                            maxLength={OTP_MAX_LENGTH}
                                            value={code}
                                            onChange={(value) => setCode(value)}
                                            disabled={processing}
                                            pattern={REGEXP_ONLY_DIGITS}
                                            autoFocus
                                        >
                                            <InputOTPGroup>
                                                {Array.from(
                                                    { length: OTP_MAX_LENGTH },
                                                    (_, index) => (
                                                        <InputOTPSlot
                                                            className="h-12 w-9 text-lg sm:w-11"
                                                            key={index}
                                                            index={index}
                                                        />
                                                    ),
                                                )}
                                            </InputOTPGroup>
                                        </InputOTP>
                                    </div>
                                    <InputError
                                        role="alert"
                                        message={errors.code}
                                    />
                                </div>
                            )}

                            <Button
                                type="submit"
                                className="w-full"
                                disabled={processing}
                            >
                                Continuer
                            </Button>

                            <div className="text-center text-sm text-muted-foreground">
                                <button
                                    type="button"
                                    className="min-h-11 cursor-pointer rounded-sm text-brand-highlight underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-ring"
                                    onClick={() =>
                                        toggleRecoveryMode(clearErrors)
                                    }
                                >
                                    {authConfigContent.toggleText}
                                </button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

import { useLocale } from '@/hooks/use-locale';
import { useLocalizedRoutes } from '@/hooks/use-localized-routes';
import { Form, Head, setLayoutProps } from '@inertiajs/react';
import { REGEXP_ONLY_DIGITS } from 'input-otp';
import { useState } from 'react';
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

export default function TwoFactorChallenge() {
    const { t } = useLocale();
    const localized = useLocalizedRoutes();
    const store = localized['two-factor.login.store'];

    const [showRecoveryInput, setShowRecoveryInput] = useState<boolean>(false);
    const [code, setCode] = useState<string>('');

    setLayoutProps(
        showRecoveryInput
            ? {
                  titleKey: 'auth.two-factor-challenge.recovery-code',
                  descriptionKey:
                      'auth.two-factor-challenge.recovery-description',
              }
            : TwoFactorChallenge.layout,
    );

    const toggleRecoveryMode = (clearErrors: () => void): void => {
        setShowRecoveryInput(!showRecoveryInput);
        clearErrors();
        setCode('');
    };

    return (
        <>
            <Head
                title={t('auth.two-factor-challenge.twofactor-authentication')}
            />

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
                                        {t(
                                            'auth.two-factor-challenge.recovery-code',
                                        )}
                                    </Label>
                                    <Input
                                        id="recovery_code"
                                        aria-invalid={Boolean(
                                            errors.recovery_code,
                                        )}
                                        name="recovery_code"
                                        type="text"
                                        placeholder={t(
                                            'auth.two-factor-challenge.your-recovery-code',
                                        )}
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
                                        {t(
                                            'auth.two-factor-challenge.authentication-code',
                                        )}
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
                                {t('auth.two-factor-challenge.continue')}
                            </Button>

                            <div className="text-center text-sm text-muted-foreground">
                                <button
                                    type="button"
                                    className="min-h-11 cursor-pointer rounded-sm text-brand-highlight underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-ring"
                                    onClick={() =>
                                        toggleRecoveryMode(clearErrors)
                                    }
                                >
                                    {t(
                                        showRecoveryInput
                                            ? 'auth.two-factor-challenge.use-an-authentication-code'
                                            : 'auth.two-factor-challenge.use-a-recovery-code',
                                    )}
                                </button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

TwoFactorChallenge.layout = {
    titleKey: 'auth.two-factor-challenge.twostep-verification',
    descriptionKey: 'auth.two-factor-challenge.code-description',
} as const;

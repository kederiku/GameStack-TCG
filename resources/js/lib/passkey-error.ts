import type { Translate, TranslationKey } from '../i18n/types';

/** Translate the public vendor error identities; server errors are already localized. */
export function passkeyErrorMessage(
    error: Error | null,
    t: Translate<TranslationKey>,
): string | undefined {
    if (!error) return undefined;
    const keys: Partial<Record<string, TranslationKey>> = {
        NotSupportedError: 'auth.passkey-errors.unsupported',
        UserCancelledError: 'auth.passkey-errors.cancelled',
        PasskeyExistsError: 'auth.passkey-errors.exists',
        InvalidDomainError: 'auth.passkey-errors.domain',
    };
    const key = keys[error.name];
    if (key) return t(key);
    if (error.message === 'An unknown error occurred.') {
        return t('auth.passkey-errors.unknown');
    }
    const status = /^Request failed with status (\d+)$/.exec(
        error.message,
    )?.[1];
    return status
        ? t('auth.passkey-errors.request', { status })
        : error.message;
}

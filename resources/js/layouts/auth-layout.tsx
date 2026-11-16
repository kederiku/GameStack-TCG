import { useLocale } from '@/hooks/use-locale';
import type { TranslationKey } from '@/i18n/types';
import AuthLayoutTemplate from '@/layouts/auth/auth-immersive-layout';

export default function AuthLayout({
    title = '',
    titleKey,
    description = '',
    descriptionKey,
    children,
}: {
    title?: string;
    titleKey?: TranslationKey;
    description?: string;
    descriptionKey?: TranslationKey;
    children: React.ReactNode;
}) {
    const { t } = useLocale();
    return (
        <AuthLayoutTemplate
            title={titleKey ? t(titleKey) : title}
            description={descriptionKey ? t(descriptionKey) : description}
        >
            {children}
        </AuthLayoutTemplate>
    );
}

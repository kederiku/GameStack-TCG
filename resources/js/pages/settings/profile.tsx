import { useLocale } from '@/hooks/use-locale';
import { Form, Head, Link, usePage } from '@inertiajs/react';
import { BadgeCheck, Mail, Save, UserRound } from 'lucide-react';
import { useLocalizedRoutes } from '@/hooks/use-localized-routes';
import DeleteUser from '@/components/delete-user';
import InputError from '@/components/input-error';
import SettingsSection from '@/components/settings-section';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useInitials } from '@/hooks/use-initials';
import type { Auth } from '@/types';

export default function Profile({
    mustVerifyEmail,
    status,
}: {
    mustVerifyEmail: boolean;
    status?: string;
}) {
    const { t } = useLocale();
    const routes = useLocalizedRoutes();
    const { auth } = usePage<{ auth: Auth }>().props;
    const getInitials = useInitials();
    return (
        <>
            <Head title={t('account.profile.my-profile')} />
            <div className="brand-surface flex flex-wrap items-center gap-4 rounded-2xl border border-border p-5 sm:p-6">
                <Avatar className="size-14 rounded-2xl border border-primary/20">
                    <AvatarFallback className="rounded-2xl bg-primary/10 text-lg font-semibold text-brand-highlight">
                        {getInitials(auth.user.name)}
                    </AvatarFallback>
                </Avatar>
                <div className="min-w-0 flex-1">
                    <p className="font-semibold [overflow-wrap:anywhere]">
                        {auth.user.name}
                    </p>
                    <p className="mt-1 text-sm [overflow-wrap:anywhere] text-muted-foreground">
                        {auth.user.email}
                    </p>
                </div>
                {auth.user.email_verified_at && (
                    <Badge
                        variant="outline"
                        className="gap-1.5 bg-card/60 text-brand-highlight"
                    >
                        <BadgeCheck aria-hidden="true" />
                        {t('account.profile.email-verified')}
                    </Badge>
                )}
            </div>
            <SettingsSection
                id="profile"
                title={t('account.profile.personal-information')}
                description={t('account.profile.description')}
                icon={UserRound}
            >
                <Form
                    {...routes['profile.update'].form()}
                    options={{ preserveScroll: true }}
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-5 sm:grid-cols-2">
                                <div className="grid content-start gap-2">
                                    <Label htmlFor="name">
                                        {t('account.profile.full-name')}
                                    </Label>
                                    <Input
                                        id="name"
                                        defaultValue={auth.user.name}
                                        name="name"
                                        required
                                        autoComplete="name"
                                        placeholder={t(
                                            'account.profile.your-name',
                                        )}
                                        aria-invalid={!!errors.name}
                                        aria-describedby={
                                            errors.name
                                                ? 'name-error'
                                                : undefined
                                        }
                                    />
                                    <InputError
                                        id="name-error"
                                        message={errors.name}
                                    />
                                </div>
                                <div className="grid content-start gap-2">
                                    <Label htmlFor="email">
                                        {t('account.profile.email-address')}
                                    </Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        defaultValue={auth.user.email}
                                        name="email"
                                        required
                                        autoComplete="username"
                                        placeholder={t(
                                            'account.profile.youexamplecom',
                                        )}
                                        aria-invalid={!!errors.email}
                                        aria-describedby={
                                            errors.email
                                                ? 'email-error'
                                                : undefined
                                        }
                                    />
                                    <InputError
                                        id="email-error"
                                        message={errors.email}
                                    />
                                </div>
                            </div>
                            {mustVerifyEmail &&
                                auth.user.email_verified_at === null && (
                                    <div className="flex items-start gap-3 rounded-xl border border-border bg-background p-4 text-sm leading-6">
                                        <Mail
                                            className="mt-1 size-4 shrink-0 text-brand-highlight"
                                            aria-hidden="true"
                                        />
                                        <div>
                                            <p className="text-muted-foreground">
                                                {t(
                                                    'account.profile.email-unverified',
                                                )}
                                            </p>
                                            <Link
                                                href={routes[
                                                    'verification.send'
                                                ]()}
                                                as="button"
                                                className="text-left font-medium text-brand-highlight underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-ring"
                                            >
                                                {t(
                                                    'account.profile.resend-verification-link',
                                                )}
                                            </Link>
                                            {status ===
                                                'verification-link-sent' && (
                                                <p
                                                    role="status"
                                                    className="mt-2"
                                                >
                                                    {t(
                                                        'account.profile.link-sent',
                                                    )}
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                )}
                            <div className="flex flex-wrap items-center justify-between gap-4 border-t border-border pt-5">
                                <p className="text-xs leading-5 text-muted-foreground">
                                    {t('account.profile.account-help')}
                                </p>
                                <Button
                                    disabled={processing}
                                    data-test="update-profile-button"
                                >
                                    {processing ? (
                                        <Spinner />
                                    ) : (
                                        <Save
                                            className="size-4"
                                            aria-hidden="true"
                                        />
                                    )}
                                    {processing
                                        ? t('account.profile.saving')
                                        : t('account.profile.save-changes')}
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </SettingsSection>
            <DeleteUser />
        </>
    );
}
Profile.layout = {
    breadcrumbs: [
        { title: 'account.profile.my-profile', route: 'profile.edit' },
    ],
};

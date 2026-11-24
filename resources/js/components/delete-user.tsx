import { useLocale } from '@/hooks/use-locale';
import { Form } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import { useRef } from 'react';
import { useLocalizedRoutes } from '@/hooks/use-localized-routes';
import SettingsSection from '@/components/settings-section';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';

export default function DeleteUser() {
    const { t } = useLocale();
    const routes = useLocalizedRoutes();
    const passwordInput = useRef<HTMLInputElement>(null);

    return (
        <SettingsSection
            id="delete-account"
            title={t('account.delete-user.delete-my-account')}
            description={t('account.delete-user.description')}
            icon={Trash2}
            destructive
        >
            <div className="space-y-4">
                <div className="space-y-1 text-sm leading-6 text-muted-foreground">
                    <p className="font-medium text-foreground">
                        {t('account.delete-user.an-irreversible-action')}
                    </p>
                    <p className="text-sm">
                        {t('account.delete-user.warning')}
                    </p>
                </div>

                <Dialog>
                    <DialogTrigger asChild>
                        <Button
                            variant="destructive"
                            data-test="delete-user-button"
                        >
                            {t('account.delete-user.delete-my-account')}
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogTitle>
                            {t(
                                'account.delete-user.permanently-delete-your-account',
                            )}
                        </DialogTitle>
                        <DialogDescription>
                            {t('account.delete-user.confirmation')}
                        </DialogDescription>

                        <Form
                            {...routes['profile.destroy'].form()}
                            options={{
                                preserveScroll: true,
                            }}
                            onError={() => passwordInput.current?.focus()}
                            resetOnSuccess
                            className="space-y-6"
                        >
                            {({ resetAndClearErrors, processing, errors }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="password">
                                            {t('account.delete-user.password')}
                                        </Label>

                                        <PasswordInput
                                            id="password"
                                            name="password"
                                            ref={passwordInput}
                                            placeholder={t(
                                                'account.delete-user.your-password',
                                            )}
                                            autoComplete="current-password"
                                        />

                                        <InputError message={errors.password} />
                                    </div>

                                    <DialogFooter className="gap-2">
                                        <DialogClose asChild>
                                            <Button
                                                variant="secondary"
                                                onClick={() =>
                                                    resetAndClearErrors()
                                                }
                                            >
                                                {t(
                                                    'account.delete-user.cancel',
                                                )}
                                            </Button>
                                        </DialogClose>

                                        <Button
                                            variant="destructive"
                                            disabled={processing}
                                            asChild
                                        >
                                            <button
                                                type="submit"
                                                data-test="confirm-delete-user-button"
                                            >
                                                {t(
                                                    'account.delete-user.delete-my-account',
                                                )}
                                            </button>
                                        </Button>
                                    </DialogFooter>
                                </>
                            )}
                        </Form>
                    </DialogContent>
                </Dialog>
            </div>
        </SettingsSection>
    );
}

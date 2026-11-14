import { Form } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import { useRef } from 'react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
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
    const passwordInput = useRef<HTMLInputElement>(null);

    return (
        <SettingsSection
            id="delete-account"
            title="Supprimer mon compte"
            description="La suppression du compte et de ses données est définitive."
            icon={Trash2}
            destructive
        >
            <div className="space-y-4">
                <div className="space-y-1 text-sm leading-6 text-muted-foreground">
                    <p className="font-medium text-foreground">
                        Une action irréversible
                    </p>
                    <p className="text-sm">
                        Assurez-vous de vouloir quitter GameStackr avant de
                        continuer.
                    </p>
                </div>

                <Dialog>
                    <DialogTrigger asChild>
                        <Button
                            variant="destructive"
                            data-test="delete-user-button"
                        >
                            Supprimer mon compte
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogTitle>
                            Supprimer définitivement votre compte ?
                        </DialogTitle>
                        <DialogDescription>
                            Votre compte et toutes ses données seront supprimés.
                            Saisissez votre mot de passe pour confirmer cette
                            action. Vous ne pourrez pas revenir en arrière.
                        </DialogDescription>

                        <Form
                            {...ProfileController.destroy.form()}
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
                                            Mot de passe
                                        </Label>

                                        <PasswordInput
                                            id="password"
                                            name="password"
                                            ref={passwordInput}
                                            placeholder="Votre mot de passe"
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
                                                Annuler
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
                                                Supprimer mon compte
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

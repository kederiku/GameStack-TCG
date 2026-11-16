import { useLocale } from '@/hooks/use-locale';
import { KeyRound, Trash2 } from 'lucide-react';
import { useState } from 'react';
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
import type { Passkey } from '@/types/auth';

type Props = {
    passkey: Passkey;
    onDelete: (id: number, onError: () => void) => void;
};

export default function PasskeyItem({ passkey, onDelete }: Props) {
    const { t } = useLocale();
    const [isDeleting, setIsDeleting] = useState(false);

    const handleDelete = () => {
        setIsDeleting(true);
        onDelete(passkey.id, () => setIsDeleting(false));
    };

    return (
        <div className="flex min-w-0 items-center justify-between gap-3 border-b p-4 last:border-b-0">
            <div className="flex min-w-0 items-start gap-3">
                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-muted">
                    <KeyRound className="h-5 w-5 text-muted-foreground" />
                </div>
                <div className="min-w-0 space-y-1">
                    <div className="flex flex-wrap items-center gap-2">
                        <p className="font-medium tracking-tight [overflow-wrap:anywhere]">
                            {passkey.name}
                        </p>
                        {passkey.authenticator && (
                            <span className="inline-flex max-w-full items-center gap-1 rounded-md bg-muted px-2 py-0.5 text-[11px] font-medium tracking-wide [overflow-wrap:anywhere] text-muted-foreground uppercase ring-1 ring-border ring-inset">
                                {passkey.authenticator}
                            </span>
                        )}
                    </div>
                    <p className="text-sm text-muted-foreground">
                        {t('auth.passkey-item.added')} {passkey.created_at_diff}
                        {passkey.last_used_at_diff && (
                            <>
                                <span className="mx-1 text-muted-foreground/50">
                                    /
                                </span>
                                {t('auth.passkey-item.last-used')}{' '}
                                {passkey.last_used_at_diff}
                            </>
                        )}
                    </p>
                </div>
            </div>

            <Dialog>
                <DialogTrigger asChild>
                    <Button
                        variant="ghost"
                        size="sm"
                        className="shrink-0 text-destructive-foreground hover:bg-destructive/10 hover:text-destructive-foreground"
                    >
                        <Trash2 className="h-4 w-4" />
                        <span className="sr-only">
                            {t('auth.passkey-item.delete-passkey')}{' '}
                            {passkey.name}
                        </span>
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogTitle>
                        {t('auth.passkey-item.delete-title')}
                    </DialogTitle>
                    <DialogDescription>
                        {t('auth.passkeys.delete-confirm', {
                            name: passkey.name,
                        })}
                    </DialogDescription>
                    <DialogFooter className="gap-2">
                        <DialogClose asChild>
                            <Button variant="secondary">
                                {t('auth.passkey-item.cancel')}
                            </Button>
                        </DialogClose>
                        <Button
                            variant="destructive"
                            onClick={handleDelete}
                            disabled={isDeleting}
                        >
                            {isDeleting
                                ? 'Suppression…'
                                : t('auth.passkey-item.delete-title')}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    );
}

import { useLocale } from '@/hooks/use-locale';
import { Loader2Icon } from "lucide-react"

import { cn } from "@/lib/utils"

function Spinner({ className, ...props }: React.ComponentProps<"svg">) {
    const { t } = useLocale();
  return (
    <Loader2Icon
      role="status"
      aria-label={t('common.spinner.loading')}
      className={cn("size-4 animate-spin motion-reduce:animate-none", className)}
      {...props}
    />
  )
}

export { Spinner }

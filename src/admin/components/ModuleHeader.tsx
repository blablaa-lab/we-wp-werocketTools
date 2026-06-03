import { Button } from '@/components/ui/button'
import { Switch } from '@/components/ui/switch'
import { IconDeviceFloppy, IconLoader2 } from '@tabler/icons-react'

interface Props {
  title: string
  description: string
  saving?: boolean
  active?: boolean
  moduleId?: string
  onToggle?: (active: boolean) => void
}

export function ModuleHeader({ title, description, saving, active, onToggle }: Props) {
  return (
    <div className="bg-card rounded-xl border shadow-sm p-6 mb-4">
      <div className="flex items-center justify-between pb-4 border-b border-border">
        <div>
          <h2 className="text-xl font-semibold text-foreground">{title}</h2>
          <p className="text-muted-foreground text-sm mt-1">{description}</p>
        </div>
        <div className="flex items-center gap-4">
          {onToggle !== undefined && (
            <label className="relative inline-flex items-center cursor-pointer">
              <Switch
                checked={!!active}
                onCheckedChange={onToggle}
                aria-label="Activer le module"
              />
              <span className="ml-3 text-sm font-medium text-foreground">Actif</span>
            </label>
          )}
          <Button type="submit" size="sm" disabled={saving} className="gap-2">
            {saving ? <IconLoader2 size={15} className="animate-spin" /> : <IconDeviceFloppy size={15} />}
            {saving ? 'Enregistrement...' : 'Enregistrer'}
          </Button>
        </div>
      </div>
    </div>
  )
}

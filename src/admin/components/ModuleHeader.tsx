import { Button } from '@/components/ui/button'
import { Card, CardAction, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
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
    <Card size="sm" className="mb-2">
      <CardHeader className="items-center">
        <CardTitle className="text-xl">{title}</CardTitle>
        <CardDescription>{description}</CardDescription>
        <CardAction>
          <div className="flex items-center gap-4">
            {onToggle !== undefined && (
              <label className="inline-flex items-center gap-2 cursor-pointer">
                <Switch
                  checked={!!active}
                  onCheckedChange={onToggle}
                  aria-label="Activer le module"
                />
                <span className="text-sm font-medium text-foreground">Actif</span>
              </label>
            )}
            <Button type="submit" disabled={saving} className="gap-2">
              {saving ? <IconLoader2 className="animate-spin" /> : <IconDeviceFloppy />}
              {saving ? 'Enregistrement...' : 'Enregistrer'}
            </Button>
          </div>
        </CardAction>
      </CardHeader>
    </Card>
  )
}

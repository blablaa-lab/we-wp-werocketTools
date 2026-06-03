import { useState } from 'react'
import { toast } from 'sonner'
import { Switch } from '@/components/ui/switch'
import { Button } from '@/components/ui/button'
import { IconSettings } from '@tabler/icons-react'
import { cn } from '@/lib/utils'
import { api } from '@/lib/api'
import type { Module } from '@/lib/types'

interface Props {
  module: Module
  onToggle: (id: string, active: boolean) => void
  onNavigate: (tab: string) => void
}

export function ModuleCard({ module, onToggle, onNavigate }: Props) {
  const [loading, setLoading] = useState(false)

  async function handleToggle(checked: boolean) {
    setLoading(true)
    try {
      await api.post(`/modules/${module.id}/toggle`, { active: checked })
      onToggle(module.id, checked)
      toast.success(checked ? `${module.name} activé` : `${module.name} désactivé`)
    } catch {
      toast.error('Erreur lors de la mise à jour')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="bg-card rounded-xl border shadow-sm p-6 hover:shadow-md transition-shadow flex flex-col gap-4">
      <div className="flex items-start justify-between">
        <div className="flex items-center gap-3">
          <div
            className="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary shrink-0"
            dangerouslySetInnerHTML={{ __html: module.icon }}
          />
          <div>
            <h3 className="text-sm font-semibold text-foreground">{module.name}</h3>
            <span className={cn(
              'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium mt-1',
              module.active
                ? 'bg-primary/15 text-primary'
                : 'bg-muted text-muted-foreground'
            )}>
              {module.active ? 'Actif' : 'Inactif'}
            </span>
          </div>
        </div>
        <Switch
          checked={module.active}
          onCheckedChange={handleToggle}
          disabled={loading}
          aria-label={`Activer ${module.name}`}
        />
      </div>

      <p className="text-sm text-muted-foreground leading-relaxed flex-1">{module.description}</p>

      <Button
        variant="outline"
        size="sm"
        className="w-full gap-2 hover:text-primary hover:border-primary/30"
        onClick={() => onNavigate(module.id)}
      >
        <IconSettings size={15} />
        Configurer
      </Button>
    </div>
  )
}

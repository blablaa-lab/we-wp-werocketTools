import { useState } from 'react'
import { toast } from 'sonner'
import { Card, CardAction, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Switch } from '@/components/ui/switch'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { IconSettings } from '@tabler/icons-react'
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
    <Card className="hover:ring-foreground/10 transition-all">
      <CardHeader>
        <div className="flex items-center gap-3">
          <div
            className="w-11 h-11 rounded-2xl bg-primary/10 flex items-center justify-center text-primary shrink-0 [&_svg]:size-5"
            dangerouslySetInnerHTML={{ __html: module.icon }}
          />
          <CardTitle className="text-base">{module.name}</CardTitle>
        </div>
        <CardAction>
          <Switch
            checked={module.active}
            onCheckedChange={handleToggle}
            disabled={loading}
            aria-label={`Activer ${module.name}`}
          />
        </CardAction>
      </CardHeader>
      <CardContent className="space-y-3">
        <Badge variant={module.active ? 'default' : 'secondary'}>
          <span className={`w-1.5 h-1.5 rounded-full ${module.active ? 'bg-primary-foreground' : 'bg-muted-foreground/60'}`} />
          {module.active ? 'Actif' : 'Inactif'}
        </Badge>
        <CardDescription className="leading-relaxed">{module.description}</CardDescription>
      </CardContent>
      <CardFooter>
        <Button
          variant="outline"
          size="sm"
          className="w-full gap-2"
          onClick={() => onNavigate(module.id)}
        >
          <IconSettings />
          Configurer
        </Button>
      </CardFooter>
    </Card>
  )
}

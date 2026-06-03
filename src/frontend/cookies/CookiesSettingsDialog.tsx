import { useEffect, useState } from 'react'
import { Button } from '@/components/ui/button'
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Label } from '@/components/ui/label'
import { Switch } from '@/components/ui/switch'
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion'
import { type CookiesSettings } from '@/lib/types'
import { getKlaroManager, type KlaroManager } from './klaro-client'

interface Props {
  open: boolean
  onOpenChange: (open: boolean) => void
  config: CookiesSettings
  onConsentSaved: () => void
}

export function CookiesSettingsDialog({ open, onOpenChange, config, onConsentSaved }: Props) {
  const [manager, setManager] = useState<KlaroManager | null>(null)
  const [consents, setConsents] = useState<Record<string, boolean>>({})

  useEffect(() => {
    getKlaroManager().then(m => {
      if (!m) return
      setManager(m)
      const initial: Record<string, boolean> = {}
      config.services.filter(s => s.enabled).forEach(s => {
        initial[s.name] = m.consents[s.name] ?? s.default
      })
      setConsents(initial)
    })
  }, [config.services])

  function setConsent(name: string, value: boolean) {
    setConsents(prev => ({ ...prev, [name]: value }))
  }

  function acceptAll() {
    const all: Record<string, boolean> = {}
    config.services.filter(s => s.enabled).forEach(s => { all[s.name] = true })
    setConsents(all)
  }

  function declineAll() {
    const none: Record<string, boolean> = {}
    config.services.filter(s => s.enabled).forEach(s => {
      none[s.name] = s.required ? true : false
    })
    setConsents(none)
  }

  function saveSelected() {
    if (!manager) return
    config.services.filter(s => s.enabled).forEach(s => {
      manager.updateConsent(s.name, consents[s.name] ?? s.default)
    })
    manager.saveAndApplyConsents()
    onConsentSaved()
    onOpenChange(false)
  }

  const purposes = Object.entries(config.purposes)

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>{config.texts.purposes_title ?? 'Finalités'}</DialogTitle>
        </DialogHeader>

        <Accordion type="multiple" defaultValue={['necessary']} className="space-y-1">
          {purposes.map(([key, purpose]) => {
            const purposeServices = config.services.filter(s => s.enabled && s.purposes.includes(key))
            if (purposeServices.length === 0 && key !== 'necessary') return null
            return (
              <AccordionItem value={key} key={key} className="border rounded-lg px-3">
                <AccordionTrigger className="hover:no-underline py-3">
                  <div className="flex flex-col items-start text-left">
                    <span className="font-medium text-sm">{purpose.title}</span>
                    <span className="text-xs text-muted-foreground font-normal">{purpose.description}</span>
                  </div>
                </AccordionTrigger>
                <AccordionContent className="pb-3 space-y-3">
                  {purposeServices.length === 0 && (
                    <p className="text-xs text-muted-foreground italic">Aucun service configuré.</p>
                  )}
                  {purposeServices.map(svc => (
                    <div key={svc.name} className="flex items-start justify-between gap-4">
                      <div className="flex-1 min-w-0">
                        <Label className="text-sm font-medium cursor-pointer">{svc.title}</Label>
                        {svc.description && (
                          <p className="text-xs text-muted-foreground mt-0.5">{svc.description}</p>
                        )}
                        {svc.required && (
                          <span className="text-xs text-emerald-600 font-medium">Requis</span>
                        )}
                      </div>
                      <Switch
                        checked={consents[svc.name] ?? svc.default}
                        disabled={svc.required}
                        onCheckedChange={v => setConsent(svc.name, v)}
                        className="shrink-0 mt-0.5"
                      />
                    </div>
                  ))}
                </AccordionContent>
              </AccordionItem>
            )
          })}
        </Accordion>

        <DialogFooter className="gap-2 flex-wrap">
          <Button variant="outline" size="sm" onClick={declineAll}>
            {config.texts.decline_all ?? 'Tout refuser'}
          </Button>
          <Button variant="outline" size="sm" onClick={acceptAll}>
            {config.texts.accept_all ?? 'Tout accepter'}
          </Button>
          <Button size="sm" onClick={saveSelected}>
            {config.texts.save ?? 'Enregistrer'}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  )
}

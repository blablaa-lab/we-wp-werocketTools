import { useEffect, useRef, useState } from 'react'
import { Button } from '@/components/ui/button'
import { IconDeviceFloppy, IconLoader2, IconCircleFilled } from '@tabler/icons-react'
import { useSaveContext } from '../context/SaveContext'
import { cn } from '@/lib/utils'

export function GlobalSaveButton() {
  const { formId, saving, isDirty } = useSaveContext()
  const [lastSaved, setLastSaved] = useState<Date | null>(null)
  const prevSaving = useRef(false)

  // Réinitialiser la date quand on change de page de réglages
  useEffect(() => {
    setLastSaved(null)
  }, [formId])

  // Enregistrer l'heure dès que saving passe de true → false
  useEffect(() => {
    if (prevSaving.current && !saving) {
      setLastSaved(new Date())
    }
    prevSaving.current = saving
  }, [saving])

  if (!formId) return null

  const savedText = lastSaved && !isDirty
    ? `Sauvegardé à ${lastSaved.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}`
    : null

  return (
    <div className="flex items-center gap-3">
      {isDirty && !saving && (
        <span className="flex items-center gap-1.5 text-sm text-amber-300/90">
          <IconCircleFilled className="size-2 animate-pulse" />
          Modifications non enregistrées
        </span>
      )}
      {savedText && (
        <span className="text-sm text-white/70">{savedText}</span>
      )}
      <Button
        type="submit"
        form={formId}
        size="lg"
        disabled={saving}
        className={cn(
          'h-11 gap-2 text-[15px] px-5 shadow-md transition-all',
          isDirty && !saving && 'ring-2 ring-amber-300/40 ring-offset-2 ring-offset-background',
        )}
      >
        {saving
          ? <IconLoader2 className="size-[18px] animate-spin" />
          : <IconDeviceFloppy className="size-[18px]" />}
        {saving ? 'Enregistrement...' : 'Enregistrer'}
      </Button>
    </div>
  )
}

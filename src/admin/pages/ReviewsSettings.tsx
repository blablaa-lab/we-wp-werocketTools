import { useEffect, useState } from 'react'
import { useForm } from 'react-hook-form'
import { toast } from 'sonner'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Switch } from '@/components/ui/switch'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import { IconKey, IconEye, IconLoader2, IconLayoutGrid, IconLayoutList, IconCarouselHorizontal } from '@tabler/icons-react'
import { api } from '@/lib/api'
import { cn } from '@/lib/utils'
import { ModuleHeader } from '../components/ModuleHeader'
import { ReviewsPreview } from '../components/ReviewsPreview'
import { TEMPLATE_META } from '@/frontend/reviews/templates'
import type { ReviewsSettings as TReviewsSettings, ReviewTemplate } from '@/lib/types'

const LAYOUT_OPTIONS: { value: string; label: string; Icon: typeof IconLayoutGrid }[] = [
  { value: 'grid', label: 'Grille', Icon: IconLayoutGrid },
  { value: 'list', label: 'Liste', Icon: IconLayoutList },
  { value: 'carousel', label: 'Carrousel', Icon: IconCarouselHorizontal },
]

export function ReviewsSettings() {
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const { register, handleSubmit, setValue, watch, reset } = useForm<TReviewsSettings>()

  useEffect(() => {
    api.get<{ settings: TReviewsSettings }>('/settings/google_reviews')
      .then(data => reset(data.settings))
      .finally(() => setLoading(false))
  }, [reset])

  async function onSubmit(data: TReviewsSettings) {
    setSaving(true)
    try {
      await api.put('/settings/google_reviews', { settings: data })
      toast.success('Paramètres avis Google enregistrés')
    } catch (e) {
      toast.error(e instanceof Error ? e.message : 'Erreur lors de l\'enregistrement')
    } finally {
      setSaving(false)
    }
  }

  if (loading) return <Spinner />

  const currentTemplate = (watch('template') as ReviewTemplate) || 'classic'
  const currentLayout = watch('display_style') || 'grid'

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
      <ModuleHeader
        title="Avis Google"
        description="Affichage des avis Google sur votre site"
        saving={saving}
      />

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2"><IconKey size={16} /> API Google</CardTitle>
            <CardDescription>Clés nécessaires pour récupérer les avis</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <Field label="Place ID Google">
              <Input {...register('google_place_id')} placeholder="ChIJ..." />
            </Field>
            <Field label="Clé API Google Places">
              <Input {...register('google_api_key')} type="password" placeholder="AIza..." />
            </Field>
            <Field label="Durée du cache (secondes)">
              <Input {...register('cache_duration', { valueAsNumber: true })} type="number" min={60} />
            </Field>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2"><IconEye size={16} /> Affichage</CardTitle>
            <CardDescription>Paramètres de rendu des avis</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <Field label="Nombre d'avis">
              <Input {...register('reviews_count', { valueAsNumber: true })} type="number" min={1} max={20} />
            </Field>
            <Field label="Note minimale">
              <Select onValueChange={v => setValue('min_rating', parseInt(v))} value={String(watch('min_rating') ?? 4)}>
                <SelectTrigger><SelectValue /></SelectTrigger>
                <SelectContent>
                  {[1, 2, 3, 4, 5].map(n => (
                    <SelectItem key={n} value={String(n)}>{n} étoile{n > 1 ? 's' : ''} minimum</SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </Field>
            <div className="space-y-3 pt-1">
              <SwitchRow label="Afficher les étoiles" checked={watch('show_rating')} onChange={v => setValue('show_rating', v)} />
              <SwitchRow label="Afficher la date" checked={watch('show_date')} onChange={v => setValue('show_date', v)} />
              <SwitchRow label="Afficher les avatars" checked={watch('show_avatar')} onChange={v => setValue('show_avatar', v)} />
            </div>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Template</CardTitle>
          <CardDescription>Design de chaque carte d'avis</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
            {(Object.entries(TEMPLATE_META) as [ReviewTemplate, typeof TEMPLATE_META[ReviewTemplate]][]).map(([key, meta]) => (
              <button
                type="button"
                key={key}
                onClick={() => setValue('template', key)}
                className={cn(
                  'flex flex-col items-stretch gap-2 p-2.5 rounded-2xl border-2 transition-all text-left',
                  currentTemplate === key
                    ? 'border-primary bg-primary/5 shadow-sm'
                    : 'border-border hover:border-foreground/30 bg-card'
                )}
              >
                <div className="aspect-[4/3] rounded-lg bg-muted/60 flex items-center justify-center p-2 overflow-hidden">
                  {meta.thumbnail}
                </div>
                <div>
                  <div className="text-xs font-semibold text-foreground">{meta.label}</div>
                  <div className="text-[10px] text-muted-foreground leading-tight mt-0.5">{meta.description}</div>
                </div>
              </button>
            ))}
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Disposition</CardTitle>
          <CardDescription>Comment les cartes sont organisées sur la page</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-3 gap-3 max-w-md">
            {LAYOUT_OPTIONS.map(({ value, label, Icon }) => (
              <button
                type="button"
                key={value}
                onClick={() => setValue('display_style', value)}
                className={cn(
                  'flex flex-col items-center justify-center gap-1.5 p-3 rounded-2xl border-2 transition-all',
                  currentLayout === value
                    ? 'border-primary bg-primary/5'
                    : 'border-border hover:border-foreground/30 bg-card'
                )}
              >
                <Icon size={22} className={currentLayout === value ? 'text-primary' : 'text-muted-foreground'} />
                <span className="text-xs font-medium">{label}</span>
              </button>
            ))}
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2"><IconEye size={16} /> Aperçu</CardTitle>
          <CardDescription>Rendu en temps réel avec des avis fictifs</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="rounded-2xl bg-muted/40 p-4 sm:p-6">
            <ReviewsPreview watch={watch} />
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>CSS personnalisé</CardTitle>
        </CardHeader>
        <CardContent>
          <Textarea
            {...register('custom_css')}
            rows={6}
            className="font-mono text-xs"
            placeholder=".werocket-review { ... }"
          />
        </CardContent>
      </Card>

    </form>
  )
}

function Field({ label, children }: { label: string; children: React.ReactNode }) {
  return (
    <div className="space-y-1.5">
      <Label className="text-xs text-muted-foreground">{label}</Label>
      {children}
    </div>
  )
}

function SwitchRow({ label, checked, onChange }: { label: string; checked: boolean; onChange: (v: boolean) => void }) {
  return (
    <div className="flex items-center justify-between">
      <Label className="text-sm">{label}</Label>
      <Switch checked={!!checked} onCheckedChange={onChange} />
    </div>
  )
}

function Spinner() {
  return (
    <div className="flex items-center justify-center py-20 text-muted-foreground gap-2">
      <IconLoader2 size={20} className="animate-spin" /> Chargement...
    </div>
  )
}

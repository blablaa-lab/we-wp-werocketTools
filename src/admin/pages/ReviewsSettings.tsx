import { useEffect, useState } from 'react'
import { useForm } from 'react-hook-form'
import { toast } from 'sonner'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Switch } from '@/components/ui/switch'
import { Button } from '@/components/ui/button'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import {
  IconKey, IconEye, IconLoader2, IconLayoutGrid, IconLayoutList,
  IconCarouselHorizontal, IconExternalLink, IconColumns3, IconArrowsLeftRight,
  IconPlayerPlay, IconRepeat, IconArrowAutofitContent, IconCircles,
} from '@tabler/icons-react'
import { api } from '@/lib/api'
import { cn } from '@/lib/utils'
import { ModuleHeader } from '../components/ModuleHeader'
import { ReviewsPreview } from '../components/ReviewsPreview'
import { TEMPLATE_META } from '@/frontend/reviews/templates'
import type { ReviewsSettings as TReviewsSettings, ReviewTemplate, GridGap } from '@/lib/types'

const PLACE_ID_FINDER_URL = 'https://developers.google.com/maps/documentation/places/web-service/place-id'

const LAYOUT_OPTIONS: { value: string; label: string; Icon: typeof IconLayoutGrid }[] = [
  { value: 'grid', label: 'Grille', Icon: IconLayoutGrid },
  { value: 'list', label: 'Liste', Icon: IconLayoutList },
  { value: 'carousel', label: 'Carrousel', Icon: IconCarouselHorizontal },
]

const GAP_OPTIONS: { value: GridGap; label: string }[] = [
  { value: 'sm', label: 'Compact' },
  { value: 'md', label: 'Normal' },
  { value: 'lg', label: 'Spacieux' },
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
              <div className="flex items-center gap-2">
                <Input {...register('google_place_id')} placeholder="ChIJ..." className="flex-1" />
                <Button type="button" asChild className="shrink-0 gap-1.5 whitespace-nowrap">
                  <a href={PLACE_ID_FINDER_URL} target="_blank" rel="noreferrer noopener">
                    <IconExternalLink size={14} />
                    Trouver mon Place ID
                  </a>
                </Button>
              </div>
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
        <CardContent className="space-y-5">
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

          {currentLayout === 'grid' && <GridOptions watch={watch} setValue={setValue} />}
          {currentLayout === 'list' && <ListOptions watch={watch} setValue={setValue} />}
          {currentLayout === 'carousel' && <CarouselOptions watch={watch} setValue={setValue} register={register} />}
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

/* ─── Sous-sections par layout ─── */

type WatchFn = ReturnType<typeof useForm<TReviewsSettings>>['watch']
type SetValueFn = ReturnType<typeof useForm<TReviewsSettings>>['setValue']
type RegisterFn = ReturnType<typeof useForm<TReviewsSettings>>['register']

function GridOptions({ watch, setValue }: { watch: WatchFn; setValue: SetValueFn }) {
  const cols = Number(watch('grid_columns') ?? 3)
  const gap = (watch('grid_gap') as GridGap) ?? 'md'

  return (
    <div className="pt-4 border-t border-border/60 grid grid-cols-1 md:grid-cols-2 gap-5">
      <Field label="Colonnes (desktop)" icon={<IconColumns3 size={13} />}>
        <div className="flex gap-1.5">
          {[1, 2, 3, 4].map(n => (
            <button
              key={n}
              type="button"
              onClick={() => setValue('grid_columns', n)}
              className={cn(
                'flex-1 h-10 rounded-xl border-2 text-sm font-medium transition-all',
                cols === n
                  ? 'border-primary bg-primary/5 text-primary'
                  : 'border-border hover:border-foreground/30 text-muted-foreground'
              )}
            >
              {n}
            </button>
          ))}
        </div>
      </Field>
      <Field label="Espacement" icon={<IconArrowsLeftRight size={13} />}>
        <div className="flex gap-1.5">
          {GAP_OPTIONS.map(({ value, label }) => (
            <button
              key={value}
              type="button"
              onClick={() => setValue('grid_gap', value)}
              className={cn(
                'flex-1 h-10 rounded-xl border-2 text-xs font-medium transition-all',
                gap === value
                  ? 'border-primary bg-primary/5 text-primary'
                  : 'border-border hover:border-foreground/30 text-muted-foreground'
              )}
            >
              {label}
            </button>
          ))}
        </div>
      </Field>
    </div>
  )
}

function ListOptions({ watch, setValue }: { watch: WatchFn; setValue: SetValueFn }) {
  const gap = (watch('grid_gap') as GridGap) ?? 'md'
  return (
    <div className="pt-4 border-t border-border/60">
      <Field label="Espacement entre avis" icon={<IconArrowsLeftRight size={13} />}>
        <div className="flex gap-1.5 max-w-md">
          {GAP_OPTIONS.map(({ value, label }) => (
            <button
              key={value}
              type="button"
              onClick={() => setValue('grid_gap', value)}
              className={cn(
                'flex-1 h-10 rounded-xl border-2 text-xs font-medium transition-all',
                gap === value
                  ? 'border-primary bg-primary/5 text-primary'
                  : 'border-border hover:border-foreground/30 text-muted-foreground'
              )}
            >
              {label}
            </button>
          ))}
        </div>
      </Field>
    </div>
  )
}

function CarouselOptions({ watch, setValue, register }: { watch: WatchFn; setValue: SetValueFn; register: RegisterFn }) {
  const autoplay = !!watch('carousel_autoplay')

  return (
    <div className="pt-4 border-t border-border/60 grid grid-cols-1 md:grid-cols-2 gap-5">
      <div className="space-y-3">
        <ToggleRow
          label="Défilement automatique"
          icon={<IconPlayerPlay size={14} />}
          checked={autoplay}
          onChange={v => setValue('carousel_autoplay', v)}
        />
        {autoplay && (
          <Field label="Vitesse (secondes)">
            <Input
              {...register('carousel_autoplay_speed', { valueAsNumber: true })}
              type="number"
              min={2}
              max={30}
            />
          </Field>
        )}
        <ToggleRow
          label="Lecture en boucle"
          icon={<IconRepeat size={14} />}
          checked={watch('carousel_loop') !== false}
          onChange={v => setValue('carousel_loop', v)}
        />
      </div>
      <div className="space-y-3">
        <ToggleRow
          label="Flèches de navigation"
          icon={<IconArrowAutofitContent size={14} />}
          checked={watch('carousel_show_arrows') !== false}
          onChange={v => setValue('carousel_show_arrows', v)}
        />
        <ToggleRow
          label="Points indicateurs"
          icon={<IconCircles size={14} />}
          checked={watch('carousel_show_dots') !== false}
          onChange={v => setValue('carousel_show_dots', v)}
        />
      </div>
    </div>
  )
}

/* ─── Atomes ─── */

function Field({ label, children, icon }: { label: string; children: React.ReactNode; icon?: React.ReactNode }) {
  return (
    <div className="space-y-1.5">
      <Label className="text-xs text-muted-foreground flex items-center gap-1.5">
        {icon}
        {label}
      </Label>
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

function ToggleRow({
  label,
  icon,
  checked,
  onChange,
}: {
  label: string
  icon: React.ReactNode
  checked: boolean
  onChange: (v: boolean) => void
}) {
  return (
    <label className="flex items-center justify-between py-2 px-3 rounded-xl border border-border bg-card hover:bg-muted/40 transition-colors cursor-pointer">
      <span className="flex items-center gap-2 text-sm text-foreground">
        <span className="text-muted-foreground">{icon}</span>
        {label}
      </span>
      <Switch checked={!!checked} onCheckedChange={onChange} />
    </label>
  )
}

function Spinner() {
  return (
    <div className="flex items-center justify-center py-20 text-muted-foreground gap-2">
      <IconLoader2 size={20} className="animate-spin" /> Chargement...
    </div>
  )
}

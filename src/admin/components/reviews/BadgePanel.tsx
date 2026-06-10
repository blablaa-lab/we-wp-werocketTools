import { useEffect, useState } from 'react'
import type { UseFormWatch, UseFormSetValue } from 'react-hook-form'
import { Label } from '@/components/ui/label'
import { Switch } from '@/components/ui/switch'
import {
  IconBrandGoogle, IconHash, IconStar, IconMessage2, IconTypography, IconInfoCircle,
} from '@tabler/icons-react'
import { api } from '@/lib/api'
import { RatingBadgeView } from '@/frontend/reviews/RatingBadge'
import { ColorField } from './CustomizationPanel'
import type { ReviewsMeta, ReviewsSettings } from '@/lib/types'

interface Props {
  watch: UseFormWatch<ReviewsSettings>
  setValue: UseFormSetValue<ReviewsSettings>
  /** Incrémenté après une synchro réussie pour recharger la note réelle */
  refreshKey?: number
}

const MOCK_META: ReviewsMeta = { rating: 4.8, total: 127 }

export function BadgePanel({ watch, setValue, refreshKey = 0 }: Props) {
  const [meta, setMeta] = useState<ReviewsMeta | null>(null)

  useEffect(() => {
    api.get<{ meta: ReviewsMeta | null }>('/reviews')
      .then(data => setMeta(data.meta && data.meta.rating ? data.meta : null))
      .catch(() => setMeta(null))
  }, [refreshKey])

  const usingRealData = meta !== null
  const display = meta ?? MOCK_META

  return (
    <div className="grid grid-cols-1 lg:grid-cols-[300px_1fr] gap-6">
      {/* ── Réglages (gauche) ── */}
      <aside className="space-y-5 lg:border-r lg:border-border/60 lg:pr-6">
        <div className="space-y-2">
          <Label className="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider block">
            Éléments affichés
          </Label>
          <ToggleRow
            label="Logo Google"
            icon={<IconBrandGoogle size={14} />}
            checked={watch('badge_show_logo') !== false}
            onChange={v => setValue('badge_show_logo', v, { shouldDirty: true })}
          />
          <ToggleRow
            label="Note"
            icon={<IconHash size={14} />}
            checked={watch('badge_show_rating') !== false}
            onChange={v => setValue('badge_show_rating', v, { shouldDirty: true })}
          />
          <ToggleRow
            label="Étoiles"
            icon={<IconStar size={14} />}
            checked={watch('badge_show_stars') !== false}
            onChange={v => setValue('badge_show_stars', v, { shouldDirty: true })}
          />
          <ToggleRow
            label="Nombre d'avis"
            icon={<IconMessage2 size={14} />}
            checked={watch('badge_show_count') !== false}
            onChange={v => setValue('badge_show_count', v, { shouldDirty: true })}
          />
        </div>

        <div className="space-y-4">
          <Label className="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider block">
            Couleurs
          </Label>
          <ColorField
            label="Note"
            icon={<IconTypography size={13} />}
            fallback="#1F1F1F"
            value={watch('badge_rating_color') ?? ''}
            onChange={v => setValue('badge_rating_color', v, { shouldDirty: true })}
          />
          <ColorField
            label="Étoiles"
            icon={<IconStar size={13} />}
            fallback="#FBBC04"
            value={watch('badge_star_color') ?? ''}
            onChange={v => setValue('badge_star_color', v, { shouldDirty: true })}
          />
          <ColorField
            label="Nombre d'avis"
            icon={<IconMessage2 size={13} />}
            fallback="#5F6368"
            value={watch('badge_count_color') ?? ''}
            onChange={v => setValue('badge_count_color', v, { shouldDirty: true })}
          />
        </div>
      </aside>

      {/* ── Aperçu + doc shortcode (droite) ── */}
      <div className="space-y-4">
        <div className="space-y-2">
          <Label className="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider block">
            Aperçu
          </Label>
          <div className="rounded-2xl bg-muted/40 px-6 py-10 flex items-center justify-center">
            <div className="rounded-2xl bg-white px-8 py-6 shadow-sm ring-1 ring-foreground/5">
              <RatingBadgeView
                rating={display.rating}
                total={display.total}
                showLogo={watch('badge_show_logo') !== false}
                showRating={watch('badge_show_rating') !== false}
                showStars={watch('badge_show_stars') !== false}
                showCount={watch('badge_show_count') !== false}
                ratingColor={watch('badge_rating_color') ?? ''}
                starColor={watch('badge_star_color') ?? ''}
                countColor={watch('badge_count_color') ?? ''}
              />
            </div>
          </div>
          {!usingRealData && (
            <p className="inline-flex items-center gap-1.5 text-[11px] text-muted-foreground">
              <IconInfoCircle size={13} />
              Note fictive — synchronisez vos avis pour afficher la vraie note Google.
            </p>
          )}
        </div>

        <div className="rounded-2xl border border-border bg-muted/30 p-4 space-y-2">
          <p className="text-xs font-semibold text-foreground">Attributs du shortcode</p>
          <p className="text-[11px] text-muted-foreground leading-relaxed">
            Chaque élément peut être forcé via <code className="font-mono bg-background px-1 py-0.5 rounded">true</code> /{' '}
            <code className="font-mono bg-background px-1 py-0.5 rounded">false</code>, sinon les réglages ci-contre s'appliquent :
          </p>
          <code className="block text-[11px] font-mono bg-background rounded-lg px-3 py-2 text-foreground/80">
            [werocket_reviews_badge logo="true" note="true" etoiles="true" avis="false"]
          </code>
        </div>
      </div>
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

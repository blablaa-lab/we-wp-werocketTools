import { IconStarFilled, IconStar, IconQuote } from '@tabler/icons-react'
import { Card, CardContent } from '@/components/ui/card'
import { cn } from '@/lib/utils'
import type { Review, ReviewsSettings, ReviewTemplate } from '@/lib/types'

export interface TemplateProps {
  review: Review
  settings: Partial<ReviewsSettings>
}

export function Stars({ rating, size = 15 }: { rating: number; size?: number }) {
  return (
    <div className="flex gap-0.5">
      {Array.from({ length: 5 }, (_, i) => i + 1).map(n =>
        n <= rating
          ? <IconStarFilled key={n} size={size} className="text-amber-400" />
          : <IconStar key={n} size={size} className="text-muted-foreground/30" />
      )}
    </div>
  )
}

function Avatar({ review }: { review: Review }) {
  if (!review.profile_photo_url) {
    const initial = review.author_name?.charAt(0)?.toUpperCase() ?? '?'
    return (
      <div className="w-9 h-9 rounded-full bg-primary/10 text-primary text-sm font-semibold flex items-center justify-center flex-shrink-0">
        {initial}
      </div>
    )
  }
  return (
    <img
      src={review.profile_photo_url}
      alt={review.author_name}
      className="w-9 h-9 rounded-full object-cover flex-shrink-0"
    />
  )
}

/* ─── Minimal ─── */
export function MinimalCard({ review, settings }: TemplateProps) {
  return (
    <div className="border-b border-border/60 pb-4 last:border-0 last:pb-0">
      {settings.show_rating !== false && (
        <div className="mb-2"><Stars rating={review.rating} size={14} /></div>
      )}
      <p className="text-sm text-foreground/90 leading-relaxed mb-3">{review.text}</p>
      <div className="flex items-center gap-2 text-xs">
        <span className="font-medium text-foreground">{review.author_name}</span>
        {settings.show_date !== false && review.relative_time_description && (
          <>
            <span className="text-muted-foreground/40">·</span>
            <span className="text-muted-foreground">{review.relative_time_description}</span>
          </>
        )}
      </div>
    </div>
  )
}

/* ─── Classic ─── */
export function ClassicCard({ review, settings }: TemplateProps) {
  return (
    <Card>
      <CardContent className="flex flex-col gap-3 pt-5">
        <div className="flex items-center gap-3">
          {settings.show_avatar !== false && <Avatar review={review} />}
          <div className="min-w-0">
            <p className="text-sm font-semibold text-foreground truncate">{review.author_name}</p>
            {settings.show_date !== false && review.relative_time_description && (
              <p className="text-xs text-muted-foreground">{review.relative_time_description}</p>
            )}
          </div>
        </div>
        {settings.show_rating !== false && <Stars rating={review.rating} />}
        <p className="text-sm text-foreground/80 leading-relaxed">{review.text}</p>
      </CardContent>
    </Card>
  )
}

/* ─── Card (premium) ─── */
export function CardCard({ review, settings }: TemplateProps) {
  return (
    <Card className="relative overflow-hidden shadow-md ring-1 ring-primary/10">
      <span
        aria-hidden
        className="absolute -top-4 -left-2 text-[120px] leading-none font-serif text-primary/10 select-none pointer-events-none"
      >
        &ldquo;
      </span>
      <CardContent className="relative flex flex-col gap-3 pt-5">
        {settings.show_rating !== false && <Stars rating={review.rating} />}
        <p className="text-sm text-foreground/85 leading-relaxed">{review.text}</p>
        <div className="flex items-center gap-3 pt-2 mt-auto border-t border-border/40">
          {settings.show_avatar !== false && <Avatar review={review} />}
          <div className="min-w-0">
            <p className="text-sm font-semibold text-foreground truncate">{review.author_name}</p>
            {settings.show_date !== false && review.relative_time_description && (
              <p className="text-xs text-muted-foreground">{review.relative_time_description}</p>
            )}
          </div>
        </div>
      </CardContent>
    </Card>
  )
}

/* ─── Quote (centré) ─── */
export function QuoteCard({ review, settings }: TemplateProps) {
  return (
    <Card>
      <CardContent className="flex flex-col items-center text-center gap-3 pt-6 pb-5">
        <IconQuote size={28} className="text-primary/40" />
        <p className="text-sm text-foreground/85 leading-relaxed italic">&ldquo;{review.text}&rdquo;</p>
        <div className="flex flex-col items-center gap-1.5 mt-1">
          <p className="text-sm font-semibold text-foreground">{review.author_name}</p>
          {settings.show_rating !== false && <Stars rating={review.rating} size={14} />}
          {settings.show_date !== false && review.relative_time_description && (
            <p className="text-xs text-muted-foreground">{review.relative_time_description}</p>
          )}
        </div>
      </CardContent>
    </Card>
  )
}

/* ─── Google branded ─── */
function GoogleLogo({ size = 16 }: { size?: number }) {
  return (
    <svg width={size} height={size} viewBox="0 0 48 48" aria-hidden>
      <path fill="#4285F4" d="M45.12 24.5c0-1.56-.14-3.06-.4-4.5H24v8.51h11.84c-.51 2.75-2.06 5.08-4.39 6.64v5.52h7.11c4.16-3.83 6.56-9.47 6.56-16.17z" />
      <path fill="#34A853" d="M24 46c5.94 0 10.92-1.97 14.56-5.33l-7.11-5.52c-1.97 1.32-4.49 2.1-7.45 2.1-5.73 0-10.58-3.87-12.31-9.07H4.34v5.7C7.96 41.07 15.4 46 24 46z" />
      <path fill="#FBBC05" d="M11.69 28.18C11.25 26.86 11 25.45 11 24s.25-2.86.69-4.18v-5.7H4.34C2.85 17.09 2 20.45 2 24c0 3.55.85 6.91 2.34 9.88l7.35-5.7z" />
      <path fill="#EA4335" d="M24 10.75c3.23 0 6.13 1.11 8.41 3.29l6.31-6.31C34.91 4.18 29.93 2 24 2 15.4 2 7.96 6.93 4.34 14.12l7.35 5.7c1.73-5.2 6.58-9.07 12.31-9.07z" />
    </svg>
  )
}

export function GoogleCard({ review, settings }: TemplateProps) {
  return (
    <Card className="border-l-4" style={{ ['--google-blue' as never]: '#4285F4', borderLeftColor: 'var(--google-blue)' }}>
      <CardContent className="flex flex-col gap-3 pt-5">
        <div className="flex items-center gap-2">
          <GoogleLogo size={18} />
          <span className="text-xs font-medium" style={{ color: 'var(--google-blue)' }}>Avis Google</span>
        </div>
        <div className="flex items-center gap-3">
          {settings.show_avatar !== false && <Avatar review={review} />}
          <div className="min-w-0 flex-1">
            <p className="text-sm font-semibold text-foreground truncate">{review.author_name}</p>
            <div className="flex items-center gap-2 mt-0.5">
              {settings.show_rating !== false && <Stars rating={review.rating} size={13} />}
              {settings.show_date !== false && review.relative_time_description && (
                <span className="text-xs text-muted-foreground">{review.relative_time_description}</span>
              )}
            </div>
          </div>
        </div>
        <p className="text-sm text-foreground/80 leading-relaxed">{review.text}</p>
      </CardContent>
    </Card>
  )
}

/* ─── Registre ─── */
export const TEMPLATES: Record<ReviewTemplate, React.FC<TemplateProps>> = {
  minimal: MinimalCard,
  classic: ClassicCard,
  card: CardCard,
  quote: QuoteCard,
  google: GoogleCard,
}

export interface TemplateMeta {
  label: string
  description: string
  thumbnail: React.ReactNode
}

const ThumbBars = ({ count = 3, className = '' }: { count?: number; className?: string }) => (
  <div className={cn('flex flex-col gap-0.5 w-full', className)}>
    {Array.from({ length: count }).map((_, i) => (
      <div key={i} className="h-1 rounded-full bg-foreground/20" style={{ width: `${[100, 85, 60][i % 3]}%` }} />
    ))}
  </div>
)

const ThumbStars = ({ size = 'h-1.5 w-1.5' }: { size?: string }) => (
  <div className="flex gap-0.5">
    {Array.from({ length: 5 }).map((_, i) => (
      <div key={i} className={cn(size, 'rounded-sm bg-amber-400')} />
    ))}
  </div>
)

const ThumbAvatar = ({ size = 'h-3 w-3' }: { size?: string }) => (
  <div className={cn(size, 'rounded-full bg-primary/40')} />
)

export const TEMPLATE_META: Record<ReviewTemplate, TemplateMeta> = {
  minimal: {
    label: 'Minimal',
    description: 'Texte épuré, séparateurs subtils',
    thumbnail: (
      <div className="flex flex-col gap-1.5 p-2 w-full">
        <ThumbStars />
        <ThumbBars count={2} />
        <div className="h-1 w-1/3 rounded-full bg-foreground/30" />
        <div className="h-px w-full bg-foreground/10 mt-0.5" />
      </div>
    ),
  },
  classic: {
    label: 'Classique',
    description: 'Carte standard avec avatar',
    thumbnail: (
      <div className="flex flex-col gap-1.5 p-2 w-full bg-background rounded-md ring-1 ring-foreground/10">
        <div className="flex items-center gap-1.5">
          <ThumbAvatar />
          <div className="flex flex-col gap-0.5 flex-1">
            <div className="h-1 w-2/3 rounded-full bg-foreground/30" />
            <div className="h-0.5 w-1/3 rounded-full bg-foreground/15" />
          </div>
        </div>
        <ThumbStars />
        <ThumbBars count={2} />
      </div>
    ),
  },
  card: {
    label: 'Premium',
    description: 'Guillemet décoratif, ombrée',
    thumbnail: (
      <div className="relative flex flex-col gap-1.5 p-2 w-full bg-background rounded-md shadow-sm ring-1 ring-primary/20">
        <span className="absolute -top-1 left-0.5 text-2xl leading-none font-serif text-primary/30">&ldquo;</span>
        <div className="ml-1">
          <ThumbStars />
        </div>
        <ThumbBars count={2} />
        <div className="flex items-center gap-1.5 pt-1 border-t border-foreground/10">
          <ThumbAvatar size="h-2.5 w-2.5" />
          <div className="h-1 w-1/2 rounded-full bg-foreground/30" />
        </div>
      </div>
    ),
  },
  quote: {
    label: 'Citation',
    description: 'Centré avec icône',
    thumbnail: (
      <div className="flex flex-col items-center gap-1 p-2 w-full bg-background rounded-md ring-1 ring-foreground/10">
        <IconQuote size={12} className="text-primary/50" />
        <div className="h-1 w-3/4 rounded-full bg-foreground/20" />
        <div className="h-1 w-2/3 rounded-full bg-foreground/15" />
        <div className="h-1 w-1/3 rounded-full bg-foreground/30 mt-0.5" />
        <ThumbStars size="h-1 w-1" />
      </div>
    ),
  },
  google: {
    label: 'Google',
    description: 'Style Google officiel',
    thumbnail: (
      <div
        className="flex flex-col gap-1 p-2 w-full bg-background rounded-md ring-1 ring-foreground/10 border-l-2"
        style={{ borderLeftColor: '#4285F4' }}
      >
        <div className="flex items-center gap-1">
          <GoogleLogo size={10} />
          <div className="h-0.5 w-8 rounded-full" style={{ backgroundColor: '#4285F4' }} />
        </div>
        <div className="flex items-center gap-1">
          <ThumbAvatar size="h-2 w-2" />
          <div className="h-1 w-1/2 rounded-full bg-foreground/30" />
        </div>
        <ThumbStars size="h-1 w-1" />
        <ThumbBars count={2} />
      </div>
    ),
  },
}

/* ─── Helper layout ─── */
export function getWrapClass(displayStyle: string): string {
  switch (displayStyle) {
    case 'list':
      return 'flex flex-col gap-4'
    case 'carousel':
      return 'flex gap-4 overflow-x-auto pb-2 [scroll-snap-type:x_mandatory]'
    default:
      return 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4'
  }
}

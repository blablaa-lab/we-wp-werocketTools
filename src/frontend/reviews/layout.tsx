import { useEffect, useRef, useState, type ReactNode } from 'react'
import { cn } from '@/lib/utils'
import type { ReviewsSettings, GridGap } from '@/lib/types'

const GAP_CLASS: Record<GridGap, string> = {
  sm: 'gap-2',
  md: 'gap-4',
  lg: 'gap-6',
}

const COLS_CLASS: Record<number, string> = {
  1: 'grid-cols-1',
  2: 'grid-cols-1 sm:grid-cols-2',
  3: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
  4: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
}

export interface LayoutProps {
  children: ReactNode[]
  settings: Partial<ReviewsSettings>
}

export function ReviewsLayout({ children, settings }: LayoutProps) {
  const style = settings.display_style || 'grid'

  if (style === 'list') {
    return <div className={cn('flex flex-col', GAP_CLASS[settings.grid_gap ?? 'md'])}>{children}</div>
  }

  if (style === 'carousel') {
    return <Carousel settings={settings}>{children}</Carousel>
  }

  const cols = Math.max(1, Math.min(4, Number(settings.grid_columns ?? 3)))
  const gap = settings.grid_gap ?? 'md'

  return (
    <div className={cn('grid', COLS_CLASS[cols], GAP_CLASS[gap])}>{children}</div>
  )
}

/* ─── Carousel ─── */

function Carousel({ children, settings }: { children: ReactNode[]; settings: Partial<ReviewsSettings> }) {
  const trackRef = useRef<HTMLDivElement>(null)
  const [index, setIndex] = useState(0)
  const [maxIndex, setMaxIndex] = useState(0)
  const [paused, setPaused] = useState(false)

  const autoplay = !!settings.carousel_autoplay
  const speed = Math.max(2, Math.min(30, Number(settings.carousel_autoplay_speed ?? 5)))
  const loop = settings.carousel_loop !== false
  const showArrows = settings.carousel_show_arrows !== false
  const showDots = settings.carousel_show_dots !== false
  const count = children.length

  /* Recompute maxIndex (dernière position scrollable) au resize */
  useEffect(() => {
    const el = trackRef.current
    if (!el) return
    const update = () => {
      const card = el.querySelector<HTMLElement>('[data-carousel-slide]')
      if (!card) return
      const cardWidth = card.offsetWidth + parseFloat(getComputedStyle(el).columnGap || '16')
      const visible = Math.max(1, Math.floor(el.clientWidth / cardWidth))
      setMaxIndex(Math.max(0, count - visible))
    }
    update()
    const ro = new ResizeObserver(update)
    ro.observe(el)
    return () => ro.disconnect()
  }, [count])

  function scrollTo(i: number) {
    const el = trackRef.current
    if (!el) return
    const card = el.querySelector<HTMLElement>('[data-carousel-slide]')
    if (!card) return
    const cardWidth = card.offsetWidth + parseFloat(getComputedStyle(el).columnGap || '16')
    el.scrollTo({ left: i * cardWidth, behavior: 'smooth' })
    setIndex(i)
  }

  function go(delta: number) {
    let next = index + delta
    if (next > maxIndex) next = loop ? 0 : maxIndex
    if (next < 0) next = loop ? maxIndex : 0
    scrollTo(next)
  }

  /* Autoplay */
  useEffect(() => {
    if (!autoplay || paused || count <= 1) return
    const id = setInterval(() => go(1), speed * 1000)
    return () => clearInterval(id)
    /* eslint-disable-next-line react-hooks/exhaustive-deps */
  }, [autoplay, paused, speed, index, maxIndex, loop, count])

  /* Sync index sur scroll manuel */
  function handleScroll() {
    const el = trackRef.current
    if (!el) return
    const card = el.querySelector<HTMLElement>('[data-carousel-slide]')
    if (!card) return
    const cardWidth = card.offsetWidth + parseFloat(getComputedStyle(el).columnGap || '16')
    const i = Math.round(el.scrollLeft / cardWidth)
    if (i !== index) setIndex(i)
  }

  const gap = settings.grid_gap ?? 'md'

  return (
    <div
      className="relative"
      onMouseEnter={() => setPaused(true)}
      onMouseLeave={() => setPaused(false)}
    >
      <div
        ref={trackRef}
        onScroll={handleScroll}
        className={cn(
          'flex overflow-x-auto pb-1 [scroll-snap-type:x_mandatory] [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden',
          GAP_CLASS[gap]
        )}
      >
        {children.map((child, i) => (
          <div
            key={i}
            data-carousel-slide
            className="flex-none w-[85%] sm:w-[48%] lg:w-[32%] [scroll-snap-align:start]"
          >
            {child}
          </div>
        ))}
      </div>

      {showArrows && count > 1 && (
        <>
          <ArrowButton direction="prev" onClick={() => go(-1)} disabled={!loop && index === 0} />
          <ArrowButton direction="next" onClick={() => go(1)} disabled={!loop && index >= maxIndex} />
        </>
      )}

      {showDots && maxIndex > 0 && (
        <div className="flex items-center justify-center gap-1.5 mt-4">
          {Array.from({ length: maxIndex + 1 }).map((_, i) => (
            <button
              type="button"
              key={i}
              onClick={() => scrollTo(i)}
              aria-label={`Aller à la diapositive ${i + 1}`}
              className={cn(
                'transition-all rounded-full',
                i === index
                  ? 'w-6 h-2 bg-foreground/70'
                  : 'w-2 h-2 bg-foreground/20 hover:bg-foreground/40'
              )}
            />
          ))}
        </div>
      )}
    </div>
  )
}

function ArrowButton({
  direction,
  onClick,
  disabled,
}: {
  direction: 'prev' | 'next'
  onClick: () => void
  disabled: boolean
}) {
  return (
    <button
      type="button"
      onClick={onClick}
      disabled={disabled}
      aria-label={direction === 'prev' ? 'Précédent' : 'Suivant'}
      className={cn(
        'absolute top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full bg-background shadow-md ring-1 ring-foreground/10 flex items-center justify-center transition-all',
        'hover:shadow-lg hover:scale-105 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:scale-100',
        direction === 'prev' ? '-left-2 sm:-left-4' : '-right-2 sm:-right-4'
      )}
    >
      <svg width={16} height={16} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2.5} className="text-foreground/70">
        {direction === 'prev'
          ? <path d="M15 18l-6-6 6-6" strokeLinecap="round" strokeLinejoin="round" />
          : <path d="M9 18l6-6-6-6" strokeLinecap="round" strokeLinejoin="round" />}
      </svg>
    </button>
  )
}

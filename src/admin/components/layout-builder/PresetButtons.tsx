import { cn } from '@/lib/utils'

interface PresetOption<T> {
  value: T
  label: string
}

interface Props<T extends string | number> {
  value: T
  onChange: (v: T) => void
  options: PresetOption<T>[]
  className?: string
}

export function PresetButtons<T extends string | number>({ value, onChange, options, className }: Props<T>) {
  return (
    <div className={cn('grid gap-1.5', className)} style={{ gridTemplateColumns: `repeat(${options.length}, minmax(0, 1fr))` }}>
      {options.map(opt => (
        <button
          key={String(opt.value)}
          type="button"
          onClick={() => onChange(opt.value)}
          className={cn(
            'h-9 rounded-lg border-2 text-xs font-medium transition-all',
            value === opt.value
              ? 'border-primary bg-primary/5 text-primary'
              : 'border-border hover:border-foreground/30 text-muted-foreground'
          )}
        >
          {opt.label}
        </button>
      ))}
    </div>
  )
}

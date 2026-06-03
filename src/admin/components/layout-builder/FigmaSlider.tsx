import { Slider } from '@/components/ui/slider'
import { cn } from '@/lib/utils'

interface Props {
  value: number
  onChange: (v: number) => void
  min: number
  max: number
  step?: number
  presets?: number[]
  suffix?: string
}

export function FigmaSlider({ value, onChange, min, max, step = 1, presets, suffix = 'px' }: Props) {
  return (
    <div className="space-y-2">
      <div className="flex items-center gap-3">
        <Slider
          value={[value]}
          min={min}
          max={max}
          step={step}
          onValueChange={([v]) => onChange(v)}
          className="flex-1"
        />
        <div className="shrink-0 inline-flex items-center gap-1 px-2 py-1 rounded-md bg-muted text-xs font-mono tabular-nums min-w-[3.25rem] justify-center">
          <span className="text-foreground font-medium">{value}</span>
          <span className="text-muted-foreground">{suffix}</span>
        </div>
      </div>
      {presets && presets.length > 0 && (
        <div className="flex flex-wrap gap-1">
          {presets.map(preset => (
            <button
              key={preset}
              type="button"
              onClick={() => onChange(preset)}
              className={cn(
                'px-2 py-0.5 text-[11px] font-medium rounded-md transition-colors',
                value === preset
                  ? 'bg-primary/10 text-primary ring-1 ring-primary/40'
                  : 'text-muted-foreground hover:bg-muted hover:text-foreground'
              )}
            >
              {preset}
            </button>
          ))}
        </div>
      )}
    </div>
  )
}

import { IconDeviceDesktop, IconDeviceTablet, IconDeviceMobile } from '@tabler/icons-react'
import { cn } from '@/lib/utils'
import type { Breakpoint } from '@/lib/types'

const ITEMS: { value: Breakpoint; Icon: typeof IconDeviceDesktop; label: string }[] = [
  { value: 'desktop', Icon: IconDeviceDesktop, label: 'Desktop' },
  { value: 'tablet',  Icon: IconDeviceTablet,  label: 'Tablette' },
  { value: 'mobile',  Icon: IconDeviceMobile,  label: 'Mobile' },
]

interface Props {
  value: Breakpoint
  onChange: (bp: Breakpoint) => void
  size?: 'sm' | 'md'
}

export function BreakpointPicker({ value, onChange, size = 'sm' }: Props) {
  const btn = size === 'sm' ? 'h-6 w-6' : 'h-7 w-7'
  const icon = size === 'sm' ? 12 : 14
  return (
    <div
      role="tablist"
      className="inline-flex items-center gap-0.5 p-0.5 rounded-md bg-muted/60 ring-1 ring-border"
    >
      {ITEMS.map(({ value: v, Icon, label }) => (
        <button
          key={v}
          type="button"
          role="tab"
          aria-selected={value === v}
          aria-label={label}
          title={label}
          onClick={() => onChange(v)}
          className={cn(
            'flex items-center justify-center rounded transition-all',
            btn,
            value === v
              ? 'bg-background text-foreground shadow-sm'
              : 'text-muted-foreground hover:text-foreground'
          )}
        >
          <Icon size={icon} />
        </button>
      ))}
    </div>
  )
}

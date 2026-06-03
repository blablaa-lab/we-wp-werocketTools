import { useState, type ReactNode } from 'react'
import { Label } from '@/components/ui/label'
import { BreakpointPicker } from './BreakpointPicker'
import type { Breakpoint, ResponsiveValue } from '@/lib/types'

interface Props<T> {
  label: string
  icon?: ReactNode
  value: ResponsiveValue<T>
  onChange: (next: ResponsiveValue<T>) => void
  children: (active: Breakpoint, value: T, onChangeValue: (v: T) => void) => ReactNode
  defaultBreakpoint?: Breakpoint
}

export function ResponsiveControl<T>({
  label,
  icon,
  value,
  onChange,
  children,
  defaultBreakpoint = 'desktop',
}: Props<T>) {
  const [active, setActive] = useState<Breakpoint>(defaultBreakpoint)

  const updateValue = (v: T) => {
    onChange({ ...value, [active]: v })
  }

  return (
    <div className="space-y-2">
      <div className="flex items-center justify-between gap-2">
        <Label className="flex items-center gap-1.5 text-xs text-muted-foreground">
          {icon}
          {label}
        </Label>
        <BreakpointPicker value={active} onChange={setActive} />
      </div>
      {children(active, value[active], updateValue)}
    </div>
  )
}

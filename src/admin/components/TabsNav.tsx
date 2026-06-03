import { IconLayoutDashboard, IconCookie, IconStarFilled } from '@tabler/icons-react'
import { cn } from '@/lib/utils'
import type { Module } from '@/lib/types'

const MODULE_ICONS: Record<string, React.ReactNode> = {
  cookies: <IconCookie size={16} />,
  google_reviews: <IconStarFilled size={16} />,
}

interface Props {
  modules: Module[]
  currentTab: string
  onNavigate: (tab: string) => void
}

export function TabsNav({ modules, currentTab, onNavigate }: Props) {
  const tabClass = (active: boolean) =>
    cn(
      'relative inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-sm font-medium whitespace-nowrap transition-all cursor-pointer select-none',
      active
        ? 'bg-background text-foreground shadow-sm ring-1 ring-foreground/5'
        : 'text-muted-foreground hover:text-foreground'
    )

  return (
    <nav
      className="bg-muted rounded-full p-1 inline-flex items-center gap-1 ring-1 ring-foreground/10"
      aria-label="Navigation principale"
    >
      <button type="button" className={tabClass(currentTab === 'dashboard')} onClick={() => onNavigate('dashboard')}>
        <IconLayoutDashboard size={16} />
        Tableau de bord
      </button>
      {modules.map(m => (
        <button type="button" key={m.id} className={tabClass(currentTab === m.id)} onClick={() => onNavigate(m.id)}>
          {MODULE_ICONS[m.id] ?? null}
          {m.name}
        </button>
      ))}
    </nav>
  )
}

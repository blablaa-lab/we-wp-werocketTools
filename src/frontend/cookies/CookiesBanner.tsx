import { useEffect, useState } from 'react'
import { Button } from '@/components/ui/button'
import { IconCookie, IconSettings, IconX } from '@tabler/icons-react'
import { type CookiesSettings } from '@/lib/types'
import { getKlaroManager, hasConsented } from './klaro-client'

interface Props {
  config: CookiesSettings
  onOpenSettings: () => void
  onDismiss: () => void
}

export function CookiesBanner({ config, onOpenSettings, onDismiss }: Props) {
  const [visible, setVisible] = useState(false)

  useEffect(() => {
    if (hasConsented(config.cookie_name)) return
    getKlaroManager().then(manager => {
      if (!manager || manager.confirmed) return
      setTimeout(() => setVisible(true), 400)
    })
  }, [config.cookie_name])

  async function acceptAll() {
    const manager = await getKlaroManager()
    if (manager) manager.saveAndApplyConsents(true)
    setVisible(false)
    onDismiss()
  }

  async function declineAll() {
    const manager = await getKlaroManager()
    if (manager) manager.saveAndApplyConsents(false)
    setVisible(false)
    onDismiss()
  }

  function openSettings() {
    onOpenSettings()
  }

  if (!visible) return null

  const positionClasses: Record<string, string> = {
    'bottom-left':  'bottom-4 left-4 max-w-lg',
    'bottom-right': 'bottom-4 right-4 max-w-lg',
    'top-left':     'top-4 left-4 max-w-lg',
    'top-right':    'top-4 right-4 max-w-lg',
    'center':       'bottom-0 left-0 right-0',
  }
  const posClass = positionClasses[config.position] ?? positionClasses['bottom-left']
  const isBar = config.position === 'center'

  const isDark = config.theme === 'dark'
  const bg = isDark ? (config.color_background !== '#ffffff' ? config.color_background : '#1a1a2e') : config.color_background
  const textColor = isDark ? (config.color_text !== '#1f2937' ? config.color_text : '#e2e8f0') : config.color_text
  const borderColor = config.color_primary
  const isTop = config.position.startsWith('top')

  const buttons = (
    <>
      {!config.hide_learn_more && (
        <Button variant="outline" size="sm" onClick={openSettings} className="gap-1.5 text-xs h-8"
          style={{ borderColor }}>
          <IconSettings size={13} />
          {config.texts.settings ?? 'Personnaliser'}
        </Button>
      )}
      {!config.hide_decline_all && (
        <Button variant="ghost" size="sm" onClick={declineAll} className="gap-1.5 text-xs h-8">
          <IconX size={13} />
          {config.texts.decline_all ?? 'Tout refuser'}
        </Button>
      )}
      <Button size="sm" onClick={acceptAll} className="text-xs h-8 text-white"
        style={{ backgroundColor: config.color_primary }}>
        {config.texts.accept_all ?? 'Tout accepter'}
      </Button>
    </>
  )

  return (
    <div
      className={`fixed ${posClass} z-[9999] shadow-lg rounded-lg`}
      style={{
        backgroundColor: bg,
        color: textColor,
        borderTop: !isTop && !isBar ? `2px solid ${borderColor}` : undefined,
        borderBottom: isTop ? `2px solid ${borderColor}` : undefined,
        padding: isBar ? '12px 16px' : '16px',
      }}
    >
      <div className={`flex ${isBar ? 'flex-col sm:flex-row items-start sm:items-center justify-between' : 'flex-col'} gap-3`}>
        <div className="flex items-start gap-3 flex-1 min-w-0">
          <IconCookie size={18} className="shrink-0 mt-0.5" style={{ color: config.color_primary }} />
          <div className="min-w-0">
            {config.texts.notice_title && (
              <p className="font-semibold text-sm mb-1">{config.texts.notice_title}</p>
            )}
            {config.html_texts ? (
              <p className="text-xs leading-relaxed"
                dangerouslySetInnerHTML={{ __html: config.texts.notice_description ?? '' }} />
            ) : (
              <p className="text-xs leading-relaxed">{config.texts.notice_description}</p>
            )}
            {config.texts.privacy_policy_url && (
              <a href={config.texts.privacy_policy_url} target="_blank" rel="noopener noreferrer"
                className="text-xs underline mt-1 inline-block" style={{ color: config.color_primary }}>
                {config.texts.privacy_policy ?? 'Politique de confidentialité'}
              </a>
            )}
          </div>
        </div>
        <div className={`flex items-center gap-2 shrink-0 flex-wrap ${config.flip_buttons ? 'flex-row-reverse' : ''}`}>
          {buttons}
        </div>
      </div>
    </div>
  )
}

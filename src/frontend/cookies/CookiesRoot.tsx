import { useEffect, useState } from 'react'
import { type CookiesSettings } from '@/lib/types'
import { CookiesBanner } from './CookiesBanner'
import { CookiesSettingsDialog } from './CookiesSettingsDialog'
import { hasConsented } from './klaro-client'

interface Props {
  config: CookiesSettings
}

export function CookiesRoot({ config }: Props) {
  const [bannerVisible, setBannerVisible] = useState(!hasConsented(config.cookie_name))
  const [dialogOpen, setDialogOpen] = useState(false)

  useEffect(() => {
    const onOpen = () => setDialogOpen(true)
    const onBanner = () => setBannerVisible(true)
    document.addEventListener('werocket:open-settings', onOpen)
    document.addEventListener('werocket:show-banner', onBanner)
    return () => {
      document.removeEventListener('werocket:open-settings', onOpen)
      document.removeEventListener('werocket:show-banner', onBanner)
    }
  }, [])

  function handleDismiss() {
    setBannerVisible(false)
  }

  function handleConsentSaved() {
    setBannerVisible(false)
  }

  return (
    <>
      {bannerVisible && (
        <CookiesBanner
          config={config}
          onOpenSettings={() => {
            setBannerVisible(false)
            setDialogOpen(true)
          }}
          onDismiss={handleDismiss}
        />
      )}
      <CookiesSettingsDialog
        open={dialogOpen}
        onOpenChange={setDialogOpen}
        config={config}
        onConsentSaved={handleConsentSaved}
      />
    </>
  )
}

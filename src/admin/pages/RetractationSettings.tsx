import { useEffect, useState } from 'react'
import { useForm } from 'react-hook-form'
import { toast } from 'sonner'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Switch } from '@/components/ui/switch'
import { Button } from '@/components/ui/button'
import {
  IconLoader2, IconSettings, IconShieldCheck, IconBell, IconExternalLink,
  IconClipboardList, IconCode, IconAlertTriangle,
} from '@tabler/icons-react'
import { api } from '@/lib/api'
import { ModuleHeader } from '../components/ModuleHeader'
import type { RetractationSettings as TRetractationSettings } from '@/lib/types'

export function RetractationSettings() {
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const { register, handleSubmit, setValue, watch, reset } = useForm<TRetractationSettings>()

  useEffect(() => {
    api.get<{ settings: TRetractationSettings }>('/settings/retractation')
      .then(data => reset(data.settings))
      .finally(() => setLoading(false))
  }, [reset])

  async function onSubmit(data: TRetractationSettings) {
    setSaving(true)
    try {
      await api.put('/settings/retractation', { settings: data })
      toast.success('Réglages enregistrés')
    } catch (e) {
      toast.error(e instanceof Error ? e.message : 'Erreur lors de l\'enregistrement')
    } finally {
      setSaving(false)
    }
  }

  if (loading) return <Spinner />

  const slug = watch('endpoint_slug') ?? 'retractation'
  const siteUrl = window.location.origin

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
      <ModuleHeader
        title="Rétractation (WooCommerce)"
        description="Formulaire de rétractation en ligne conforme à l'obligation B2C 2026"
        saving={saving}
      />

      <Card className="border-amber-200 bg-amber-50/40 dark:bg-amber-950/20">
        <CardContent className="flex gap-3 py-4">
          <IconAlertTriangle className="size-5 text-amber-600 shrink-0 mt-0.5" />
          <div className="text-sm space-y-1">
            <p className="font-medium text-foreground">À valider par un juriste avant mise en production</p>
            <p className="text-muted-foreground text-xs leading-relaxed">
              Ce module s'inscrit dans la continuité du droit existant (Code de la consommation, art. L221-18 et s. ;
              formulaire type R221-1 ; AR support durable L221-21). La date d'entrée en application et la rédaction des
              CGV doivent être validées par un juriste.
            </p>
          </div>
        </CardContent>
      </Card>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2"><IconSettings size={16} /> Page publique</CardTitle>
            <CardDescription>Titre + slug de l'endpoint My Account</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <Field label="Titre de la page">
              <Input {...register('page_title')} placeholder="Demande de rétractation" />
            </Field>
            <Field label="Slug de l'endpoint">
              <Input {...register('endpoint_slug')} placeholder="retractation" />
              <p className="text-[11px] text-muted-foreground mt-1.5">
                Après modification, allez dans <strong>Réglages → Permaliens</strong> et cliquez sur Enregistrer pour rafraîchir les URLs.
              </p>
            </Field>
            <SwitchRow
              label="Afficher la notice légale"
              description="Phrase introductive rappelant le droit de rétractation au-dessus du formulaire."
              checked={!!watch('show_legal_notice')}
              onChange={v => setValue('show_legal_notice', v)}
            />
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2"><IconBell size={16} /> Notifications marchand</CardTitle>
            <CardDescription>Email reçu à chaque nouvelle demande</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <SwitchRow
              label="Notifier le marchand"
              description="Envoie un email à chaque nouvelle demande de rétractation."
              checked={!!watch('merchant_notify')}
              onChange={v => setValue('merchant_notify', v)}
            />
            <Field label="Email marchand (optionnel)">
              <Input {...register('merchant_email')} type="email" placeholder="admin@monsite.fr" />
              <p className="text-[11px] text-muted-foreground mt-1.5">
                Si vide, l'email d'administration WordPress est utilisé.
              </p>
            </Field>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2"><IconClipboardList size={16} /> Accès rapides</CardTitle>
          <CardDescription>Liens vers le formulaire public et l'écran de gestion</CardDescription>
        </CardHeader>
        <CardContent className="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <Button type="button" variant="outline" asChild className="h-auto py-3 justify-start text-left">
            <a href={`${siteUrl}/mon-compte/${slug}/`} target="_blank" rel="noreferrer noopener">
              <div className="flex flex-col items-start gap-0.5">
                <span className="flex items-center gap-1.5 font-medium">
                  <IconExternalLink size={14} />
                  Page client (My Account)
                </span>
                <span className="text-[11px] text-muted-foreground font-normal truncate">
                  /mon-compte/{slug}/
                </span>
              </div>
            </a>
          </Button>

          <Button type="button" variant="outline" asChild className="h-auto py-3 justify-start text-left">
            <a href={`${siteUrl}/wp-admin/admin.php?page=wr-retractations`}>
              <div className="flex flex-col items-start gap-0.5">
                <span className="flex items-center gap-1.5 font-medium">
                  <IconShieldCheck size={14} />
                  Gérer les demandes
                </span>
                <span className="text-[11px] text-muted-foreground font-normal">
                  WooCommerce → Rétractations
                </span>
              </div>
            </a>
          </Button>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2"><IconCode size={16} /> Shortcode invité</CardTitle>
          <CardDescription>Pour offrir le formulaire aux visiteurs non connectés</CardDescription>
        </CardHeader>
        <CardContent className="space-y-2">
          <p className="text-sm text-muted-foreground">
            Insérez ce shortcode sur une page publique :
          </p>
          <code className="block bg-muted text-foreground px-3 py-2 rounded-md font-mono text-sm">
            [wr_retractation]
          </code>
          <p className="text-xs text-muted-foreground">
            Le client saisit son numéro de commande + email pour ouvrir l'étape 2 (sélection des articles).
            Idéal pour une page « Rétractation » référencée dans le footer / les CGV.
          </p>
        </CardContent>
      </Card>

    </form>
  )
}

function Field({ label, children }: { label: string; children: React.ReactNode }) {
  return (
    <div className="space-y-1.5">
      <Label className="text-xs text-muted-foreground">{label}</Label>
      {children}
    </div>
  )
}

function SwitchRow({
  label,
  description,
  checked,
  onChange,
}: {
  label: string
  description?: string
  checked: boolean
  onChange: (v: boolean) => void
}) {
  return (
    <label className="flex items-start justify-between gap-3 py-2 cursor-pointer">
      <div className="min-w-0 flex-1">
        <div className="text-sm font-medium text-foreground">{label}</div>
        {description && (
          <p className="text-xs text-muted-foreground mt-0.5 leading-relaxed">{description}</p>
        )}
      </div>
      <Switch checked={checked} onCheckedChange={onChange} className="mt-1 shrink-0" />
    </label>
  )
}

function Spinner() {
  return (
    <div className="flex items-center justify-center py-20 text-muted-foreground gap-2">
      <IconLoader2 size={20} className="animate-spin" /> Chargement...
    </div>
  )
}

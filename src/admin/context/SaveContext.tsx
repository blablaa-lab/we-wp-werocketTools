import { createContext, useContext, useState, useCallback, useEffect, type ReactNode } from 'react'

interface SaveContextValue {
  formId: string | null
  saving: boolean
  isDirty: boolean
  registerForm: (id: string) => void
  unregisterForm: (id: string) => void
  setSaving: (b: boolean) => void
  setDirty: (b: boolean) => void
}

const SaveContext = createContext<SaveContextValue | null>(null)

export function SaveProvider({ children }: { children: ReactNode }) {
  const [formId, setFormId] = useState<string | null>(null)
  const [saving, setSavingState] = useState(false)
  const [isDirty, setDirtyState] = useState(false)

  const registerForm = useCallback((id: string) => {
    setFormId(id)
    setSavingState(false)
    setDirtyState(false)
  }, [])

  const unregisterForm = useCallback((id: string) => {
    setFormId(prev => (prev === id ? null : prev))
    setDirtyState(false)
  }, [])

  const setSaving = useCallback((b: boolean) => setSavingState(b), [])
  const setDirty = useCallback((b: boolean) => setDirtyState(b), [])

  return (
    <SaveContext.Provider value={{ formId, saving, isDirty, registerForm, unregisterForm, setSaving, setDirty }}>
      {children}
    </SaveContext.Provider>
  )
}

export function useSaveContext(): SaveContextValue {
  const ctx = useContext(SaveContext)
  if (!ctx) {
    throw new Error('useSaveContext doit être utilisé à l\'intérieur d\'un <SaveProvider>')
  }
  return ctx
}

/**
 * Hook utilitaire à monter dans chaque page de réglages. Enregistre le formId
 * tant que la page est montée, fournit un setter saving pré-câblé, et sync
 * l'état dirty avec le contexte global (utilisé par le badge "non enregistré"
 * dans le header + le beforeunload guard).
 */
export function useRegisterSaveForm(formId: string, dirty?: boolean): {
  saving: boolean
  setSaving: (b: boolean) => void
} {
  const { registerForm, unregisterForm, saving, setSaving, setDirty } = useSaveContext()

  useEffect(() => {
    registerForm(formId)
    return () => unregisterForm(formId)
  }, [formId, registerForm, unregisterForm])

  useEffect(() => {
    if (dirty !== undefined) setDirty(dirty)
  }, [dirty, setDirty])

  return { saving, setSaving }
}

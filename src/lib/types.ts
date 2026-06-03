export interface Module {
  id: string
  name: string
  description: string
  icon: string
  active: boolean
}

export interface ModulesResponse {
  modules: Module[]
}

export interface SettingsResponse {
  settings: Record<string, unknown>
}

export interface ApiResponse<T = unknown> {
  success?: boolean
  data?: T
  message?: string
  code?: string
}

export interface Review {
  author_name: string
  profile_photo_url?: string
  rating: number
  text: string
  relative_time_description: string
  time: number
}

export interface ReviewsSettings {
  google_place_id: string
  google_api_key: string
  display_style: string
  reviews_count: number
  min_rating: number
  show_rating: boolean
  show_date: boolean
  show_avatar: boolean
  cache_duration: number
  custom_css: string
}

export type CookiePosition = 'bottom-left' | 'bottom-right' | 'top-left' | 'top-right' | 'center'
export type CookieTheme = 'light' | 'dark' | 'custom'
export type StorageMethod = 'cookie' | 'localStorage'
export type ConsentValue = 'granted' | 'denied'

export interface CookieService {
  name: string
  title: string
  description: string
  purposes: string[]
  cookies: string[]
  required: boolean
  default: boolean
  opt_out: boolean
  only_once: boolean
  enabled: boolean
}

export interface CookiePurpose {
  title: string
  description: string
}

export interface CookiesSettings {
  cookie_name: string
  cookie_expires_days: number
  cookie_domain: string
  storage_method: StorageMethod
  must_consent: boolean
  accept_all: boolean
  hide_decline_all: boolean
  hide_learn_more: boolean
  hide_toggle_all: boolean
  default: boolean
  required: boolean
  opt_out: boolean
  group_by_purpose: boolean
  theme: CookieTheme
  position: CookiePosition
  modal_trigger_position: string
  notice_as_modal: boolean
  flip_buttons: boolean
  html_texts: boolean
  color_primary: string
  color_primary_hover: string
  color_background: string
  color_text: string
  color_text_secondary: string
  color_border: string
  color_toggle_on: string
  color_toggle_off: string
  texts: Record<string, string>
  gcm_enabled: boolean
  gcm_default_analytics: ConsentValue
  gcm_default_ad_storage: ConsentValue
  gcm_default_ad_user_data: ConsentValue
  gcm_default_ad_personalization: ConsentValue
  gcm_default_functionality: ConsentValue
  gcm_default_security: ConsentValue
  gcm_wait_for_update: number
  gcm_region: string
  services: CookieService[]
  purposes: Record<string, CookiePurpose>
  additional_class: string
  custom_css: string
  callback_on_accept: string
  callback_on_decline: string
}

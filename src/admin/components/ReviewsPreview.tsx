import type { UseFormWatch } from 'react-hook-form'
import { TEMPLATES, getWrapClass } from '@/frontend/reviews/templates'
import type { Review, ReviewsSettings, ReviewTemplate } from '@/lib/types'

const MOCK_REVIEWS: Review[] = [
  {
    author_name: 'Marie Dubois',
    profile_photo_url: 'https://i.pravatar.cc/64?img=47',
    rating: 5,
    text: 'Service exceptionnel, équipe à l\'écoute et très professionnelle. Le résultat dépasse mes attentes, je recommande vivement !',
    relative_time_description: 'il y a 2 semaines',
    time: Date.now() / 1000,
  },
  {
    author_name: 'Thomas Laurent',
    profile_photo_url: 'https://i.pravatar.cc/64?img=12',
    rating: 5,
    text: 'Travail impeccable, livré dans les temps. Communication fluide du début à la fin. Je referai appel à eux sans hésiter.',
    relative_time_description: 'il y a 1 mois',
    time: Date.now() / 1000,
  },
  {
    author_name: 'Sophie Martin',
    profile_photo_url: 'https://i.pravatar.cc/64?img=32',
    rating: 4,
    text: 'Très bonne expérience globale. Quelques petits ajustements ont été nécessaires mais l\'équipe a été très réactive.',
    relative_time_description: 'il y a 3 mois',
    time: Date.now() / 1000,
  },
  {
    author_name: 'Antoine Garcia',
    rating: 5,
    text: 'Une prestation de qualité, avec un excellent rapport qualité/prix. Je suis ravi du résultat final.',
    relative_time_description: 'il y a 5 mois',
    time: Date.now() / 1000,
  },
  {
    author_name: 'Camille Roux',
    profile_photo_url: 'https://i.pravatar.cc/64?img=20',
    rating: 5,
    text: 'Bravo pour le sérieux et le professionnalisme. Tout a été parfait, du premier contact à la livraison.',
    relative_time_description: 'il y a 6 mois',
    time: Date.now() / 1000,
  },
]

interface Props {
  watch: UseFormWatch<ReviewsSettings>
}

export function ReviewsPreview({ watch }: Props) {
  const template = (watch('template') as ReviewTemplate) || 'classic'
  const displayStyle = watch('display_style') || 'grid'
  const minRating = Number(watch('min_rating') ?? 4)
  const count = Math.max(1, Math.min(20, Number(watch('reviews_count') ?? 3)))

  const settings: Partial<ReviewsSettings> = {
    template,
    display_style: displayStyle,
    show_rating: watch('show_rating'),
    show_date: watch('show_date'),
    show_avatar: watch('show_avatar'),
    min_rating: minRating,
    reviews_count: count,
  }

  const reviews = MOCK_REVIEWS
    .filter(r => r.rating >= minRating)
    .slice(0, count)

  if (!reviews.length) {
    return (
      <p className="text-sm text-muted-foreground text-center py-8">
        Aucun avis fictif ne correspond à la note minimale ({minRating}★).
      </p>
    )
  }

  const Template = TEMPLATES[template] ?? TEMPLATES.classic
  const isCarousel = displayStyle === 'carousel'

  return (
    <div className={getWrapClass(displayStyle)}>
      {reviews.map((review, i) => (
        <div
          key={i}
          className={isCarousel ? 'flex-none w-72 [scroll-snap-align:start]' : undefined}
        >
          <Template review={review} settings={settings} />
        </div>
      ))}
    </div>
  )
}

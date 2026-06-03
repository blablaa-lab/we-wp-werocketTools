# WeRocket Tools

Plugin WordPress pour agences : gestion du consentement cookies (GDPR), affichage des avis Google, et informations Google Business.

## Installation initiale sur un site WordPress

1. Télécharger le ZIP `werocket-tools.zip` depuis la [dernière release GitHub](https://github.com/blablaa-lab/we-wp-werocketTools/releases/latest)
2. Dans WordPress : **Extensions → Ajouter → Téléverser une extension**
3. Uploader le ZIP et activer le plugin

## Mises à jour automatiques

Le plugin se met à jour automatiquement depuis ce dépôt GitHub privé. Pour activer les updates dans le back-office WordPress, ajouter dans le fichier `wp-config.php` du site :

```php
define( 'WEROCKET_TOOLS_GH_TOKEN', 'ghp_xxxxxxxxxxxxxxxxxxxx' );
```

Le PAT (Personal Access Token) GitHub doit avoir les droits **Contents: Read-only** sur le dépôt `blablaa-lab/we-wp-werocketTools`.

Une fois configuré, les mises à jour apparaissent dans **Tableau de bord → Mises à jour**, comme n'importe quel plugin WordPress.

### Générer un PAT GitHub

1. GitHub → Settings → Developer settings → Personal access tokens → Fine-grained tokens
2. Cliquer **Generate new token**
3. Repository access : sélectionner uniquement `we-wp-werocketTools`
4. Permissions : **Contents → Read-only**
5. Copier le token et le coller dans `wp-config.php`

## Développement

### Prérequis

- Node.js 20+
- npm

### Lancer le build local

```bash
npm install
npm run dev    # watch mode
npm run build  # build de production
```

### Publier une nouvelle version

Pousser sur `main` — le workflow GitHub Actions s'occupe de tout :
- Build Vite
- Bump automatique de la version patch
- Création du tag et de la release GitHub avec le ZIP

### Stack technique

- **PHP 8.0+** — PSR-4, pas de Composer
- **React 19 + TypeScript** — interface admin via Vite
- **shadcn/ui + Tailwind CSS v4** — composants UI
- **Plugin Update Checker v5.7** — système d'auto-update depuis GitHub

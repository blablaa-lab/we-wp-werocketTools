#!/bin/bash

set -e

PLUGIN_SLUG="werocket-tools"
PLUGIN_DIR="$(cd "$(dirname "$0")" && pwd)"
OUTPUT_DIR="$PLUGIN_DIR/../"
ZIP_PATH="$OUTPUT_DIR$PLUGIN_SLUG.zip"

echo "→ Build du zip de distribution pour $PLUGIN_SLUG"

# Vérifier que le build existe
if [ ! -d "$PLUGIN_DIR/dist" ]; then
  echo "⚠️  Dossier dist/ introuvable. Lance d'abord : npm run build"
  exit 1
fi

# Supprimer un zip existant
rm -f "$ZIP_PATH"

# Créer le zip en excluant les fichiers de développement
cd "$PLUGIN_DIR/.."

zip -r "$ZIP_PATH" "$PLUGIN_SLUG" \
  --exclude "$PLUGIN_SLUG/node_modules/*" \
  --exclude "$PLUGIN_SLUG/src/*" \
  --exclude "$PLUGIN_SLUG/.git/*" \
  --exclude "$PLUGIN_SLUG/.gitignore" \
  --exclude "$PLUGIN_SLUG/package-lock.json" \
  --exclude "$PLUGIN_SLUG/package.json" \
  --exclude "$PLUGIN_SLUG/vite.config.ts" \
  --exclude "$PLUGIN_SLUG/tsconfig.json" \
  --exclude "$PLUGIN_SLUG/tsconfig.app.json" \
  --exclude "$PLUGIN_SLUG/tsconfig.node.json" \
  --exclude "$PLUGIN_SLUG/tailwind.config.js" \
  --exclude "$PLUGIN_SLUG/components.json" \
  --exclude "$PLUGIN_SLUG/CLAUDE.md" \
  --exclude "$PLUGIN_SLUG/build-zip.sh" \
  --exclude "$PLUGIN_SLUG/*.zip"

SIZE=$(du -sh "$ZIP_PATH" | cut -f1)
echo "✅ Zip créé : $ZIP_PATH ($SIZE)"

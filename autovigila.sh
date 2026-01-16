#!/bin/bash

# Carpeta a vigilar
DIRECTORY="/var/www/segusmart-core"

echo "👀 Vigilando cambios en $DIRECTORY..."

# Vigilamos eventos de cerrar un archivo tras escribir (close_write)
# Excluimos la carpeta .git para no entrar en un bucle infinito
inotifywait -m -r -e close_write --exclude '\.git' "$DIRECTORY" | while read path action file; do
    echo "💾 Cambio detectado en $file. Preparando versión..."
    
    # Esperamos 5 segundos por si estás guardando varios archivos a la vez
    sleep 5
    
    # Generamos un timestamp para la versión y el mensaje
    VERSION="v$(date +'%Y%m%d.%H%M%S')"
    MESSAGE="Auto-update: Cambio detectado en $file"
    
    cd "$DIRECTORY"
    git add .
    # Solo hacemos commit si hay cambios reales
    if git commit -m "$MESSAGE"; then
        git tag -a "$VERSION" -m "Versión automática $VERSION"
        git push origin main --tags
        echo "✅ Versión $VERSION subida a GitHub."
    else
        echo "shhh... sin cambios reales que subir."
    fi
done

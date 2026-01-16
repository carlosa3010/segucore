#!/bin/bash
git add .
git commit -m "$1"
git tag -a "$2" -m "Versión $2"
git push origin main --tags
echo "✅ Cambio subido y Versión $2 creada."


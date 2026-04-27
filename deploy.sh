#!/bin/bash

# Colores para que se vea bonito en la terminal
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}📚 INICIANDO DESPLIEGUE AUTOMÁTICO DE GESTOR LIBRV́M...${NC}"

# 1. Levantar contenedores (en segundo plano)
echo -e "${YELLOW}🐳 Levantando contenedores Docker...${NC}"
docker-compose up -d --build

# 2. Instalar dependencias de PHP (Composer)
echo -e "${YELLOW}📦 Instalando dependencias (Composer)...${NC}"
docker-compose exec -T app composer install --optimize-autoloader --no-dev


if [ ! -f src/.env ]; then
    echo -e "${YELLOW}📄 Creando archivo .env...${NC}"
    cp src/.env.example src/.env
    docker-compose exec -T app php artisan key:generate
fi

# 4. Esperar un poco a que la Base de Datos arranque bien
echo -e "${YELLOW}⏳ Esperando a que la Base de Datos esté lista...${NC}"
sleep 10

# 5. Resetear Base de Datos y ejecutar Seeders (Crea Admin y Maria)
echo -e "${YELLOW}🔄 Reseteando Base de Datos y creando usuarios...${NC}"
docker-compose exec -T app php artisan migrate:fresh --seed --force

# 6. Limpiar cachés y optimizar (Evita error 419 y rutas viejas)
echo -e "${YELLOW}🧹 Limpiando cachés...${NC}"
docker-compose exec -T app php artisan config:clear
docker-compose exec -T app php artisan cache:clear
docker-compose exec -T app php artisan route:clear
docker-compose exec -T app php artisan view:clear

# 7. Permisos de carpetas (Para evitar errores de escritura en logs)
echo -e "${YELLOW}🔒 Ajustando permisos...${NC}"
docker-compose exec -T app chmod -R 777 storage bootstrap/cache

echo -e "${GREEN}✅ ¡DESPLIEGUE COMPLETADO CON ÉXITO!${NC}"
echo -e "${GREEN}🌍 Entra en: http://localhost:8090${NC}"
echo -e "${BLUE}   - Admin: admin@test.com / password${NC}"
echo -e "${BLUE}   - User:  maria@test.com / password${NC}"

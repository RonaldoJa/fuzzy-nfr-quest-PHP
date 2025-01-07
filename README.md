# Sof-Req-Game-Backend
Servicio de back-end para Sof-Req-Game.

## Requisitos
Para construir y ejecutar la aplicación, se requiere:
- Docker

O instalación local de:

- PHP 8.2
- MySQL 8.0
- Apache 2.4

## Levantar la aplicación
1. Clonar el repositorio
2. Crear un archivo .env con las siguientes variables:

```
APP_ENV=development

DB_HOST=
DB_NAME=
DB_USER=
DB_ROOT_PASSWORD=
DB_PASSWORD=

OPENAI_API_KEY=
SOF_REQ_ASSISTANT_ID=

SEED_USER=
SEED_PASSWORD=

SUB_FOLDER_NAME=
```

3. Para desarrollo, clonar el archivo .htaccess.development y renombrarlo a .htaccess.

4. Construir la imagen Docker

```
docker compose build
```

5. Ejecutar la imagen Docker

```
docker compose up -d
```

## Configuración para producción

1. Si la aplicación se va a desplegar en un subdirectorio, se debe configurar la variable **SUB_FOLDER_NAME** en el archivo `.env`.

```
SUB_FOLDER_NAME=<nombre_del_subdirectorio>
```

Ejemplo:

```
SUB_FOLDER_NAME=ReqScapeNew
```

2. Para producción, clonar el archivo `.htaccess.production` y renombrarlo a `.htaccess`.

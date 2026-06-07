FROM node:18-alpine

WORKDIR /usr/src/app

# Copiar archivos de empaquetado primero para aprovechar la caché de Docker
COPY ../../src/node-service/package*.json ./

# Instalar dependencias de producción
RUN npm install

# Exponer el puerto de Express
EXPOSE 3000

# Comando por defecto para iniciar la aplicación usando node directamente sobre el index mapeado
CMD ["node", "index.js"]
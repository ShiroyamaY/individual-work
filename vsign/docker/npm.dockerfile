FROM node:23-alpine

WORKDIR /var/www/vsign-client

COPY client/package*.json ./

RUN npm install -g vite && npm install

ENTRYPOINT ["npm"]

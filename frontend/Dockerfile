# syntax=docker/dockerfile:1

# ---- dev: コードは bind mount で渡す ----
FROM node:22-alpine AS dev
WORKDIR /app
EXPOSE 3000
CMD ["sh", "-c", "npm install && npm run dev -- --host 0.0.0.0"]

# ---- build: SPA を静的ファイルとして生成 (ssr: false) ----
FROM node:22-alpine AS build
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run generate

# ---- prod: 静的配信（S3 + CloudFront に置くなら .output/public だけ使えばよい）----
FROM nginx:1.27-alpine AS prod
COPY --from=build /app/.output/public /usr/share/nginx/html

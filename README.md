# Teddy Bier Tools

Ferramentas de cálculo para cervejeiros artesanais. Laravel 11 + Vue 3 + Inertia.js + Tailwind CSS 4.

## Início Rápido — Desenvolvimento

### Requisitos

- Docker Engine 20.10+
- Docker Compose 2.0+

### Subir o ambiente

```bash
git clone <repo>
cd teddy-bier-tools
docker compose up -d
```

O Compose traz up automaticamente:
- **PHP-FPM** (app): localhost:9000 (via Nginx)
- **Nginx**: http://localhost:8010
- **Vite dev server**: http://localhost:5173 (HMR)
- **MySQL**: localhost:3307
- **Redis**: localhost:6380

Dependências PHP e Node serão instaladas automaticamente no primeiro start. Abra http://localhost:8010 no navegador.

**Hot reload:** Alterações em `.vue`, `.css`, `.js` refletem instantaneamente via Vite HMR — não rode `npm run build`.

### Parar e limpar

```bash
docker compose down
```

## Deploy em Produção

Para instruções completas de implantação num servidor com Traefik, veja [DEPLOY.md](DEPLOY.md).

Resumo:
```bash
# No servidor
git clone <repo> /srv/teddy-bier-tools
cd /srv/teddy-bier-tools
cp .env.example .env.production
# Edite .env.production com credenciais reais
docker compose -f compose.yaml -f compose.prod.yaml up -d
```

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

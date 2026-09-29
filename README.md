# Uttara University website

Laravel + Blade site with an admin content manager.

## Run

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

- Site: http://127.0.0.1:8000
- Admin: http://127.0.0.1:8000/admin


## Layout

- `app/` — controllers, CMS services
- `resources/views/layouts` — site Blade layout
- `resources/views/admin` — content manager
- `public/css`, `public/js` — front-end assets
- `storage/app/cms/pages.json` — page content
- `legacy/` — original static HTML (import source only)

Re-import static pages:

```bash
php artisan cms:import --force
```

## Stack

- PHP 8.3
- Laravel 13.32
- Blade templates
- MySQL 8.0
- Vite 8
- Tailwind CSS 4
- Vanilla JavaScript
- TinyMCE rich-text editor
- Font Awesome icons
- PHPUnit 12 and Laravel Pint
- Composer and npm

## Database

The application uses MySQL only.

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=uttara_university
DB_USERNAME=uu_app
DB_PASSWORD=<set-in-server-environment>
```
```


# CMS_UU-Clone-

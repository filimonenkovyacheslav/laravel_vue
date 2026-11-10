# Laravel + Vue — Multi-Category Property & Lifestyle Portal

**A custom content, listings, and user-profile platform built with Laravel and Vue.js.**

This repository contains a broad, multi-category web portal: property listings, real-estate professionals and agencies, architectural/design projects, jobs, news, and several marketplace-style catalogues. It combines Laravel-rendered pages and APIs with interactive Vue components.

## Project overview

The platform is structured around many types of searchable content and professional profiles instead of a single product catalogue. Visitors can browse listings and directories, while authenticated users have profile and content-management workflows.

### Application modules

| Module | Examples of functionality visible in the project |
| --- | --- |
| **Real estate** | Property directories, listing detail pages, property searches |
| **Professionals & companies** | Agents, agencies, architects, property-management and building-company profiles |
| **Design & architecture** | Design projects, project categories, portfolios, media |
| **Marketplace catalogues** | Products, general goods, furniture and wine directories |
| **Jobs & editorial** | Job listings, news, informational pages |
| **Account management** | Registration, login, user profiles and profile editing |
| **Content administration** | Profile-management views, image uploads, categories, editor interfaces |
| **Localization** | Language-aware routing, translated views and frontend language data |
| **Discovery** | Filtering/search components, maps/geosearch, galleries and saved-search related UI |

The repository includes code for both public-facing and administrative/profile routes.

## Architecture

The backend uses **Laravel 5.6** controllers organized by business domain. Public content can be rendered through Blade/Vue pages, while selected listing and profile data is exposed through JSON endpoints.

The frontend uses **Vue 2**, interactive components, jQuery/Bootstrap integrations and Laravel Mix for bundling. The codebase also includes media tooling, rich-text editing, search components and mapping libraries.

```text
app/
  Http/Controllers/
    Properties/       # Property listings
    Agencies/         # Agencies and company directories
    Agents/           # Professional profiles
    Designs/          # Architecture/design projects
    Products/ Goods/  # Product and marketplace catalogues
    Furnitures/ Wines/
    JobEntities/      # Job listings
    News/ Pages/      # Editorial content
    Profile/          # User-facing profile operations
    Uploads/          # File/media workflows
    Import/ Parsers/  # Import and parsing modules
resources/
  assets/js/
    app.js            # Vue initialization and components
    components/       # Vue UI components
  assets/sass/        # Frontend styles
  views/              # Blade pages and layouts
routes/
  web.php             # Localized public, user and admin routes
  api.php             # Selected listing/profile JSON endpoints
database/             # Database migrations and seed data
```

## Technology stack

| Area | Technologies |
| --- | --- |
| Backend | **PHP, Laravel 5.6**, Eloquent |
| Frontend | **Vue.js 2**, JavaScript, Blade, Bootstrap 4 |
| Asset pipeline | Laravel Mix / Webpack, Sass |
| Data & search | Laravel query logic, JSON endpoints, UI filtering |
| Maps | OpenLayers and Leaflet GeoSearch-related libraries |
| Content tools | TinyMCE, media uploads, image processing, galleries |
| Localization | Laravel localization packages, translation resources |
| Integrations | HTTP integrations and data import/parsing modules |

## Development setup

This is a **legacy project snapshot**, not a modern Laravel/Vue starter. Dependency versions, Node tooling and existing data-model expectations must be considered before running it.

```bash
git clone https://github.com/filimonenkovyacheslav/laravel_vue.git
cd laravel_vue

composer install
cp .env.example .env
php artisan key:generate

# Configure your own local database and project-specific settings.
php artisan migrate
npm install
npm run dev
php artisan serve
```

A successful local setup may additionally require compatible historical PHP/Node versions, seed/reference data, and external-service configuration. The commands above are **illustrative**, not tested installation guarantees.

## Engineering focus

This project demonstrates a Laravel/Vue architecture applied to a **large, heterogeneous business domain**: reusable frontend components, separate server-side controllers for each listing category, localized routing, rich profile workflows, and multiple content types served through one application.

## Important notes

- This is a **portfolio/source snapshot**, not a hosted demo or a claim of current production readiness.
- A historical SQL database dump and diagnostic scripts were removed from the current branch because they could expose private records or server details. **Do not use old commits as a source of production data or secrets.**
- The old compiled `public/js/app.js` asset was removed because it contained embedded API credentials. Run the frontend build (`npm run dev`) when creating a new local installation. Browser-side geocoding needs separately configured, domain-restricted *public* tokens via `window.MEDICALEER_MAP_KEYS` (`here`, `mapbox`, `bing`, `locationiq`). Browser tokens are visible to visitors by design; do not use unrestricted private keys.
- Before deployment, audit dependencies, publicly accessible utility scripts, credentials, access control, and any data stored in the repository.
- The absence of documented test results should not be interpreted as verified compatibility with recent PHP, Laravel, or Vue versions.

## Developer

**[Vyacheslav Filimonenko](https://github.com/filimonenkovyacheslav)** — Full-Stack Developer: PHP/Laravel, Vue/JavaScript, data-heavy web platforms, and API integrations.

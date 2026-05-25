# E-Commerce B2C Platform

Une plateforme e-commerce B2C monolithique construite avec **Laravel 12**, **PHP 8.3** et une architecture **DDD-lite**.

## 🏗️ Architecture

### Structure DDD-lite

L'application suit une architecture Domain-Driven Design simplifiée avec une séparation claire des responsabilités :

```
app/
├── Domains/                 # Core business logic organized by domain
│   ├── Catalog/            # Product management
│   ├── Cart/               # Shopping cart
│   ├── Checkout/           # Checkout process
│   ├── Order/              # Order management
│   ├── Customer/           # Customer profiles and accounts
│   ├── Promotion/          # Discounts and promotions
│   ├── Loyalty/            # Loyalty program
│   ├── Support/            # Customer support tickets
│   └── RMA/                # Returns management
├── Infrastructure/          # External service integrations
│   ├── Payment/            # Payment gateway integrations
│   ├── Notification/       # Email, SMS notifications
│   └── Search/             # Search engine integration
├── Http/
│   ├── Web/                # Frontend client controllers
│   └── Admin/              # Admin dashboard controllers
└── Core/                   # Shared abstractions
    ├── AbstractRepository
    ├── AbstractService
    └── ValueObjects/       # Money, Email, SKU
```

## 🛠️ Stack Technique

- **Framework**: Laravel 12
- **PHP**: 8.3+
- **Base de données**: PostgreSQL 16
- **Cache/Sessions/Queue**: Redis
- **Search**: Meilisearch
- **Frontend**: Livewire 3 + Blade
- **Package Manager**: Composer

## 📦 Packages Clés

- **Spatie** - Permission, Media, Activity Log, Query Builder, Data, Settings, Sluggable, Tags, Backup, Health
- **Laravel Scout** - Full-text search with Meilisearch
- **Livewire** - Reactive components
- **l3aro/pipeline-query-collection** - Query building helpers

## 🚀 Démarrage Rapide

### Prérequis

- Docker & Docker Compose
- Git

### Installation

1. **Cloner le repository**
   ```bash
   git clone <repository-url>
   cd new-commerce
   ```

2. **Démarrer les conteneurs Docker**
   ```bash
   docker-compose up -d
   ```

3. **Installer les dépendances**
   ```bash
   docker-compose exec app composer install
   ```

4. **Configuration de l'environnement**
   ```bash
   docker-compose exec app cp .env.example .env
   docker-compose exec app php artisan key:generate
   ```

5. **Migrations et seeds**
   ```bash
   docker-compose exec app php artisan migrate
   docker-compose exec app php artisan settings:seed
   docker-compose exec app php artisan db:seed
   ```

6. **Construire les assets**
   ```bash
   docker-compose exec app npm install
   docker-compose exec app npm run build
   ```

### Accès à l'application

- **Frontend**: http://localhost
- **Admin Dashboard**: http://localhost/admin
- **Meilisearch UI**: http://localhost:7700

## 🔒 Services Docker

| Service | Port | Détails |
|---------|------|---------|
| Nginx | 80, 443 | Web server |
| PHP-FPM | 9000 | Application |
| PostgreSQL | 5432 | Database |
| Redis | 6379 | Cache/Sessions/Queue |
| Meilisearch | 7700 | Full-text search |

### Identifiants par défaut

- **PostgreSQL**:
  - Database: `ecommerce`
  - User: `ecommerce`
  - Password: `ecommerce`

- **Meilisearch**:
  - Master Key: `masterKey`

## 📋 Conventions de Code

- **PSR-12** strict
- **Strict types**: `declare(strict_types=1);` dans tous les fichiers
- **Type hints**: Full type hints pour tous les arguments et retours
- **Dependency Injection**: Préféré aux Facades dans le domaine
- **Enums**: PHP 8.1+ enums pour tous les statuts
- **Value Objects**: Immuables pour Money, Email, SKU

## 🗄️ Configuration des Settings

Les settings e-commerce sont gérés via `config/settings.php` et stockés en base de données via Spatie LaravelSettings.

### Seeding initial
```bash
php artisan settings:seed
```

### Sections disponibles

- **shop**: Nom, devise, timezone, email
- **tax**: TVA et inclusion dans les prix
- **shipping**: Seuil livraison gratuite, méthodes
- **loyalty**: Programme de fidélité
- **catalog**: Affichage des produits
- **cart**: Gestion du panier
- **orders**: Configuration des commandes
- **rma**: Gestion des retours
- **support**: Support client
- **notifications**: Emails de notification
- **payment**: Passerelles de paiement
- **security**: Politiques de sécurité

## 📁 Structure des Domaines

Chaque domaine suit la structure suivante :

```
Domain/
├── Models/          # Eloquent models
├── Services/        # Business logic
├── Repositories/    # Data access
├── Actions/         # Single-purpose operations
├── Enums/          # Status enums
└── ValueObjects/   # Domain value objects
```

## 🧪 Tests

```bash
php artisan test
```

Les tests doivent être dans le répertoire `tests/` avec une structure mirroir du code source.

## 🔄 Queue & Jobs

Les jobs asynchrones utilisent Redis comme driver :

```bash
php artisan queue:listen
```

## 📊 Monitoring

### Health Checks
```bash
php artisan health:check
```

### Activity Log
Tous les changements importants sont enregistrés via `spatie/laravel-activitylog`

### Backups
```bash
php artisan backup:run
```

## 📚 IDE Helper

Générer l'autocompletion IDE :
```bash
composer ide-helper
```

## 🔐 Sécurité

- Strict PSR-12 coding standards
- Type hints complets
- Validation des données
- CSRF protection par défaut
- XSS prevention
- Password hashing avec bcrypt
- Rate limiting sur les endpoints sensibles

## 📝 Commandes Artisan Personnalisées

- `php artisan settings:seed` - Seed les settings e-commerce

## 🤝 Contribution

Les contributions sont les bienvenues ! Merci de respecter les conventions de code du projet.

## 📄 License

MIT


In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

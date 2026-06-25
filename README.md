# LIBOTA CONNEXION

Plateforme de généalogie africaine — **Préserver nos racines, transmettre notre histoire.**

## Vision

LIBOTA CONNEXION (LC) est une plateforme numérique dédiée à la préservation de l'histoire familiale, des traditions et des origines des familles africaines, avec un accent sur la République Démocratique du Congo (RDC).

## Structure du projet

```
Sytem Libota/
├── frontend/          # Next.js 16 + TypeScript + Tailwind CSS
├── backend/           # Laravel 12 API + SQLite/MySQL
└── README.md
```

## Fonctionnalités implémentées

### Frontend (MVP — Tableau de bord admin)

- Interface fidèle à la maquette fournie
- Sidebar de navigation complète (14 modules)
- KPIs : familles, membres, documents, publications, visites
- Graphiques : évolution des inscriptions, répartition par rôle
- Activités récentes, top 5 familles, documents récents
- Carte géographique RDC (visualisation simplifiée)

### Backend (MVP — Structure API)

- Migrations : `users`, `clans`, `families`, `family_members`, `marriages`, `documents`, `posts`, `notifications`
- Modèles Eloquent avec relations
- API REST : `GET /api/v1/dashboard`, `GET /api/v1/health`
- Seeder avec données de démonstration

## Démarrage rapide

### Frontend

```bash
cd frontend
npm install
npm run dev
```

Ouvrir [http://localhost:3000/admin](http://localhost:3000/admin)

### Backend

```bash
cd backend
composer install
php artisan migrate --seed
php artisan serve
```

API : [http://localhost:8000/api/v1/dashboard](http://localhost:8000/api/v1/dashboard)

**Compte admin de démo :** `admin@libota-connexion.cd` / `password`

## Roadmap

| Phase | Fonctionnalités |
|-------|-----------------|
| **MVP (actuel)** | Auth, familles, arbres, documents, dashboard admin |
| **Niveau 2** | Publications, cartographie, notifications, export GEDCOM |
| **Niveau 3** | IA (transcription audio), archives historiques, forum |

## Stack technique

| Couche | Technologie |
|--------|-------------|
| Frontend Web | Next.js 16, React 19, TypeScript, Tailwind CSS 4 |
| Backend API | Laravel 12, PHP 8.2 |
| Base de données | SQLite (dev) / MySQL (prod) |
| Mobile (prévu) | React Native Expo |

## Note d'analyse

| Critère | Évaluation |
|---------|------------|
| Innovation | 9/10 |
| Impact social | 10/10 |
| Faisabilité | 8/10 |
| Potentiel commercial | 8/10 |
| Valeur culturelle | 10/10 |

**Note globale : 9/10** — Projet à fort potentiel pour la RDC et la diaspora africaine.

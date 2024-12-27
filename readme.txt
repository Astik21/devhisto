/project-root
│
├── /app
│   ├── /controllers          # Logique de l'application
│   │   ├── PdfController.php
│   │   └── TaskController.php
│   │
│   ├── /models               # Modèles pour la base de données
│   │   ├── Devis.php
│   │   ├── LignePrix.php
│   │   ├── Ouvrage.php
│   │   └── Piece.php
│   │
│   ├── /services             # Intégration des services externes
│   │   ├── OcrService.php
│   │   ├── PdfParser.php
│   │   └── CronService.php
│   │
│   ├── /templates            # Fichiers HTML
│   │   ├── importPage.php
│   │   └── validationPage.php
│   │
│   ├── /assets               # Fichiers CSS, JS, images
│   │   └── /js               # Scripts JS
│   │       └── validation.js
│   │
│   └── /utils                # Fonctions utilitaires
│       └── PdfUtils.php
│
├── /cron                     # Tâches cron
│   └── import_task.php
│
├── /uploads                  # Fichiers PDF importés
│   ├── /to_validate
│   ├── /validated
│   └── /error
│
├── /migrations               # Scripts de migration
│
├── composer.json             # Gestion des dépendances
├── .env                      # Variables d'environnement
└── index.php                 # Point d'entrée

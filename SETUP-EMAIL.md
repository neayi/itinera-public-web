# Configuration de l'envoi d'emails avec Brevo

## Installation

### 1. Installer les dépendances PHP avec Composer

```bash
composer install
```

### 2. Configurer les variables d'environnement

1. Copiez le fichier `.env.example` vers `.env` :
```bash
cp .env.example .env
```

2. Éditez le fichier `.env` et remplissez vos identifiants Brevo :

```env
BREVO_SMTP_HOST=smtp-relay.brevo.com
BREVO_SMTP_PORT=587
BREVO_SMTP_USERNAME=votre-email-brevo@example.com
BREVO_SMTP_PASSWORD=votre-clé-smtp-brevo
BREVO_FROM_EMAIL=noreply@votredomaine.com
BREVO_FROM_NAME=Itinera
CONTACT_EMAIL=contact@votredomaine.com
```

### 3. Obtenir vos identifiants Brevo

1. Connectez-vous à votre compte [Brevo](https://app.brevo.com)
2. Allez dans **Paramètres** > **SMTP & API**
3. Générez une nouvelle clé SMTP si nécessaire
4. Utilisez votre email de connexion Brevo comme `BREVO_SMTP_USERNAME`
5. Utilisez la clé SMTP générée comme `BREVO_SMTP_PASSWORD`

## Structure des fichiers

- `send-email.php` - Script PHP qui gère l'envoi des emails via Brevo SMTP
- `.env` - Fichier de configuration (NON versionné dans Git)
- `.env.example` - Exemple de configuration (versionné dans Git)
- `composer.json` - Dépendances PHP (PHPMailer et DotEnv)

## Sécurité

⚠️ **IMPORTANT** : Le fichier `.env` contenant vos identifiants est automatiquement exclu de Git via `.gitignore`. Ne le committez jamais !

## Test

Pour tester l'envoi d'emails, remplissez simplement le formulaire de contact sur la page d'accueil. Le formulaire enverra les données au script PHP qui se chargera de l'envoi via Brevo.

## Configuration serveur

Assurez-vous que votre serveur web :
- A PHP 7.4 ou supérieur installé
- A Composer installé
- Peut établir des connexions SMTP sortantes (port 587)

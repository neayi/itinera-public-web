# Configuration HubSpot

## Obtenir le Form GUID

Pour que les soumissions de formulaire soient envoyées à HubSpot, vous devez configurer le `HUBSPOT_FORM_GUID` dans le fichier `js/contact-form.js`.

### Étapes pour trouver votre Form GUID :

1. **Connectez-vous à HubSpot** : Allez sur votre compte HubSpot
2. **Accédez aux formulaires** : Marketing > Formulaires
3. **Créez ou sélectionnez un formulaire** avec les champs suivants :
   - `firstname` (Prénom)
   - `lastname` (Nom)
   - `email` (Email)
   - `company` (Entreprise)
   - `message` (Message)
4. **Récupérez le Form GUID** :
   - Cliquez sur le formulaire
   - Dans l'URL, vous verrez quelque chose comme : `https://app.hubspot.com/forms/5882962/editor/XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX`
   - Le Form GUID est la dernière partie de l'URL (le UUID avec des tirets)

### Configuration

1. Ouvrez le fichier `js/contact-form.js`
2. Remplacez `'YOUR_FORM_GUID'` par votre Form GUID réel
3. Sauvegardez le fichier

```javascript
const HUBSPOT_FORM_GUID = 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'; // Votre Form GUID
```

## Fonctionnement

- Le formulaire envoie d'abord les données à votre serveur PHP (`send-email.php`)
- Si la soumission PHP réussit, les données sont également envoyées à HubSpot
- Si HubSpot échoue, cela n'affecte pas l'expérience utilisateur
- Les erreurs HubSpot sont enregistrées dans la console du navigateur

## Mapping des champs

Le formulaire HTML est mappé aux champs HubSpot comme suit :

| Champ HTML | Champ HubSpot | Description |
|------------|---------------|-------------|
| `firstname` | `firstname` | Prénom du contact |
| `lastname` | `lastname` | Nom du contact |
| `email` | `email` | Email du contact |
| `company` | `company` | Nom de l'entreprise (optionnel) |
| `message` | `message` | Message du contact (optionnel) |

## Test

Pour tester l'intégration :

1. Configurez le Form GUID
2. Soumettez le formulaire de contact
3. Vérifiez dans la console du navigateur (F12) les messages de log
4. Vérifiez dans HubSpot : Contacts > Listes pour voir le nouveau contact

## Désactivation

Si vous ne souhaitez pas utiliser HubSpot, laissez simplement `HUBSPOT_FORM_GUID` à `'YOUR_FORM_GUID'`. L'intégration sera automatiquement désactivée avec un avertissement dans la console.

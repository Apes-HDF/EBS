# EBS - Plateforme d'échange de Biens & Services


# Contexte 
Le logiciel est destiné aux porteurs de projets qui souhaitent développer une plateforme coopérative à une échelle territoriale locale (ville, EPCI, département, région Hauts-de-France) et ce dans une fédération d’instances de plateformes coopératives.

Ce logiciel propose une plateforme d'échange de biens et services au sein d'une communauté. 
La plateforme propose un moyen de rentrer en contact avec quelqu’un pour permettre cet échange : soit en tant que prêteur, qui propose un objet, soit en tant qu’emprunteur, qui l’utilise.

Pour les administrateur·rice·s de la plateforme, elle est personnalisable en configurant les fonctionnalités disponibles et en personnalisant les contenus via l’espace d’administration.

La plateforme a été conçue au format responsive pour être utilisée tant sur ordinateur qu’appareil mobile (tablette ou smartphone). 



# Licence 
Le code est disponible sous licence AGPL (Affero General Public License). Voir les détails sur [cette page](/LICENSE).


# Fonctionnalités 

## Interface d’administration
La plateforme dispose d’une interface d’administration, utilisant EasyAdmin, accessible aux  utilisateur·rice·s  disposant du rôle “administrateur·rice”.

Les administrateur·rice·s peuvent être ajoutés ou supprimés via l’interface d’administration.  

## Utilisateur·rice·s
Les utilisateur·rice·s peuvent s’inscrire librement sur la plateforme avec une adresse e-mail valide. 
Le processus de création de compte se fait en 2 étapes : saisie de l’adresse e-mail, envoi d’un lien de vérification de l’adresse e-mail, puis saisie du profil.  

La plateforme propose aux utilisateur·rice·s de choisir entre 2 types de profils : individuel ou lieu

- Utilisateur·rice individuel
Un individu s’inscrit en saisissant : prénom, nom, adresse e-mail, mot de passe (8 caractères minimum). 
Il pourra compléter ensuite son profil en ajoutant les données optionnelles : avatar, description, catégorie d’objets/services préférée, numéro de téléphone. 
Pour créer des objets/services, il devra également saisir une adresse. 

- Utilisateur·rice de type lieux
Un lieu partenaire est un espace physique qui sert de stockage et de récupération des objets proposés dans les annonces, comme par exemple une objetothèque. 
Il est possible de filtrer le formulaire de recherche d’annonce par lieu partenaire.

Un lieu s’inscrit en saisissant : nom, adresse e-mail, mot de passe (8 caractères minimum). 
Il pourra compléter ensuite son profil en ajoutant les données optionnelles : avatar, description, catégorie d’objets/services préférée, horaires, numéro de téléphone. 
Pour créer des objets/services, il devra également saisir une adresse.


Les utilisateur·rice·s disposent d’un profil visible via la fiche de ses objets ou l’annuaire des groupes dont il est membre.


Chaque utilisateur·rice pourra se connecter à son espace en ligne, ainsi que demander la réinitialisation de son mot de passe via la plateforme.

Les profils des utilisateur·rice·s peuvent être consultés dans l’interface d’administration. 
Il est également possible de transformer un compte utilisateur·rice en compte administrateur·rice via l’interface d’administration. 

## Système d’échange 

### Objets et services 
Un bien ou un service peut être proposé par un utilisateur·rice Prêteur à des utilisateur·rice·s Emprunteurs.

Il peut être créé par un utilisateur·rice individuel ou lieu librement via son compte utilisateur·rice, en saisissant les informations suivantes :

| Champ                                                     | Obligatoire / Facultatif                                                        | Détails            |
|-----------------------------------------------------------|---------------------------------------------------------------------------------|--------------------|
| Titre                                                     | Obligatoire                                                                     |                    |
| Catégorie                                                 | Obligatoire                                                                     |                    |
| Description                                               | Obligatoire                                                                     |                    |
| Âge de l’objet                                            |                                                                                 | Objet uniquement   |
| Durée de la prestation                                    |                                                                                 | Service uniquement |
| Montant de la caution                                     |                                                                                 |                    |
| Durée d’emprunt souhaité                                  |                                                                                 |                    |
| Photos                                                    | Si aucune photo n’est saisie, l’image par défaut de la catégorie sera utilisée. |                    |
| Visibilité : publique ou uniquement pour certains groupes |                                                                                 |                    |
 

L’objet ou service peut avoir le statut : en ligne ou en pause. L’utilisateur·rice propriétaire de l’objet/service peut modifier librement le statut via son compte utilisateur·rice. 

L’utilisateur·rice peut également renseigner des périodes d’indisponibilités de l’objet ou service, il ne sera alors pas possible de faire une demande d’emprunt à ces dates. 

La plateforme propose un traitement similaire aux objets et aux services. 

Les objets et services peuvent être consultés par les administrateur·rice·s dans l’interface d’administration.
Les services peuvent être activés ou désactivés pour l'instance, via l'espace d'administration.


### Catégories

Chaque objet ou service est associé à une catégorie ou sous-catégorie. 
Les catégories et sous-catégories sont administrables dans l’interface d’administration : nom de la catégorie, image par défaut, catégorie parente (si sous-catégorie). 

L’image par défaut de la catégorie/sous-catégorie est affichée pour les objets et services de cette catégorie n’ayant pas ajouté d’image.


### Rechercher un objet ou un service
La plateforme permet de parcourir les objets ou services proposés librement sans être connecté.
Les objets et services affichés seront tous ceux disponibles pour l’utilisateur·rice qui consulte. Seront donc masqués : les objets et services en pause, ceux d’utilisateurs “en vacances” ou désactivés, les objets et services dont la visibilité est restreinte à certains groupes dont l’utilisateur·rice qui consulte ne fait pas partie. 

Il est également possible de filtrer les objets et services par : catégorie, utilisateur·rice·s de type lieu (exemple : consulter tous les objets de l’objetothèque de ma ville), par une ville et rayon autour de la ville. 
Pour cela, la plateforme utilise le service MellieSearch. 

 
### Faire une demande d’emprunt
Depuis la fiche d’un objet il est possible de faire une demande d’emprunt. 
Le calendrier des disponibilités de l’objet/service affiche les jours disponibles et indisponibles (saisis par le prêteur ou ayant déjà un emprunt en cours). 
L’emprunteur saisit les dates de début et fin souhaitées pour l’emprunt, et envoie sa demande au prêteur. 

Une conversation entre le prêteur et l’emprunteur est alors créée, il s’agit d’un espace libre de conversation entre eux. 

Le prêteur devra autoriser la demande d’emprunt, qui sera ensuite confirmée par l’emprunteur. Les 2 utilisateur·rice·s se mettent alors d’accord entre eux sur les détails de l’emprunt. 
Le jour de la fin de l’emprunt, l’emprunt est automatiquement terminé, aucune action spécifique des 2 utilisateur·rice·s n’est nécessaire. 

L’emprunt peut être annulé ou refusé, les dates peuvent être modifiées avant la confirmation de l’emprunt. L’emprunt peut également être terminé manuellement (si l’objet est restitué avant la date de fin initialement prévue par exemple).

Les utilisateur·rice·s reçoivent des notifications par e-mail et SMS (si activé) à chaque étape de l’emprunt. 

Les emprunts peuvent être consultés par les administrateur·rice·s dans l’interface d’administration. Les conversations peuvent ou non être consultées également dans l’interface d’administration (cette option est activable par instance de la plateforme). 




## Mode vacances
Un utilisateur·rice peut activer le mode vacances dans son compte utilisateur·rice, cela masque alors automatiquement tous ses objets et services de la recherche. 
Il pourra le désactiver à la date de son choix dans son compte utilisateur·rice.

## Groupes 
La plateforme propose de gérer des groupes d’utilisateur·rice·s. 
La création de groupe peut être autorisée librement pour tous les utilisateur·rice·s, ou uniquement par les administrateur·rice·s. Cette option se configure dans les paramètres de l’instance. 

Les groupes peuvent être privés ou publics.
Un groupe public est en accès libre : les utilisateur·rice·s peuvent le rejoindre librement.
Un groupe privé peut être rejoint à partir d’une invitation. 
 
Un groupe dispose d’un·e gérant·e, de modérateur(s) et de membres. 
Les gérant·e·s et modérateurs de groupes ont accès aux options de gestion du groupe, et ils peuvent inviter des membres. 

L’adhésion à un groupe peut être gratuite ou payante. Si elle est payante, le tarif des adhésions est personnalisable dans l’interface d’administration des groupes, et le paiement devra se faire par l’utilisateur·rice invité au moment de rejoindre le groupe via le module de paiement sécurisé Mollie. 
Une adhésion peut être unique ou à durée fixe (mensuelle, annuelle). Si l’adhésion a une date de fin, lors de l’expiration de celle-ci, l’utilisateur·rice sera automatiquement retiré du groupe, il devra rejoindre à nouveau le groupe pour renouveler son adhésion et son paiement. 

Les utilisateur·rice·s reçoivent les invitations à des groupes par email ainsi que les rappels d’expiration de leur adhésion le cas échéant. 


Chaque groupe dispose d’une fiche descriptive et d’un annuaire de ses membres. 


## Notifications 
La plateforme gère l’envoi de notifications automatiques par e-mail et SMS pour les cas suivants : 
| Fonctionnalité                                | Type           |
|-----------------------------------------------|----------------|
| Mot de passe oublié                           | E-mail         |
| Création de compte (utilisateur·rice  et administrateur·rice)    | E-mail         |
| Nouvelle demande d’emprunt                    | E-mail et SMS  |
| Modification des dates de l’emprunt (x2)      | E-mail et SMS  |
| Validation de l’emprunt                       | E-mail et SMS  |
| Confirmation de l’emprunt                     | E-mail et SMS  |
| Emprunt refusé / annulé                       | E-mail et SMS  |
| Rappel 1 jour avant le début d’emprunt        | E-mail et SMS  |
| Rappel 1 jour avant fin d’emprunt             | E-mail et SMS  |
| Demande de création d’un groupe privé         | E-mail         |
| Nouveau gérant·e ou administrateur·rice de groupe             | E-mail et SMS  |
| Invitation dans un groupe                     | E-mail et SMS  |
| Rappel expiration d’adhésion à 1 groupe J-7 J | E-mail et SMS  |


L’envoi d’email devra être configuré pour chaque instance avec un service tiers dédié (envoi serveur, Mailgun, Sendinblue, Mailchimp, …) 
L’envoi de SMS devra être configuré pour chaque instance avec un service tiers dédié (Twillio, Sendinblue, OVH, …) 

## Contenu personnalisables

### Pages de contenu
Par défaut, la plateforme inclut 2 pages : la page d’accueil et la page de conditions générales d’utilisation. 
L’interface d’administration permet de modifier le contenu de ces pages grâce à l’éditeur de contenu wysiwyg CKEditor4 : textes mis en forme, images, tableau etc. 
Elle permet également de créer librement de nouvelles pages. 

NB : la page d’accueil affichera toujours en bas de page le bandeau de logos des financements du projet. Il n’est pas désactivable. 


### Menu (header) 
L’interface d’administration permet de configurer les liens du menu. Le menu peut comporter des menus et sous-menus (libellé + URL) ainsi que des pictogrammes (choix du pictogramme + URL). 

### Pied de page (footer) 
L’interface d’administration permet de configurer les liens du menu. Le pied de page peut comporter des liens (libellé + URL) ainsi que des pictogrammes (choix du pictogramme + URL).



# Configuration de l’instance

## Fonctionnalités configurables
Les options suivantes peuvent être configurées manuellement dans l’espace d’administration : 
- Activation des services
- Gestion des administrateur·rice·s de l’instance
- Expéditeur des notifications (e-mail, nom)
- Activation du lien de contact dans le menu
- Activation des groupes
- Création de groupe : Ouverte à tous ou uniquement par les administrateur·rice·s de l'instance
- Création de groupe payante ou gratuite (NB : le paiement se fait hors de la plateforme)
- Conversations d’emprunt visibles ou masquées dans l’espace d’administration


## Personnalisation du thème 
L’apparence globale du site (couleurs, disposition) est elle seulement modifiable par un technicien qui proposerait un « thème » Bootstrap.  


## Possibilité d’extension 
Il est possible de mettre en place une instance du logiciel Libre et Open Source PlateformeCoop puis de développer des pages supplémentaires sur-mesure. 
Il est par exemple possible de rajouter un fichier PHP et de lui attribuer une route sur le serveur HTTP, afin de permettre aux utilisateur·rice·s de charger cette page dans leur navigateur. 
La page en question bénéficiera de l’accès au reste du framework, à la base de données et au contexte de session de l’utilisateur·rice connecté·e.



# Installation et documentation technique
La documentation d'installation et configuration technique de la plateforme est disponible sur [cette page](docs/README.md).

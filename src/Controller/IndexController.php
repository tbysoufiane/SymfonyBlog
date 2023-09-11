<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Entity\Bulletin;
use App\Form\BulletinType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(ManagerRegistry $doctrine): Response
    {
        //Nous récupérons la liste des Bulletin de notre base de données grâce à l'Entity Manager de Doctrine et le Repository de Bulletin
        $entityManager = $doctrine->getManager();
        $bulletinRepository = $entityManager->getRepository(Bulletin::class);
        //Nous récupérons la liste de nos Category
        $categoryList = $bulletinRepository->findEachCategory();
        //Le Repository nous permet de lancer des recherches dans la base de données, nous allons l'utiliser pour récupérer tous les éléments de la table Bulletin
        $bulletins = $bulletinRepository->findAll();
        $bulletins = array_reverse($bulletins); //On inverse l'ordre du tableau afin d'avoir les éléments les plus récents en premier

        return $this->render('index/index.html.twig', [
            //Les variables Twig, utilisées dans notre template
            'bulletins' => $bulletins,
            'categoryList' => $categoryList,
        ]);
    }

    #[Route('/category/{categoryName}', name: 'index_category')]
    public function indexCategory(ManagerRegistry $doctrine, string $categoryName): Response
    {
        //Cette méthode renvoie la liste de tous les bulletins liés à la Catégorie indiquée dans l'URL.
        //Afin de pouvoir récupérer des Bulletin de notre base de données, nous avons besoin de l'Entity Manager ainsi que du Repository de Bulletin
        $entityManager = $doctrine->getManager();
        $bulletinRepository = $entityManager->getRepository(Bulletin::class);
        //Nous récupérons la liste de nos Category
        $categoryList = $bulletinRepository->findEachCategory();
        //On utilise le Repository pour rechercher la Category selon son nom
        //findBy() permet de récupérer une série de résultats sous forme de tableau. Le premier tableau permet de récupérer les éléments grâce à un critère. Le second tableau permet d'ordonner les éléments selon un critère.
        $bulletins = $bulletinRepository->findBy(['category' => $categoryName, 'pinned' => false], ['id' => 'DESC']);
        $pinnedBulletins = $bulletinRepository->findBy(['category' => $categoryName, 'pinned' => true], ['id' => 'DESC']);
        //Si aucun bulletin n'est retrouvé après cette recherche, nous retournons à l'index
        if(!$bulletins && !$pinnedBulletins){
            return $this->redirectToRoute('app_index');
        }
        //On renvoie notre liste de bulletins à index.html.twig
        return $this->render('index/index.html.twig', [
            'pinnedBulletins' => $pinnedBulletins,
            'bulletins' => $bulletins,
            'categoryList' => $categoryList,
        ]);
    }

    #[Route('/tag/{tagName}', name: 'index_tag')]
    public function indexTag(string $tagName, ManagerRegistry $doctrine): Response
    {
        //Cette route affiche tous les bulletins liés à un tag dont le nom est indiqué dans l'URL.
        //On récupère l'Entity Manager et le Repository de TAG, car c'est le TAG que nous voulons récupérer
        $entityManager = $doctrine->getManager();
        $tagRepository = $entityManager->getRepository(Tag::class);
        //On récupère le Tag grâce à la méthode du Repository findOneBy(), laquelle nous permet de récupérer un élément de la table indiquée de la base de données, selon un critère
        $tag = $tagRepository->findOneBy(['name' => $tagName]);
        //Le tag retrouvé sera instancié avec tous les Bulletins qui lui sont liés. Si le tag n'existe pas, la variable aura pour valeur null et nous retournons à l'index
        if(!$tag){
            return $this->redirectToRoute('app_index');
        }
        //On renvoie la liste des bulletins vers l'index
        return $this->render('index/index.html.twig', [
            'bulletins' => $tag->getBulletins(),
        ]);
    }

    #[Route('/cheatsheet', name: 'index_cheatsheet')]
    public function cheatsheet(): Response
    {
        return $this->render('index/cheatsheet.html.twig');
    }

    #[Route('/bulletin/pin/{bulletinId}', name: 'bulletin_pin')]
    public function pinBulletin(int $bulletinId, ManagerRegistry $doctrine): Response
    {
        /*
            Nous allons préparer cette méthode de Controller en l'ordonnant de la manière suivante:
            -> On initialise les Services dont a besoin (Manager, Repository)
            -> On crée ou récupère les objets dont on a besoin (on récupère l'Entity qui nous intéresse)
            -> On applique les différentes opérations sur l'objet dont on a besoin avec l'aide des Services initialisés (nous changeons la valeur de l'attribut $pinned avant d'utiliser l'Entity Manager pour persister ces changements)
            -> On affiche une page Twig ou on redirige le serveur vers une autre méthode de Contrôleur (On redirige le serveur vers la route 'app_index')
        */
        //1-Récupération des Services
        $entityManager = $doctrine->getManager();
        $bulletinRepository = $entityManager->getRepository(Bulletin::class);
        //2-Récupération de l'objet dont a besoin (le Bulletin)
        $bulletin = $bulletinRepository->find($bulletinId);
        if(!$bulletin) return $this->redirectToRoute('app_index');
        //3-Opérations sur l'objet
        if($bulletin->isPinned()){ //Si notre Bulletin est épinglé
            $bulletin->setPinned(false);
        } else $bulletin->setPinned(true);
        $entityManager->persist($bulletin);
        $entityManager->flush();
        //4-Rendu de page Twig ou Redirection
        return $this->redirectToRoute('app_index');
    }

    #[Route('/bulletin/display/{bulletinId}', name: 'bulletin_display')]
    public function displayBulletin(ManagerRegistry $doctrine, int $bulletinId): Response
    {
        //Cette méthode affiche un bulletin en particulier dont l'ID a été renseigné dans l'URL
        //Afin de pouvoir dialoguer avec notre base de données, nous avons besoin de l'Entity Manager, et du Repository de la classe Entity qui nous intéresse (Bulletin)
        $entityManager = $doctrine->getManager();
        $bulletinRepository = $entityManager->getRepository(Bulletin::class);
        //On récupère la liste de nos catégories pour le header
        $categoryList = $bulletinRepository->findEachCategory();
        //Nous recherchons le Bulletin selon son ID grâce à la méthode find()
        $bulletin = $bulletinRepository->find($bulletinId);
        //Si la recherche n'aboutit pas, la valeur rendue sera null. S'il n'existe pas de bulletin à afficher, nous retournons à l'index
        if(!$bulletin){
            return $this->redirectToRoute('app_index');
        }
        //Nous renvoyons notre bulletin dans un tableau vers notre index.html.twig
        return $this->render('index/index.html.twig', [
            'bulletins' => [$bulletin],
            'categoryList' => $categoryList,
        ]);
    }

    #[Route('bulletin/create', name: 'bulletin_create')]
    public function createBulletin(ManagerRegistry $doctrine, Request $request): Response
    {
        //Cette méthode permet la création via formulaire d'une instance d'Entity Bulletin.
        //Nous commençons par récupérer l'Entity Manager afin de pouvoir porter le nouveau bulletin au sein de notre base de données
        $entityManager = $doctrine->getManager();
        //Nous créons un nouvel objet Bulletin et un formulaire BulletinType auquel nous le lions
        $bulletin = new Bulletin('');
        $bulletin->clearFields(); //On vide les champs du Bulletin
        $bulletinForm = $this->createForm(BulletinType::class, $bulletin);
        //Une fois le bulletin créé, nous appliquons la requête actuelle sur ce dernier. Après validation du formulaire, la page est rechargée une seconde fois, et les valeurs stockées dans l'objet Request sont placées au sein du formulaire et de l'objet lié
        $bulletinForm->handleRequest($request);
        //Si le formulaire est rempli et valide, nous ajoutons le Bulletin lié à notre base de données
        if($bulletinForm->isSubmitted() && $bulletinForm->isValid()){
            $entityManager->persist($bulletin);
            $entityManager->flush();
            //On renvoie l'utilisateur à l'index
            return $this->redirectToRoute('app_index');
        }
        //Si le formulaire n'est pas rempli, nous envoyons notre formulaire sur Twig
        return $this->render('index/dataform.html.twig', [
            'formName' => 'Création de Bulletin',
            'dataForm' => $bulletinForm->createView(), //CreateView est indispendable pour afficher le formulaire
        ]);
    }

    #[Route('/bulletin/update/{bulletinId}', name: 'bulletin_update')]
    public function updateBulletin(int $bulletinId, ManagerRegistry $doctrine, Request $request): Response
    {
        //Cette méthode récupère un bulletin de notre base de données selon l'ID renseigné, et nous permet de le modifier via formulaire
        //Pour récupérer un Bulletin, il nous faut l'Entity Manager et le Repository de Bulletin
        $entityManager = $doctrine->getManager();
        $bulletinRepository = $entityManager->getRepository(Bulletin::class);
        //On récupère le Bulletin désiré selon l'ID renseigné. Si ce dernier n'est pas valide, nous retournons à l'index
        $bulletin = $bulletinRepository->find($bulletinId);
        if(!$bulletin){
            return $this->redirectToRoute('app_index');
        }
        //Le bulletin récupéré, nous le lions à un nouveau formulaire
        $bulletinForm = $this->createForm(BulletinType::class, $bulletin);
        //Nous appliquons la requête sur notre Bulletin
        $bulletinForm->handleRequest($request);
        //Si le formulaire est rempli et valide, nous persistons l'objet lié (le modifiant dans la base de données)
        if($bulletinForm->isSubmitted() && $bulletinForm->isValid()){
            $entityManager->persist($bulletin);
            $entityManager->flush();
            return $this->redirectToRoute('app_index');
        }
        //Si le formulaire n'est pas rempli, nous le présentons à l'utilisateur
        return $this->render('index/dataform.html.twig', [
            'formName' => 'Modification de Bulletin',
            'dataForm' => $bulletinForm->createView(),
        ]);
    }

    #[Route('/bulletin/delete/{bulletinId}', name: 'bulletin_delete')]
    public function deleteBulletin(int $bulletinId, ManagerRegistry $doctrine): Response
    {
        //Cette méthode permet la suppression d'un Bulletin de notre base de données dont l'ID a été renseigné dans l'URL.
        //On récupère l'Entity Manager et le Repository pour pouvoir accéder au Bulletin à supprimer dans notre base de données
        $entityManager = $doctrine->getManager();
        $bulletinRepository = $entityManager->getRepository(Bulletin::class);
        //On recherche le bulletin à supprimer selon le paramètre transmis dans l'URL
        $bulletin = $bulletinRepository->find($bulletinId);
        //Si le bulletin n'est pas trouvé, on retourne à l'index
        if(!$bulletin){
            return $this->redirectToRoute('app_index');
        }
        //Si nous avons le bulletin, nous procédons à sa suppression
        $entityManager->remove($bulletin);
        $entityManager->flush(); //On confirme la requête
        //On retourne sur notre page d'accueil
        return $this->redirectToRoute('app_index');
    }

    #[Route('/bulletin/generate', name: 'bulletin_generate')]
    public function generateBulletin(ManagerRegistry $doctrine): Response
    {
        //Cette méthode génère un bulletin.
        //On récupère l'Entity Manager pour les requêtes de persistance pour notre Bulletin à venir
        $entityManager = $doctrine->getManager();
        //On crée le Bulletin à persister
        $bulletin = new Bulletin("Bulletin généré #" . uniqid(), "généré");
        //Demande et exécution de la persistance de notre bulletin
        $entityManager->persist($bulletin);
        $entityManager->flush();
        //On est redirigé vers l'index
        return $this->redirectToRoute('app_index');
    }

    #[Route('/display', name: 'index_display')]
    public function displayBulletins(): Response
    {
        //Nous créons un bulletin sous forme de tableau associatif
        $bulletins = []; //tableau vide bulletinS

        for($i=0;$i<15;$i++){
            $bulletin = [
                'title' => 'Bulletin #' . rand(199,9999),
                'category' => 'Général',
                'content' => 'lorem',
                'date' => (new \DateTime("now")),
            ];
            array_push($bulletins, $bulletin); //Dans notre tableau $bulletinS, nous ajoutons notre $bulletin
        }

        return $this->render('index/index.html.twig', [
            //Les variables Twig, utilisées dans notre template
            'bulletins' => $bulletins,
        ]);
    }

    #[Route('/square/{routeArg}', name: 'index_square')]
    public function displaySquare(string $routeArg = ''): Response
    {
        //Corps de la méthode
        //Le Switch nous permet d'anticiper les valeurs possibles de $routeArg
        switch($routeArg){
            case 'rouge':
                $titre = 'red';
                break; //break permet de sortir du switch
            case 'vert':
                $titre = 'green';
                break;
            case 'jaune':
                $titre = 'yellow';
                break;
            case 'bleu':
                $titre = 'blue';
                break;
            case 'violet':
                $titre = 'purple';
                break;
            case '':    //Si $routeArg est laissée vide
                $titre = 'gray';
                break;
            default:    //Si aucune valeur ne correspond à $routeArg
                $titre = 'black';
        }

        //On renvoie un <div> vide en tant que réponse à l'utilisateur
        return new Response('<div style="width:300px;height:300px;background-color:' . $titre . ';"></div>');
    }
}

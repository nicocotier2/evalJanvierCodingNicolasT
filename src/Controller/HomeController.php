<?php

namespace App\Controller;

use App\Repository\BookReadRepository;
use App\Repository\BookRepository;
use App\Entity\BookRead;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

/*
ajout de mes entités BookRead, books et user pour pouvoir les afficher sur la page et avoir à disposition les infos du user qui sont connecter
*/

class HomeController extends AbstractController
{
    private BookReadRepository $readBookRepository;
    //private BookRepository $BookRepository;
    // Inject the repository via the constructor
    public function __construct(BookReadRepository $bookReadRepository)
    {
        $this->bookReadRepository = $bookReadRepository;
    }
/*
    route de la page principale, elle cherche dans la db des infos envoie à mes twig pour le bonne affichage des info sur ma page 
*/
    #[Route('/home', name: 'app.home')]
    public function index(BookRepository $bookRepository, BookReadRepository $bookReadRepository): Response
    {
        $user = $this->getUser();
        $userId = $user->getId();
        $booksRead  = $this->bookReadRepository->findByUserId($userId, false);
        $booksNotRead  = $this->bookReadRepository->findByUserId($userId, true);
        $books = $bookRepository->findAll();
        //ici, on envoie les data au view
        return $this->render('pages/home.html.twig', [
            'booksRead' => $booksRead,
            'booksNotRead' => $booksNotRead,
            'name'      => 'Accueil',
            'books' => $books,
            
        ]);

    }
    // route pour le modal afin qu'ils puissent créer les notes dans la db
#[Route('/add-book-note', name: 'book_read_create', methods: ['POST'])]
public function createBookRead(Request $request, EntityManagerInterface $em): JsonResponse
{
    // grace à ajax, on recup les données du formulaire pour ensuite les envoyé à la db
    // Récupération des données du formulaire
    $user = $this->getUser();
    $userId = $user->getId();
    $bookId = $request->request->get('book_id');
    $rating = $request->request->get('rating');
    $description = $request->request->get('description');
    $isRead = $request->request->get('finished') ? true : false;
    $cover = "test";

    // Création d'une nouvelle instance de BookRead
    $bookRead = new BookRead();
    $bookRead->setUserId($userId);
    $bookRead->setBookId($bookId);
    $bookRead->setRating($rating);
    $bookRead->setDescription($description);
    $bookRead->setRead($isRead);
    $bookRead->setCover($cover);
    $bookRead->setCreatedAt(new \DateTime());
    $bookRead->setUpdatedAt(new \DateTime());

    // Persistance et sauvegarde
    $em->persist($bookRead);
    $em->flush();

    // Réponse JSON
    return new JsonResponse([
        'success' => true,
        'message' => 'Le livre a été ajouté avec succès !',
    ]);
}

// route pour le modal afin qu'ils puissent mettre à jour les notes dans la db
#[Route('/add-book-update', name: 'book_read_update', methods: ['POST'])]
public function updateBookRead(Request $request, EntityManagerInterface $em): JsonResponse
{
    // Récupération des données du formulaire
    $bookReadId = $request->request->get('book_read_id'); // ici on récup l'id de la note 
    $rating = $request->request->get('rating'); //maintenant on récupère le reste des donnés du formulaire
    $description = $request->request->get('description');
    $isRead = $request->request->get('finished') ? true : false;
    // Création d'une nouvelle instance de BookRead
    $bookRead = $em->getRepository(BookRead::class)->findOneBy([
        'id' => $bookReadId,
    ]);
    $bookRead->setRating($rating);
    $bookRead->setDescription($description);
    $bookRead->setRead($isRead);
    $bookRead->setUpdatedAt(new \DateTime());

    // Persistance et sauvegarde
    $em->persist($bookRead);
    $em->flush();

    // Réponse JSON
    return new JsonResponse([
        'success' => true,
        'message' => 'La note a été mis à jour avec succès !',
    ]);
}



}

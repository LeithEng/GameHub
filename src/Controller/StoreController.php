<?php



namespace App\Controller;

use App\Repository\GameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StoreController extends AbstractController
{
    #[Route('/store', name: 'app_store')]
    public function index(Request $request, GameRepository $gameRepository): Response
    {
        $user = $this->getUser();
        if(!$user)
            return $this->redirectToRoute('app_login');
        if(in_array('ROLE_ADMIN', $user->getRoles()))
        {
            return $this->redirectToRoute('admin_dashboard');
        }
        $filters = [
            'genre' => $request->query->get('genre'),
            'publisher' => $request->query->get('publisher'),
            'price' => $request->query->get('price'),
        ];

        $priceRange = $request->query->get('price');
        if ($priceRange) {
            $priceParts = explode('-', $priceRange);
            $filters['price_min'] = $priceParts[0];
            $filters['price_max'] = $priceParts[1] ?? null;
        }


        $queryBuilder = $gameRepository->findByFilters(array_filter($filters));
        $games = $queryBuilder->getQuery()->getResult();
        $genres = $gameRepository->getGenres();
        $publishers = $gameRepository->getPublishers();

        return $this->render('store/store.html.twig', [
            'games' => $games,
            'genres' => $genres,
            'publishers' => $publishers,
        ]);
    }
}

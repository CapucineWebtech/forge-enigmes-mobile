<?php

namespace App\Controller;

use App\Entity\WineGame;
use App\Form\WineGameType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends AbstractController
{
    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(
        EntityManagerInterface $em
    )
    {
        $this->em = $em;
    }

    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        return $this->render('index.html.twig');
    }

    #[Route('/winegame/{id}', name: 'app_wineGame')]
    public function winegame(WineGame $wineGame, Request $request): Response
    {
        $form = $this->createForm(WineGameType::class, $wineGame);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($wineGame);
            $this->em->flush();
            $this->addFlash(
                'success',
                "Données mises à jour. Redémarrez la bouteille pour que les changements soient effectifs."
            );
            return $this->redirectToRoute('app_wineGame', ['id' => $wineGame->getId()]);
        }

        return $this->render('wineGame.html.twig', [
            'form' => $form,
            'wineGame' => $wineGame
        ]);
    }
}
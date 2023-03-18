<?php

namespace App\Controller;

use App\Entity\WineGame;
use App\Form\WineGameType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;

class HomeController extends AbstractController
{
    private $em;

    private function checkWineGameCookie(Request $request)
    {
        if (!$request->cookies->has('wineGameCookie')) {
            return null;
        }

        $cookieValue = $request->cookies->get('wineGameCookie');
        $cookieParts = explode(':', $cookieValue);

        $winegame = $this->em->getRepository(WineGame::class)->find($cookieParts[0]);
        if (!$winegame || $winegame->getCookiePass() != $cookieParts[1]) {
            return null;
        }

        return $winegame;
    }

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(
        EntityManagerInterface $em
    )
    {
        $this->em = $em;
    }

    #[Route('/chose-winegame', name: 'app_choseWineGame')]
    public function choseWineGame(Request $request): Response
    {
        $response = new Response();
        $response->headers->clearCookie('wineGameAdminCookie');
        $response->headers->clearCookie('wineGameUserCookie');
        $winegame = $this->checkWineGameCookie($request);
        if ($winegame) {
            return $this->redirectToRoute('app_index');
        }else{
            $response->headers->clearCookie('wineGameCookie');
        }

        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('wg')
            ->from('App\Entity\WineGame', 'wg')
            ->leftJoin('wg.user', 'u')
            ->where('u.id = :userId')
            ->setParameter('userId', $user->getId());

        $winegames = $queryBuilder->getQuery()->getResult();

        return $this->render('choseWineGame.html.twig', [
            'winegames' => $winegames
        ], $response);
    }

    #[Route('/setCookie/{id}', name: 'app_setCookie')]
    public function setCookie(WineGame $wineGame): Response
    {
        $cookieValue = $wineGame->getId().":".$wineGame->getCookiePass();
        $response = $this->redirectToRoute('app_index');
        $response->headers->setCookie(
            new Cookie('wineGameCookie', $cookieValue,0,'/',null,false,false)
        );
        return $response;
    }

    #[Route('/', name: 'app_index')]
    public function index(Request $request): Response
    {
        $response = new Response();
        $response->headers->clearCookie('wineGameAdminCookie');
        $response->headers->clearCookie('wineGameUserCookie');
        $winegame = $this->checkWineGameCookie($request);
        if (!$winegame) {
            return $this->redirectToRoute('app_choseWineGame');
        }

        $form = $this->createFormBuilder()
            ->add('enter', TextType::class, [
                'label' => $winegame->getUserCodeName(),
                'required' => true,
                'attr' => [
                    'autofocus' => true,
                    'autocomplete' => 'off'
                ],
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // check user code
            if ($data['enter'] === $winegame->getUserCode()) {
                return $this->redirectToRoute('app_setUserCookie');
            }
            // check admin code
            if ($data['enter'] === $winegame->getAdminCode()) {
                return $this->redirectToRoute('app_setAdminCookie');
            }
            // invalid code
            $this->addFlash('error', 'Code incorrect');
            return $this->redirectToRoute('app_index');
        }

        return $this->render('index.html.twig', ['form' => $form], $response);
    }

    #[Route('/setAminCookie', name: 'app_setAdminCookie')]
    public function setAminCookie(): Response
    {
        $response = $this->redirectToRoute('app_admin');
        $response->headers->setCookie(
            new Cookie('wineGameAdminCookie', "admin",0,'/',null,false,false)
        );
        return $response;
    }

    #[Route('/setUserCookie', name: 'app_setUserCookie')]
    public function setUserCookie(): Response
    {
        $response = $this->redirectToRoute('app_wineRed');
        $response->headers->setCookie(
            new Cookie('wineGameUserCookie', "user",0,'/',null,false,false)
        );
        return $response;
    }

    #[Route('/admin', name: 'app_admin')]
    public function admin(Request $request): Response
    {
        $response = new Response();
        $response->headers->clearCookie('wineGameUserCookie');
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $winegame = $this->checkWineGameCookie($request);
        if (!$winegame) {
            return $this->redirectToRoute('app_choseWineGame');
        }

        if (!$request->cookies->has('wineGameAdminCookie')) {
            return $this->redirectToRoute('app_index');
        }

        $form = $this->createForm(WineGameType::class, $winegame);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($winegame);
            $this->em->flush();
            $this->addFlash(
                'success',
                "Données mises à jour. Pour que les changements sur la bouteille soient effectifs, veuillez la redémarrer."
            );
            return $this->redirectToRoute('app_admin');
        }

        return $this->render('admin.html.twig', [
            'form' => $form,
            'wineGame' => $winegame
        ], $response);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/activateBottle/{id}', name: 'app_activateBottle')]
    public function activateBottle(WineGame $wineGame)
    {
        $wineGame->setBottleRing(1);
        $this->em->persist($wineGame);
        $this->em->flush();
        $this->addFlash(
            'success',
            "La bouteille va sonner."
        );
        return $this->redirectToRoute('app_admin');
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/activatePadlock/{id}', name: 'app_activatePadlock')]
    public function activatePadlock(WineGame $wineGame)
    {
        if($wineGame->isPadlockIsOpen()) {
            $wineGame->setPadlockIsOpen(0);
            $this->addFlash(
                'success',
                "Le cadenas reprend son comportement par défaut"
            );
        }else{
            $wineGame->setPadlockIsOpen(1);
            $this->addFlash(
                'success',
                "Le cadenas s'ouvre"
            );
        }
        $this->em->persist($wineGame);
        $this->em->flush();
        return $this->redirectToRoute('app_admin');
    }

    #[Route('/wineOrder', name: 'app_wineOrder')]
    public function wineOrder(Request $request): Response
    {
        $response = new Response();
        $response->headers->clearCookie('wineGameAdminCookie');
        $winegame = $this->checkWineGameCookie($request);
        if (!$winegame) {
            return $this->redirectToRoute('app_choseWineGame');
        }

        if (!$request->cookies->has('wineGameUserCookie')) {
            return $this->redirectToRoute('app_index');
        }

        return $this->render('wineOrder.html.twig', [
            'wineGame' => $winegame
        ], $response);
    }

    #[Route('/wineWhite', name: 'app_wineWhite')]
    public function wineWhite(Request $request): Response
    {
        $response = new Response();
        $response->headers->clearCookie('wineGameAdminCookie');
        $winegame = $this->checkWineGameCookie($request);
        if (!$winegame) {
            return $this->redirectToRoute('app_choseWineGame');
        }
        if (!$request->cookies->has('wineGameUserCookie')) {
            return $this->redirectToRoute('app_index');
        }
        return $this->render('wineWhite.html.twig',[] , $response);
    }

    #[Route('/winePink', name: 'app_winePink')]
    public function winePink(Request $request): Response
    {
        $response = new Response();
        $response->headers->clearCookie('wineGameAdminCookie');
        $winegame = $this->checkWineGameCookie($request);
        if (!$winegame) {
            return $this->redirectToRoute('app_choseWineGame');
        }
        if (!$request->cookies->has('wineGameUserCookie')) {
            return $this->redirectToRoute('app_index');
        }
        return $this->render('winePink.html.twig',[] , $response);
    }

    #[Route('/wineRed', name: 'app_wineRed')]
    public function wineRed(Request $request): Response
    {
        $response = new Response();
        $response->headers->clearCookie('wineGameAdminCookie');
        $winegame = $this->checkWineGameCookie($request);
        if (!$winegame) {
            return $this->redirectToRoute('app_choseWineGame');
        }
        if (!$request->cookies->has('wineGameUserCookie')) {
            return $this->redirectToRoute('app_index');
        }
        return $this->render('wineRed.html.twig',[] , $response);
    }
}
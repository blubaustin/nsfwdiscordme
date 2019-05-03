<?php
namespace App\Controller;

use App\Entity\Purchase;
use App\Entity\PurchasePeriod;
use App\Entity\Server;
use App\Event\ServerActionEvent;
use App\Http\Request;
use App\Services\PaymentService;
use DateTime;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route(name="upgrade_")
 */
class UpgradeController extends Controller
{
    const PRICES = [
        Server::STATUS_GOLD => [
            '30' => 1250
        ],
        Server::STATUS_PLATINUM => [
            '30' => 4500
        ]
    ];

    /**
     * @Route("/upgrade/server/{slug}", name="index", methods={"GET"})
     *
     * @param string  $slug
     *
     * @return Response
     * @throws Exception
     */
    public function indexAction($slug)
    {
        $server = $this->fetchServerOrThrow($slug);
        if (!$this->hasServerAccess($server, self::SERVER_ROLE_MANAGER)) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('upgrade/index.html.twig', [
            'server' => $server,
            'prices' => self::PRICES
        ]);
    }

    /**
     * @Route("/upgrade/server/{slug}", name="purchase", methods={"POST"})
     *
     * @param string         $slug
     * @param Request        $request
     * @param PaymentService $paymentService
     *
     * @return RedirectResponse
     * @throws GuzzleException
     * @throws Exception
     */
    public function purchaseAction($slug, Request $request, PaymentService $paymentService)
    {
        $server = $this->fetchServerOrThrow($slug);
        if (!$this->hasServerAccess($server, self::SERVER_ROLE_MANAGER)) {
            throw $this->createAccessDeniedException();
        }

        $premiumStatus = $request->request->get('premiumStatus');
        $period        = $request->request->get('period');
        if (empty($period) || empty(self::PRICES[$premiumStatus][$period])) {
            throw $this->createNotFoundException();
        }
        if (!isset(Server::STATUSES[$premiumStatus])) {
            throw $this->createNotFoundException();
        }
        if (!isset(self::PRICES[$premiumStatus][$period])) {
            throw $this->createNotFoundException();
        }

        $purchase = (new Purchase())
            ->setServer($server)
            ->setPeriod($period)
            ->setUser($this->getUser())
            ->setPremiumStatus(Server::STATUSES[$premiumStatus])
            ->setPrice(self::PRICES[$premiumStatus][$period])
            ->setStatus(Purchase::STATUS_PENDING);
        $this->em->persist($purchase);
        $this->em->flush();

        $description = sprintf('Purchasing premium server status for "%s".', $server->getName());

        $token = $paymentService->getToken([
            'price'         => self::PRICES[$premiumStatus][$period],
            'description'   => $description,
            'transactionID' => $purchase->getId(),
            'successURL'    => $this->generateUrl('upgrade_complete', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancelURL'     => $this->generateUrl('home_index'),
            'failureURL'    => $this->generateUrl('upgrade_failure', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'webhookURL'    => $this->generateUrl('upgrade_webhook', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return new RedirectResponse($paymentService->getPurchaseURL($token));
    }

    /**
     * @Route("/upgrade/complete", name="complete")
     *
     * @param Request $request
     *
     * @return Response
     * @throws Exception
     */
    public function completeAction(Request $request)
    {
        $token = $request->query->get('t');
        if (!$token) {
            throw $this->createNotFoundException();
        }

        $purchase = $this->em->getRepository(Purchase::class)->findByToken($token);
        if (!$purchase
            || $purchase->getStatus() !== Purchase::STATUS_SUCCESS
            || $purchase->getUser()->getId() !== $this->getUser()->getId()
        ) {
            throw $this->createNotFoundException();
        }

        $this->addFlash('success', 'Upgrade complete!');

        return $this->render('upgrade/complete.html.twig', [
            'purchase' => $purchase
        ]);
    }

    /**
     * @Route("/upgrade/failure", name="failure")
     */
    public function failureAction()
    {
        $this->addFlash('danger', 'Payment failure!');

        return $this->render('upgrade/failure.html.twig');
    }

    /**
     * @Route("/webhook", name="webhook", methods={"POST"})
     *
     * @param Request        $request
     * @param PaymentService $paymentService
     *
     * @return JsonResponse
     * @throws Exception
     * @throws GuzzleException
     */
    public function webhookAction(Request $request, PaymentService $paymentService)
    {
        $success       = $request->json->get('success');
        $token         = $request->json->get('token');
        $code          = $request->json->get('code');
        $price         = $request->json->get('price');
        $transactionID = $request->json->get('transactionID');
        if (!$token || !$code || !$transactionID || !$price) {
            return new JsonResponse(['ok'], 400);
        }

        $purchase = $this->em->getRepository(Purchase::class)->findByID($transactionID);
        if (!$purchase) {
            throw $this->createNotFoundException();
        }

        if (!$paymentService->verify($token, $code, $price, $transactionID)) {
            $purchase->setStatus(Purchase::STATUS_FAILURE);
            $this->em->flush();

            return new JsonResponse(['ok'], 401);
        }

        if (!$success) {
            $purchase->setStatus(Purchase::STATUS_FAILURE);
            $this->em->flush();

            return new JsonResponse(['ok']);
        }

        $server = $purchase->getServer();
        if (!$server || !$server->isEnabled()) {
            throw $this->createNotFoundException();
        }
        if (!$this->hasServerAccess($server, self::SERVER_ROLE_MANAGER, $purchase->getUser())) {
            throw $this->createAccessDeniedException();
        }

        $purchase
            ->setStatus(Purchase::STATUS_SUCCESS)
            ->setPurchaseToken($token);
        $purchasePeriod = (new PurchasePeriod())
            ->setPurchase($purchase);
        $this->em->persist($purchasePeriod);

        // Starts the premium status immediately when the server is not already
        // premium. Otherwise the cron job will upgrade the server status when
        // the existing premium status expires.
        if ($server->getPremiumStatus() === Server::STATUS_STANDARD) {
            $period = $purchase->getPeriod();
            $server->setPremiumStatus($purchase->getPremiumStatus());
            $purchasePeriod
                ->setDateBegins(new DateTime())
                ->setDateExpires(new DateTime("${period} days"));
        }

        $this->em->flush();

        $action = sprintf('Purchased premium server status %s.', Server::STATUSES_STR[$purchase->getPremiumStatus()]);
        $this->eventDispatcher->dispatch(
            'app.server.action',
            new ServerActionEvent($server, $purchase->getUser(), $action)
        );

        return new JsonResponse(['ok']);
    }
}

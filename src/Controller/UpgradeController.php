<?php
namespace App\Controller;

use App\Entity\Purchase;
use App\Entity\PurchasePeriod;
use App\Entity\Server;
use App\Entity\ServerAction;
use App\Event\ServerActionEvent;
use App\Http\Request;
use App\Services\PaymentService;
use DateTime;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
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
        'gold' => [
            '30' => 1250
        ],
        'platinum' => [
            '30' => 4500
        ]
    ];

    /**
     * @Route("/upgrade/server/{slug}", name="index", methods={"GET"})
     *
     * @param string  $slug
     *
     * @param Request $request
     *
     * @return Response
     * @throws Exception
     */
    public function indexAction($slug, Request $request)
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
     */
    public function purchaseAction($slug, Request $request, PaymentService $paymentService)
    {
        $server = $this->fetchServerOrThrow($slug);
        if (!$this->hasServerAccess($server, self::SERVER_ROLE_MANAGER)) {
            throw $this->createAccessDeniedException();
        }

        $status = $request->request->get('status');
        $period = $request->request->get('period');
        if (empty($status) || empty($period) || empty(self::PRICES[$status][$period])) {
            throw $this->createNotFoundException();
        }

        $url = $paymentService->getRedirectURL([
            'discordID'   => $server->getDiscordID(),
            'status'      => $status,
            'period'      => $period,
            'price'       => self::PRICES[$status][$period],
            'redirectURL' => $this->generateUrl('upgrade_complete', [], UrlGeneratorInterface::ABSOLUTE_URL)
        ]);

        return new RedirectResponse($url);
    }

    /**
     * @Route("/upgrade/complete", name="complete")
     *
     * @param Request        $request
     * @param PaymentService $paymentService
     *
     * @return Response
     * @throws Exception
     * @throws GuzzleException
     */
    public function completeAction(Request $request, PaymentService $paymentService)
    {
        $token = $request->query->get('token');
        $code  = $request->query->get('code');
        if (!$token || !$code) {
            throw $this->createNotFoundException();
        }

        $details = $paymentService->getDetails($token, $code);
        if (!$details['success']) {
            throw $this->createNotFoundException();
        }
        $server = $this->em->getRepository(Server::class)->findByDiscordID($details['discordID']);
        if (!$server || !$server->isEnabled()) {
            throw $this->createNotFoundException();
        }
        if (!$this->hasServerAccess($server, self::SERVER_ROLE_MANAGER)) {
            throw $this->createAccessDeniedException();
        }

        $purchase = $this->em->getRepository(Purchase::class)->findByPurchaseToken($token);
        if (!$purchase) {
            $price  = (int)$details['price'];
            $period = (int)$details['period'];
            $premiumStatus = array_search($details['status'], Server::STATUSES_STR);
            if (!$premiumStatus) {
                $this->logger->error(
                    sprintf('Received invalid premium status "%s" from purchase server.', $details['status'])
                );
                throw new Exception();
            }

            $purchase = (new Purchase())
                ->setServer($server)
                ->setPurchaseToken($token)
                ->setStatus($premiumStatus)
                ->setPrice($price)
                ->setPeriod($period);
            $this->em->persist($purchase);

            $purchasePeriod = (new PurchasePeriod())
                ->setPurchase($purchase);
            $this->em->persist($purchasePeriod);

            // Starts the premium status immediately when the server is not already
            // premium. Otherwise the cron job will upgrade the server status when
            // the existing premium status expires.
            if ($server->getPremiumStatus() === Server::STATUS_STANDARD) {
                $server->setPremiumStatus($premiumStatus);
                $purchasePeriod
                    ->setDateBegins(new DateTime())
                    ->setDateExpires(new DateTime("${period} days"));
            }

            $this->em->flush();

            $action = sprintf('Upgraded server to %s.', Server::STATUSES_STR[$premiumStatus]);
            $this->eventDispatcher->dispatch(
                'app.server.action',
                new ServerActionEvent($server, $this->getUser(), $action)
            );
        }

        $this->addFlash('success', 'Upgrade complete!');

        return $this->render('upgrade/complete.html.twig', [
            'server'   => $server,
            'purchase' => $purchase
        ]);
    }
}

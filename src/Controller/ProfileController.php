<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route(name="profile_")
 */
class ProfileController extends Controller
{
    /**
     * @Route("/profile", name="index")
     *
     * @return Response
     */
    public function indexAction()
    {
        $servers = [
            [
                'slug'        => 'helldc',
                'name'        => 'Hell',
                'url'         => $this->generateUrl('server_index', ['slug' => 'helldc'], UrlGeneratorInterface::ABSOLUTE_URL),
                'icon'        => 'https://cdn.discord.me/server/c2ececb27c879cfe539bea0c21257b42d07c62f68e39dd63e221eed43226b876/icon_7a2e6ba50e95cc4dd0cdad7267cf3b92742b51c5c45c1e1811dda62d1562abb0.jpg',
                'banner'      => 'https://cdn.discord.me/server/c2ececb27c879cfe539bea0c21257b42d07c62f68e39dd63e221eed43226b876/block_a915205a89e779c495af6779a0696b870798fbf3a377c3cf71f3d775ea59f9ce.jpg',
                'bumpPoints'  => 66,
                'bumpNext'    => '0h 21m 55s'
            ],
            [
                'slug'        => 'fox',
                'name'        => 'The Fox Den',
                'url'         => $this->generateUrl('server_index', ['slug' => 'fox'], UrlGeneratorInterface::ABSOLUTE_URL),
                'icon'        => 'https://cdn.discord.me/server/541f3b62e8e9fcf817d1b64f8ed51a377f983c7fa71e384470bf8389d8ffdd72/icon_0c537b6bd7c3fd889f236fc8f12c0f6f7a7e946a8c3268288d9126c9057b1f13.jpg',
                'banner'      => 'https://cdn.discord.me/server/541f3b62e8e9fcf817d1b64f8ed51a377f983c7fa71e384470bf8389d8ffdd72/block_0c537b6bd7c3fd889f236fc8f12c0f6f7a7e946a8c3268288d9126c9057b1f13.jpg',
                'bumpPoints'  => 100,
                'bumpNext'    => '0h 21m 55s'
            ],
            [
                'slug'        => 'a-ss',
                'name'        => 'Anti-Social Society',
                'url'         => $this->generateUrl('server_index', ['slug' => 'a-ss'], UrlGeneratorInterface::ABSOLUTE_URL),
                'icon'        => 'https://cdn.discord.me/server/9efa9c18d0448b18665f11bfa41160d2b9e6c6f1eb760e679a66f43d69ef95db/icon_9e4d03dc41e673666d4a6144a776d84a37b88f5f39fc0f3eb3d879aad0e20436.jpg',
                'banner'      => 'https://cdn.discord.me/server/9efa9c18d0448b18665f11bfa41160d2b9e6c6f1eb760e679a66f43d69ef95db/block_db1d633b9cf49906690c33c3f58a23a0a8720a654f5144bf899c537526b83a48.jpg',
                'bumpPoints'  => 120,
                'bumpNext'    => '0h 21m 55s'
            ]
        ];

        return $this->render('profile/index.html.twig', [
            'servers' => $servers
        ]);
    }

    /**
     * @Route("/profile/icons", name="icons")
     *
     * @return Response
     */
    public function iconsAction()
    {
        return $this->render('profile/index.html.twig');
    }
}

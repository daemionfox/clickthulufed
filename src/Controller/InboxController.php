<?php

namespace App\Controller;

use ActivityPhp\Server;
use ActivityPhp\Server\Http\HttpSignature;
use App\Actions\ActivityAbstract;
use App\Actions\ActivityFactory;
use App\Helpers\SignatureHelper;
use App\Service\Settings;
use App\Traits\APServerTrait;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InboxController extends AbstractController
{
    use APServerTrait;




    #[Route(
        '/@{ident}/inbox',
        name: 'app_apinbox'
    )]   // condition: "request.headers.get('Accept') matches '/application\\\\/activity\\\\+json/i'",
    public function inbox(Request $request, EntityManagerInterface $entityManager, Settings $settings, LoggerInterface $logger, string $ident): Response
    {
        $logger->debug(__CLASS__ . "::" . __METHOD__ . " - Received POST to Inbox");
        $body = $request->toArray();

        // Call the buildAPServer call from the trait to create something to validate the code
        $server = $this->_buildAPServer($settings);
        $actor = $server->actor($body['actor']);

        // This piece is failing to verify signatures.  ?????

        $signature = new HttpSignature($server);

        $newServ = new Server([
            'instance' => [
                'types' => 'ignore',
            ],
        ]);
        $sig2 = new HttpSignature($newServ);

        $isValidLandrok = $signature->verify($request);
        $isValidLandrok2 = $sig2->verify($request);
        $isValidHome = SignatureHelper::validate($actor->get(), $request);

        $foo = 'bar';
return new JsonResponse(['status' => 'success']);
        /**
         * @var ActivityAbstract $function;
         */
        $activityFactory = new ActivityFactory($entityManager, $settings, $ident);
        $function = $activityFactory->create($body);
        $function->run($body);




        // x Follow
        // x Undo Follow
        // Post Reply
        // Boost
        // Reply to Reply
        // Unboost
        // delete reply





        return new JsonResponse(['status' => 'success']);
    }


}
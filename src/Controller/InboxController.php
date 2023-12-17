<?php

namespace App\Controller;

use ActivityPhp\Server\Http\HttpSignature;
use App\Service\Settings;
use App\Traits\APServerTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InboxController
{
    use APServerTrait;






    protected function validateSignature($headers)
    {
//        dd($headers);
        return true;
    }

}
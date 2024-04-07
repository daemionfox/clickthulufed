<?php

namespace App\Service;

use App\Entity\LogEntry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class AuditLog
{
    const LEVEL_EMERGENCY = 'emergency';
    const LEVEL_ALERT = 'alert';
    const LEVEL_CRITICAL = 'critical';
    const LEVEL_ERROR = 'error';
    const LEVEL_WARNING = 'warning';
    const LEVEL_NOTICE = 'notice';
    const LEVEL_INFO = 'info';
    const LEVEL_DEBUG = 'debug';

    protected EntityManagerInterface $entityManager;
    protected RequestStack $requestStack;

    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack) {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
    }

    public function emergency(mixed $message): void
    {
        $this->addLog(self::LEVEL_EMERGENCY, $message);
    }

    public function alert(mixed $message): void
    {
        $this->addLog(self::LEVEL_ALERT, $message);
    }

    public function critical(mixed $message): void
    {
        $this->addLog(self::LEVEL_CRITICAL, $message);
    }

    public function error(mixed $message): void
    {
        $this->addLog(self::LEVEL_ERROR, $message);
    }

    public function warning(mixed $message): void
    {
        $this->addLog(self::LEVEL_WARNING, $message);
    }

    public function notice(mixed $message): void
    {
        $this->addLog(self::LEVEL_NOTICE, $message);
    }

    public function info(mixed $message): void
    {
        $this->addLog(self::LEVEL_INFO, $message);
    }

    public function debug(mixed $message): void
    {
        $this->addLog(self::LEVEL_DEBUG, $message);
    }

    public function addLog(string $level, mixed $message): void
    {
        $type = 'string';
        if (is_array($message) || is_object($message)) {
            $type = 'json';
            $message = json_encode($message);
        } elseif (is_numeric($message)) {
            $type = 'number';
        } elseif (is_bool($message)) {
            $type = 'boolean';
            $message = $message ? 'true' : 'false';
        } elseif (is_null($message)) {
            $type = 'null';
            $message = 'null';
        }

        $log = new LogEntry();
        $log
            ->setType($type)
            ->setRoute($this->requestStack->getCurrentRequest()->get('_route'))
            ->setBody($message)
            ->setLevel($level);

        $this->entityManager->persist($log);

        $yesterday = strtotime('yesterday midnight');
        $lweek = $yesterday - 7 * 86400;

//        $olderLogs = $this->entityManager
//                        ->getRepository(LogEntry::class)
//                        ->createQueryBuilder('ol')
//                        ->select('ol')
//                        ->where('ol.createdat BETWEEN :yday AND :lweek')
//                        ->setParameter('yday', date('Y-m-d h:i:s', $yesterday))
//                        ->setParameter('lweek', date('Y-m-d h:i:s', $lweek))
//                        ->getQuery()
//                        ->getArrayResult();

        $this->entityManager->flush();
    }

}
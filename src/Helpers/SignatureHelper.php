<?php

namespace App\Helpers;

use ActivityPhp\Type\Extended\AbstractActor;
use App\Exceptions\SignatureValidationException;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;

class SignatureHelper
{
    public static function validate(AbstractActor $actor, Request $request): bool
    {
        /**
         * @var HeaderBag $headers
         */
        $headers = $request->headers;
        $signature = $headers->get('signature');
        $signatureParts = explode(',', $signature);
        $components = [];
        foreach ($signatureParts as $part) {
            @list($key, $value) = explode('=', $part, 2);
            $key = trim($key);
            $components[$key] = trim($value, "\ \t\n\r\0\x0B\"'");
        }

        if (empty($components['headers'])) {
            throw new SignatureValidationException("Expected header components missing.  Cannot validate signature");
        }

        $publicKey = $actor->get('publicKey');

        $headerlist = explode(' ', $components['headers']);
        $keyPath = $publicKey['id'];
        $keyOwner = $publicKey['owner'];
        $keyPem = $publicKey['publicKeyPem'];

        $expectedHeaders = [];
        foreach ($headerlist as $item) {
            if ($item === '(request-target)') {
                $expectedHeaders[] = "(request-target): post {$keyPath}";
            } else {
                $expectedHeaders[] = "{$item}: {$headers->get($item)}";
            }
        }

        $encodeStr = implode("\n", $expectedHeaders);
        $verifier = openssl_get_publickey($keyPem);
        $validate = openssl_verify($encodeStr, base64_decode($components['signature']), $verifier);

        return $validate === 1;

    }
}
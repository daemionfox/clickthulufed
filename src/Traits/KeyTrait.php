<?php

namespace App\Traits;

use JetBrains\PhpStorm\ArrayShape;

trait KeyTrait
{

    #[ArrayShape(['public' => "mixed", 'private' => ""])]
    public function _generateKeyPair(): array
    {
        $config = [
            "digest_alg" => "sha512",
            "private_key_bits" => 4096,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];

        $result = openssl_pkey_new($config);


        openssl_pkey_export($result, $privateKey);


        $publicKey = openssl_pkey_get_details($result)["key"];

        return [
            'public' => $publicKey,
            'private' => $privateKey
        ];

    }

}
<?php

namespace App\Helpers;

use Symfony\Component\HttpFoundation\ParameterBag;

class CryptoHelper
{
    private string $piikey;
    private string $cipher = 'AES-128-CBC';
    private bool|int $iv_length;
    private bool|string $encryption_key;

    public function __construct()
    {
        $this->piikey = $parameterBag->get('piikey');

    }

    /**
     * @param string $data
     * @return string
     */
    public function encrypt(string $data): string
    {
        $ivLength = openssl_cipher_iv_length($this->cipher);
        $iv = openssl_random_pseudo_bytes($ivLength);
        $ciphertext_raw = openssl_encrypt($data, $this->cipher, $this->piikey, $options=OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, $this->piikey, $as_binary = true);
        return base64_encode( $iv.$hmac.$ciphertext_raw);
    }

    public function decrypt(string $encodedData): string
    {
        $c = base64_decode($encodedData);
        $ivLen = openssl_cipher_iv_length($this->cipher);
        $iv = substr($c, 0, $ivLen);
        $hmac = substr($c, $ivLen, $sha2len=32);
        $ciphertext_raw = substr($c, $ivLen+$sha2len);
        return openssl_decrypt($ciphertext_raw, $this->cipher, $this->piikey, $options=OPENSSL_RAW_DATA, $iv);
    }

    public function hash(string $data): string
    {
        $hexString = unpack('H*', $data);
        $hex = array_shift($hexString);
        return base64_encode($hex);
    }

    public function unhash(string $encodedData): string
    {
        return hex2bin(base64_decode($encodedData));
    }



}
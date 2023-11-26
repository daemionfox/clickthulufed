<?php

namespace App\Doctrine;

use App\Entity\CryptKey;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class EncryptFieldListener implements EventSubscriber
{

    private string $piikey;
    private string $cipher = 'AES-128-CBC';

    public function __construct(string $piikey)
    {
        $this->piikey = $piikey;
    }

    public function getSubscribedEvents()
    {
        return ['prePersist', 'preUpdate', 'postLoad'];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $cryptKey = $args->getObject();
        if (!is_a($cryptKey, CryptKey::class)) {
            return;
        }
        $data = $cryptKey->getData();
        $enc = $this->encrypt($data);
        $cryptKey->setData($enc);
   }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $cryptKey = $args->getObject();
        if (!is_a($cryptKey, CryptKey::class)) {
            return;
        }
        $data = $cryptKey->getData();
        $enc = $this->encrypt($data);
        $cryptKey->setData($enc);
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $cryptKey = $args->getObject();
        if (!is_a($cryptKey, CryptKey::class)) {
            return;
        }
        $data = $cryptKey->getData();
        $dec = $this->decrypt($data);
        $cryptKey->setData($dec);
    }

    /**
     * @param string $data
     * @return string
     */
    private function encrypt(string $data): string
    {
        $ivLength = openssl_cipher_iv_length($this->cipher);
        $iv = openssl_random_pseudo_bytes($ivLength);
        $ciphertext_raw = openssl_encrypt($data, $this->cipher, $this->piikey, $options=OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, $this->piikey, $as_binary = true);
        return base64_encode( $iv.$hmac.$ciphertext_raw);
    }

    /**
     * @param string $encodedData
     * @return string
     */
    private function decrypt(string $encodedData): string
    {
        $c = base64_decode($encodedData);
        $ivLen = openssl_cipher_iv_length($this->cipher);
        $iv = substr($c, 0, $ivLen);
        $hmac = substr($c, $ivLen, $sha2len=32);
        $ciphertext_raw = substr($c, $ivLen+$sha2len);
        return openssl_decrypt($ciphertext_raw, $this->cipher, $this->piikey, $options=OPENSSL_RAW_DATA, $iv);
    }

}
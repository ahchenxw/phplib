<?php
namespace com\chenxw\security;

/**
 * RSA加密方法
 * User: ahchenxw@126.com
 * Date: 2017/5/23
 * Time: 9:54
 */
class RSA
{
    private $publicKey;
    private $privateKey;
    private $padding;

    /**
     * RSA constructor.
     * @param string $publicKey
     * @param string $privateKey
     * @param int $padding
     */
    public function __construct($publicKey, $privateKey, $padding = OPENSSL_PKCS1_PADDING)
    {
        $this->publicKey = $this->initPublicKey($publicKey);
        $this->privateKey = $this->initPrivateKey($privateKey);
        $this->padding = $padding;
    }

    /**
     * 使用私钥加密
     * @param $data
     * @return null|string
     */
    public function privateKeyEncrypt($data)
    {
        $suc = openssl_private_encrypt($data, $encrypted, $this->privateKey, $this->padding);
        return $suc ? base64_encode($encrypted) : null;
    }

    /**
     * 使用私钥解密
     * @param $data
     * @return null|string
     */
    public function privateKeyDecrypt($data)
    {
        $suc = openssl_private_decrypt(base64_decode($data), $decrypted, $this->privateKey, $this->padding);
        return $suc ? $decrypted : null;
    }

    /**
     * 使用公钥加密
     * @param string $data
     * @return null|string
     */
    public function publicKeyEncrypt($data)
    {
        $suc = openssl_public_encrypt($data, $encrypted, $this->publicKey, $this->padding);
        return $suc ? base64_encode($encrypted) : null;
    }

    /**
     * 使用公钥解密
     * @param $data
     * @return null|string
     */
    public function publicKeyDecrypt($data)
    {
        $suc = openssl_public_decrypt(base64_decode($data), $decrypted, $this->publicKey,$this->padding);
        return $suc ? $decrypted : null;
    }

    /**
     * 使用私钥生成签名
     * @param $data
     * @param int $signature_alg
     * @return string
     */
    public function sign($data, $signature_alg = OPENSSL_ALGO_SHA1)
    {
        openssl_sign($data, $signature, $this->privateKey, $signature_alg);
        return base64_encode($signature);
    }

    /**
     * 使用公钥验证签名
     * @param $data
     * @param $signature
     * @param int $signature_alg
     * @return int
     */
    public function verify($data, $signature, $signature_alg = OPENSSL_ALGO_SHA1)
    {
        return openssl_verify($data, base64_decode($signature), $this->publicKey, $signature_alg);
    }

    /**
     * 初始化公钥
     * @param $publicKey
     * @return resource
     */
    private function initPublicKey($publicKey)
    {
        $pem = chunk_split($publicKey, 64, "\n");
        $pem = "-----BEGIN PUBLIC KEY-----\n" . $pem . "-----END PUBLIC KEY-----\n";
        return openssl_pkey_get_public($pem);
    }

    /**
     * 初始化私钥
     * @param string $privateKey
     * @return bool|resource
     */
    private function initPrivateKey($privateKey)
    {
        $pem = chunk_split($privateKey, 64, "\n");
        $pem = "-----BEGIN PRIVATE KEY-----\n" . $pem . "-----END PRIVATE KEY-----\n";
        return openssl_pkey_get_private($pem);
    }

    /**
     * 释放公钥和私钥
     */
    public function __destruct()
    {
        @openssl_free_key($this->publicKey);
        @openssl_free_key($this->privateKey);
    }
}

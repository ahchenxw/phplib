<?php
namespace com\chenxw\security;

/**
 * 加密数据填充类型
 */
define('DES3_PADDING_ZERO', 'zero');
define('DES3_PADDING_PKCS5', 'pkcs5');
define('DES3_PADDING_PKCS7', 'pkcs7');
define('DES3_PADDING_ISO10126', 'iso10126');
define('DES3_PADDING_ANSIX923', 'ansix923');

/**
 * HTTP请求封装，支持通用的GET，POST
 * User: ahchenxw@126.com
 * Date: 2017/5/23
 * Time: 9:54
 */
class DES3
{
    private $key;
    private $iv;
    private $mode;//加密模式
    private $padding;//填充类型

    /**
     * DES3 constructor.
     * @param string $key
     * @param string $iv
     * @param string $mode
     * @param string $padding
     */
    public function __construct($key, $iv, $mode = MCRYPT_MODE_ECB, $padding = DES3_PADDING_ZERO)
    {
        $this->key = $key;
        $this->iv = $iv;
        $this->mode = $mode;
        $this->padding = $padding;
    }

    /**
     * 3DES加密
     * @param $data
     * @return string
     */
    public function encrypt($data)
    {
        $td = mcrypt_module_open(MCRYPT_3DES, '', $this->mode, '');
        mcrypt_generic_init($td, $this->key, $this->iv);

        $size = mcrypt_get_block_size(MCRYPT_3DES, $this->mode);
        switch ($this->padding) {
            case DES3_PADDING_PKCS5:
                $data = $this->pkcs5Pad($data, $size);
                break;
            case DES3_PADDING_PKCS7:
                $data = $this->pkcs7Pad($data, $size);
                break;
            case DES3_PADDING_ISO10126:
                break;
            case DES3_PADDING_ANSIX923:
                break;
        }
        $res = base64_encode(mcrypt_generic($td, $data));
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return $res;
    }

    /**
     * 3DES解密
     * @param $data
     * @return string
     */
    public function decrypt($data)
    {
        $td = mcrypt_module_open(MCRYPT_3DES, '', $this->mode, '');
        mcrypt_generic_init($td, $this->key, $this->iv);
        $data = mdecrypt_generic($td, base64_decode($data));
        switch ($this->padding) {
            case DES3_PADDING_PKCS5:
                $data = $this->pkcs5UnPad($data);
                break;
            case DES3_PADDING_PKCS7:
                $data = $this->pkcs7UnPad($data);
                break;
            case DES3_PADDING_ISO10126:
                break;
            case DES3_PADDING_ANSIX923:
                break;
        }
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return $data;
    }

    private function pkcs5Pad($data, $size)
    {
        $pad = $size - (strlen($data) % $size);
        return $data . str_repeat(chr($pad), $pad);
    }

    private function pkcs5UnPad($data)
    {
        $len = strlen($data);
        $pad = ord($data[$len - 1]);
        if ($pad <= $len && strspn($data, chr($pad), $len - $pad) == $pad) {
            return substr($data, 0, -1 * $pad);
        }
        return false;
    }

    private function pkcs7Pad($data, $size)
    {
        $pad = $size - strlen($data) % $size;
        if ($pad <= $size) {
            $data .= str_repeat(chr($pad), $pad);
        }
        return $data;
    }

    private function pkcs7UnPad($data)
    {
        $pad = ord(substr($data, (strlen($data) - 1), 1));
        for ($i = -1 * ($pad - strlen($data)); $i < strlen($data); $i++) {
            if (ord(substr($data, $i, 1)) != $pad){
                return false;
            }
        }
        return substr($data, 0, -1 * ($pad - strlen($data)));
    }

}

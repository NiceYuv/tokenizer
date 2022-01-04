<?php
declare(strict_types=1);
namespace NiceYuv;

use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;

class Tokenizer
{
    /**
     * Enable persistent tokens
     * When enabled, allows the temporary token to be refreshed
     */
    private bool $way = false;

    /**
     * Whether the persistent token is allowed to be refreshed
     */
    private bool $refresh = false;

    /**
     * Temporary token validity
     */
    private string $expireDate = "+1 day";

    /**
     * Long token validity
     */
    private string $extendDate = '+7 day';

    /**
     * Encrypted information secret
     */
    private string $secret = '8a8b57b12684504f511e85ad5073d1b2b430d143a';

    private DES $encryptor;

    /**
     * @return DES
     */
    private function build(): DES
    {
        $this->encryptor = new DES($this->secret, 'DES-ECB', DES::OUTPUT_BASE64);
        return $this->encryptor;
    }

    /**
     * generate token info
     * @param string $uid  user id
     * @param string $platform  platform[web|android|ios|h5|pc]
     * @return TokenizerDto
     */
    public function generate(string $uid, string $platform = "web"): TokenizerDto
    {
        $ser = SerializerBuilder::create()->build();

        /** generate token */
        $extendDto = '';
        if ($this->way){
            $extendDto = $this->setupDtoDate($uid,$platform,$ser, true);
        }
        $tokenDto = $this->setupDtoDate($uid,$platform,$ser);

        /** return */
        $tokenizer = new TokenizerDto();
        $tokenizer->token = $tokenDto;
        $tokenizer->extend = $extendDto;
        return $tokenizer;
    }

    /**
     * setup dto info
     * @param string $uid
     * @param string $platform
     * @param Serializer $ser
     * @param bool $long
     * @return string
     */
    private function setupDtoDate(
        string $uid,
        string $platform,
        Serializer $ser,
        bool $long = false
    ): string
    {
        $classDto = new TokenDto();
        /** setup public info */
        $classDto->id = $uid;
        $classDto->platform = $platform;

        if ($long){
            $classDto->refresh = $this->refresh;
            $classDto->expireTime = strtotime($this->extendDate);
        } else {
            $classDto->expireTime = strtotime($this->expireDate);
        }
        return $this->build()->encrypt($ser->serialize($classDto, 'json'));
    }

    /**
     * verify Token
     * @param string $token
     * @return null|TokenDto
     */
    public function verify(string $token): ?TokenDto
    {
        $ser  = SerializerBuilder::create()->build();
        $data = $this->build()->decrypt($token);
        $obj  = $ser->deserialize($data, TokenDto::class, 'json');

        /** Verification expiration time */
        if (intval($obj->expireTime) < time()) {
            return null;
        }
        return $obj;
    }

    /**
     * refresh token
     * @param string $extend
     * @return null|TokenizerDto
     */
    public function refreshToken(string $extend): ?TokenizerDto
    {
        $ser = SerializerBuilder::create()->build();
        $data = $this->build()->decrypt($extend);
        $obj = $ser->deserialize($data, TokenDto::class, 'json');

        /** Identify token information */
        if (is_null($obj->refresh)){
            return null;
        }

        /** Verification expiration time */
        if (intval($obj->expireTime) < time()) {
            return null;
        }

        /** create  token */
        $token = $this->setupDtoDate($obj->id,$obj->platform,$ser);

        /** return  */
        $tokenizer = new TokenizerDto();
        $tokenizer->token = $token;
        $tokenizer->extend = $extend;
        return  $tokenizer;
    }

    /**
     * refresh extend
     * @param string $extend
     * @return string|null
     */
    public function refreshExtend(string $extend): ?string
    {
        $ser = SerializerBuilder::create()->build();
        $data = $this->encryptor->decrypt($extend);
        $obj = $ser->deserialize($data, TokenDto::class, 'json');

        /** Identify token information */
        if (is_null($obj->refresh) || $obj->refresh == false){
            return null;
        }

        /** Verification expiration time */
        if (intval($obj->expireTime) < time()) {
            return null;
        }

        return $this->setupDtoDate($obj->id,$obj->platform,$ser,true);
    }

    /**
     * @param bool $way
     */
    public function setWay(bool $way): void
    {
        $this->way = $way;
    }

    /**
     * @param bool $refresh
     */
    public function setRefresh(bool $refresh): void
    {
        $this->refresh = $refresh;
    }

    /**
     * @param string $expireDate
     */
    public function setExpireDate(string $expireDate): void
    {
        $this->expireDate = $expireDate;
    }

    /**
     * @param string $extendDate
     */
    public function setExtendDate(string $extendDate): void
    {
        $this->extendDate = $extendDate;
    }

    /**
     * @param string $secret
     */
    public function setSecret(string $secret): void
    {
        $this->secret = $secret;
    }

}
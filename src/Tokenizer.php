<?php
declare(strict_types=1);
namespace NiceYuv;

use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;

class Tokenizer
{

    private bool $way = false;

    private bool $refresh = false;

    private string $expireDate = "+1 day";

    private string $extendDate = '+7 day';

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
            $extendDto = $this->setupDtoDate(new ExtendDto(),$uid,$platform,$ser);
        }
        $tokenDto = $this->setupDtoDate(new TokenDto(),$uid,$platform,$ser);

        /** return */
        $tokenizer = new TokenizerDto();
        $tokenizer->token = $tokenDto;
        $tokenizer->extend = $extendDto;
        return $tokenizer;
    }

    /**
     * setup dto info
     * @param $classDto
     * @param string $uid
     * @param string $platform
     * @param Serializer $ser
     * @return string
     */
    private function setupDtoDate(
        $classDto,
        string $uid,
        string $platform,
        Serializer $ser
    ): string
    {
        /** setup public info */
        $classDto->id = $uid;
        $classDto->platform = $platform;

        if ($classDto instanceof ExtendDto){
            $classDto->refresh = $this->refresh;
            $classDto->extendTime = strtotime($this->extendDate);
        }

        if ($classDto instanceof TokenDto){
            $classDto->expireTime = strtotime($this->expireDate);
        }
        return $this->build()->encrypt($ser->serialize($classDto, 'json'));
    }

    /**
     * verify Token
     * @param string $token
     * @return bool
     */
    public function verify(string $token): bool
    {
        $ser = SerializerBuilder::create()->build();
        $data = $this->build()->decrypt($token);
        $obj = $ser->deserialize($data, TokenDto::class, 'json');

        /** If expireDate does not exist, then ExtendDto */
        if (!isset($obj->expireTime)){
            return false;
        }

        /** Verification expiration time */
        if (intval($obj->expireTime) < time()) {
            return false;
        }
        return true;
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
        $obj = $ser->deserialize($data, ExtendDto::class, 'json');

        /** If extendTime does not exist, then TokenDto */
        if (!isset($obj->extendTime)){
            return null;
        }

        /** create  token */
        $token = $this->setupDtoDate(new TokenDto(),$obj->id,$obj->platform,$ser);

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
        $obj = $ser->deserialize($data, ExtendDto::class, 'json');

        /** If extendTime does not exist, then TokenDto */
        if (!isset($obj->extendTime)){
            return null;
        }

        /** Not available for refresh operation */
        if (!$obj->refresh){
            return null;
        }

        return $this->setupDtoDate(new ExtendDto(),$obj->id,$obj->platform,$ser);
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
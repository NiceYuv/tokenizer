<?php
declare(strict_types=1);
namespace NiceYuv;

use DateTime;
use DateTimeZone;
use Exception;
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
     * Set your time zone
     */
    private string $dateTimeZone = 'Asia/Shanghai';

    /**
     * Encrypted information secret
     */
    private string $secret = '8a8b57b12684504f511e85ad5073d1b2b430d143a';

    /**
     * @return DES
     * @throws Exception
     */
    private function build(): DES
    {
        return new DES($this->secret, 'DES-ECB', DES::OUTPUT_BASE64);
    }

    /**
     * setup dateTime zone
     * @param string $day
     * @return DateTime
     * @throws Exception
     */
    public function setDateTime(string $day = ''): DateTime
    {
        $dateTime = new DateTime($day);
        $timeZone = new DateTimeZone($this->dateTimeZone);
        return $dateTime->setTimezone($timeZone);
    }

    /**
     * generate token info
     * @param string $uid user id
     * @param string $platform platform[web|android|ios|h5|pc]
     * @return TokenizerDto
     * @throws Exception
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
        $tokenizer->login_date  = $this->setDateTime();
        $tokenizer->expire_date = $this->setDateTime($this->expireDate);
        return $tokenizer;
    }

    /**
     * setup dto info
     * @param string $uid
     * @param string $platform
     * @param Serializer $ser
     * @param bool $long
     * @return string
     * @throws Exception
     */
    private function setupDtoDate(
        string $uid,
        string $platform,
        Serializer $ser,
        bool $long = false
    ): string
    {
        $buildTokenizer = $this->build();
        $classDto = new TokenDto();

        /** setup public info */
        $classDto->id = $uid;
        $classDto->platform = $platform;

        if ($long){
            $classDto->refresh = $this->refresh;
            $classDto->expireTime = $this->setDateTime($this->extendDate);
        } else {
            $classDto->expireTime = $this->setDateTime($this->expireDate);
        }
        return $buildTokenizer->encrypt($ser->serialize($classDto, 'json'));
    }

    /**
     * verify Token
     * @param string $token
     * @return null|TokenDto
     * @throws Exception
     */
    public function verify(string $token): ?TokenDto
    {
        $ser  = SerializerBuilder::create()->build();
        $data = $this->build()->decrypt($token);
        $obj  = $ser->deserialize($data, TokenDto::class, 'json');

        /**
         * Verification expiration time
         * @var TokenDto $obj
         * {id: int, refresh: ?bool, platform: string, expireTime: \DateTime }
         */
        if ($obj->expireTime->getTimestamp() < time()) {
            return null;
        }
        return $obj;
    }

    /**
     * refresh token
     * @param string $extend
     * @return null|TokenizerDto
     * @throws Exception
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

        /**
         * Verification expiration time
         * @var TokenDto $obj
         * {id: int, refresh: ?bool, platform: string, expireTime: \DateTime }
         */
        if ($obj->expireTime->getTimestamp() < time()) {
            return null;
        }

        /** create  token */
        $token = $this->setupDtoDate($obj->id,$obj->platform,$ser);

        /** return  */
        $tokenizer = new TokenizerDto();
        $tokenizer->token = $token;
        $tokenizer->extend = $extend;
        $tokenizer->login_date  = $this->setDateTime();
        $tokenizer->expire_date = $this->setDateTime($this->expireDate);
        return  $tokenizer;
    }

    /**
     * refresh extend
     * @param string $extend
     * @return TokenizerDto|null
     * @throws Exception
     */
    public function refreshExtend(string $extend): ?TokenizerDto
    {
        $ser = SerializerBuilder::create()->build();
        $data = $this->build()->decrypt($extend);
        $obj = $ser->deserialize($data, TokenDto::class, 'json');

        /** Identify token information */
        if (is_null($obj->refresh) || $obj->refresh == false){
            return null;
        }

        /**
         * Verification expiration time
         * @var TokenDto $obj
         * {id: int, refresh: ?bool, platform: string, expireTime: \DateTime }
         */
        if ($obj->expireTime->getTimestamp() < time()) {
            return null;
        }

        return $this->generate($obj->id,$obj->platform);
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

    /**
     * @param string $dateTimeZone
     */
    public function setDateTimeZone(string $dateTimeZone): void
    {
        $this->dateTimeZone = $dateTimeZone;
    }
}
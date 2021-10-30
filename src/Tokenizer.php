<?php
declare(strict_types=1);
namespace NiceYuv;

use Exception;
use JMS\Serializer\SerializerBuilder;

class Tokenizer
{
    
    private bool $way = true;
    
    private string $ttl = "+1 day";
    
    private string $effectiveDay = '+15 day';
    
    private string $secret = 'dbcb8a8b57b12684504f511e85ad5073430d143a';
    
    private DES $encryptor;
    
    public function __construct()
    {
        $this->encryptor = new DES($this->secret, 'DES-ECB', DES::OUTPUT_BASE64);
    }
    
    /**
     * generate token info
     * @param string $uid
     * @param string $platform
     * @return TokenizerDto
     */
    public function generate(string $uid, string $platform = "web"): TokenizerDto
    {
        $ser = SerializerBuilder::create()->build();
        
        /** generate  maximum */
        $maximum = $this->dto(new MaximumDto(),$uid,$platform,$this->effectiveDay);
        $maximum = $this->encryptor->encrypt($ser->serialize($maximum, 'json'));
    
        /** generate  token */
        $token = $this->dto(new TokenDto(),$uid,$platform,$this->ttl);
        $token = $this->encryptor->encrypt($ser->serialize($token, 'json'));
        
        /** return  */
        $tokenizer = new TokenizerDto();
        $tokenizer->token = $token;
        $tokenizer->maximum = $maximum;
        return $tokenizer;
    }
    
    /**
     * verify Token
     * @param string $token
     * @param bool $mode
     * @return false|TokenDto|MaximumDto
     */
    public function verify(string $token,bool $mode = true)
    {
        try {
            $ser = SerializerBuilder::create()->build();
            $data = $this->encryptor->decrypt($token);
            if ($mode){
                /**
                 * @var TokenDto $obj
                 */
                $obj = $ser->deserialize($data, TokenDto::class, 'json');
            }else{
                /**
                 * @var MaximumDto $obj
                 */
                $obj = $ser->deserialize($data, MaximumDto::class, 'json');
            }
            if (strtotime($obj->effectiveDate) < time()) {
                return false;
            }
            return $obj;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * updated token
     * @param string $maximum
     * @return null|TokenizerDto
     * @throws Exception
     */
    public function update(string $maximum): ?TokenizerDto
    {
        $content = $this->verify($maximum,false);
    
        $ser = SerializerBuilder::create()->build();
        
        /** error */
        if ($content->way === null){
            throw new Exception('You need to pass in a value of $maximum instead of $token');
        }
        if (!$content->way){
            return null;
        }
    
        /** create  token */
        $token = $this->dto(new TokenDto(),$content->id,$content->platform,$this->ttl);
        $token = $this->encryptor->encrypt($ser->serialize($token, 'json'));
    
        /** return  */
        $tokenizer = new TokenizerDto();
        $tokenizer->token = $token;
        $tokenizer->maximum = $maximum;
        return  $tokenizer;
    }
    
    
    
    /**
     * @param $dto
     * @param string $uid
     * @param string $platform
     * @param string $ttl
     * @return mixed|MaximumDto
     */
    private function dto($dto,string $uid, string $platform, string $ttl)
    {
        if ($dto instanceof MaximumDto){
            $dto->way   = $this->way;
        }
        
        $dto->id        = $uid;
        $dto->platform  = $platform;
        $dto->generateDate  = date('Y-m-d H:i:s',time());
        $dto->effectiveDate = date('Y-m-d H:i:s', strtotime($ttl));
        return $dto;
    }
    
}
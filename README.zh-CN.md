> [简体中文](README.zh-CN.md) | [English](README.md)
> ### 背景
> 经常会使用到的token令牌解决方案
> 一次会生成两面令牌
> 
> 一个是临时令牌 `token` ，用于当前使用，一般时间为 `1+` 天。
> 此令牌可以在 `1+` 天内随意使用
> 
> 一个是长时间令牌 `maximum` ，用于保持登录，一般时间为 `7+` 天
> 此令牌可以让用户在 `7+` 天内，当临时令牌过期后，用于刷新临时令牌
> 当此令牌过期后，最好要求用户重新登录
> 
> 所以我们专门设计了这个 `token` 令牌方案

##### 在我的想法当中是这样的:<br/>
1. 用户登录注册时, 生成令牌 `Tokenizer`<br/>
2. 在临时令牌时间内 `Token`, 将用户可以直接使用临时令牌请求接口<br/>
3. 临时令牌过期后, 自动使用 `Maximum` 请求刷新 `Token` <br/>

##### 生成令牌
```php
/** 只需要引入即可 */
$app = new \NiceYuv\Tokenizer();
/** generate 值为 string */
$app->generate('1');

/** 生成令牌 返回值 */
NiceYuv\TokenizerDto {
  token: "xxx"
  maximum: "xxx"
}
```


##### 验证临时令牌
```php
$app = new \NiceYuv\Tokenizer();
$app->verify($token);

/** 验证临时令牌 返回值 */
class TokenDto
// OR
false
```

##### 重新获取临时令牌 `update`
```php
$app = new \NiceYuv\Tokenizer();
$app->update($maximum);

/** 重新获取临时令牌 返回值 */
NiceYuv\TokenizerDto {
   token: "xxx"
   maximum: "xxx"
}
// OR
null
```

##### 有几个参数需要注意
```php
/** 是否生成长久令牌 */
private bool $way = true;

/** 临时令牌可用时间 */
private string $ttl = "+1 day";

/** 长久令牌可用时间 */
private string $effectiveDay = '+15 day';

/** 加密串 */
private string $secret = 'dbcb8a8b57b12684504f511e85ad5073430d143a';
```

##### 你可以这样设置主要参数
```php
$app = new \NiceYuv\Tokenizer();
$app->setWay(false);
$app->setTtl('+3 day');
$app->setEffectiveDay('+30 day');
$app->setSecret($secret);
```

#### 下面是几个用到的 `Dto` 需要注意
##### TokenizerDto
```php
class TokenizerDto
{
    public string $token;
    
    public string $maximum;
}
```

##### TokenDto
```php
class TokenDto
{
    public string $id;
    
    public string $platform;
    
    public string $generateDate;
    
    public string $effectiveDate;
}
```

##### MaximumDto
```php
class MaximumDto
{
    public ?bool $way = null;
    
    public string $id;
    
    public string $platform;
    
    public string $generateDate;
    
    public string $effectiveDate;
}
```
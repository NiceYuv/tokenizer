> [简体中文](README.zh-CN.md) | [English](README.md)

> #### background
> Frequently used token solutions
> Two tokens are generated at one time
>
> One is the temporary token 'token', which is used for current use. The general time is' 1 + 'days.
> This token can be used freely within '1 +' days
>
> One is the long-time token 'extend', which is used to maintain login. The general time is' 7 + 'days
> This token allows the user to refresh the temporary token within '7 +' days after the temporary token expires
> When this token expires, it is best to ask the user to log in again
>
> So we specially designed this 'token' scheme


##### That's what I think: <br/>
1. When a user logs in and registers, a token is generated `Tokenizer`<br/>
2. During temporary token time `Token`, The user can request the interface directly using the temporary token<br/>
   3.When the temporary token expires, it is automatically used `extend` Request refresh `Token` <br/>

##### Generate token
```php
$app = new \NiceYuv\Tokenizer();
/** generate value string */
$resp = $app->generate('1');

/** Generate token return value */
^ NiceYuv\TokenizerDto {#2
  +token: "qiC77BlTQZGkk61Bk1NOcX+zYzzF/73J2i3Nh/O2Z2FtdA83tsCoABw75oI2d8iuu69AYel6ngc="
  +extend: ""
}
// OR
null
```

##### Validate temporary token
```php
$app = new \NiceYuv\Tokenizer();
$resp = $app->generate('1');
$data = $app->verify($resp->token);

/** Verify temporary token return value */
^ NiceYuv\TokenDto {#88
  +id: "1"
  +refresh: null
  +platform: "web"
  +expireTime: 1641534214
}
// OR
null
```

##### Refresh temporary token `refreshtoken`
- You need to set `way` to `true` to return `extendtoken`
- After calling, `refreshtoken` will refresh the `token`
- `attention` `attention` `attention`
- The incoming `extend` will not be refreshed
```php
$app = new \NiceYuv\Tokenizer();
$app->setWay(true);

$resp = $app->generate('1');
$data = $app->refreshToken($resp->extend);

/** 
 * Refresh temporary token return value
 */
^ NiceYuv\TokenizerDto {#96
  +token: "qiC77BlTQZGkk61Bk1NOcX+zYzzF/73J2i3Nh/O2Z2FtdA83tsCoABw75oI2d8iuV3LYkfND3Cc="
  +extend: "qiC77BlTQZGK6roRwTJcVlc0b58w7HlzumffGveAMg2v8jFxklOCaBJVuzVAdY+kJPaRZVM0k+fczNCI2mkQxLwEGdLJ2Ard"
}
// OR
null
```

##### Refresh extension token `refreshExtend`
- Need to `way` Set to `true`, Before you can return `ExtendToken`
- Need to `refresh` Set to `true`, Refresh will be allowed `ExtendToken`
```php
$app = new \NiceYuv\Tokenizer();
$app->setWay(true);
$app->setRefresh(true);
$resp    = $app->generate('1');
$refresh = $app->refreshExtend($resp->extend);


/** 
 * Refresh deferred token return value
 */
string
OR
null
```

##### There are several parameters to note
```php

/** 
  * Whether to generate a long-term token. The default is (false)
  * When set to (true), `token` 'is allowed to be refreshed
 */
private bool $way = false;

/** Allow `extend` to be refreshed, default (false) */ 
private bool $refresh = false;

/** Available time of temporary token, default (+1 day)   */
private string $expireDate = "+1 day";

/** Extend token availability, default(+7 day) */
private string $extendDate = '+7 day';

/** Encryption string */
private string $secret = '8a8b57b12684504f511e85ad5073d1b2b430d143a';
```


##### You can set the main parameters like this
```php
$app = new \NiceYuv\Tokenizer();
$app->setWay(true);
$app->setRefresh(true);
$app->setExpireDate('+3 day');
$app->setExtendDate('+15 day');
$app->setSecret('8a8b57b12684504f511e85ad5073d1b2b430d143a');


/** Tokenizer Example */
NiceYuv\Tokenizer {#2 ▼
  -way: true
  -refresh: true
  -expireDate: "+3 day"
  -extendDate: "+15 day"
  -secret: "8a8b57b12684504f511e85ad5073d1b2b430d143a"
}
```

#### The following are two `dto` that need attention
##### TokenizerDto
```php
class TokenizerDto
{

    public string $token;

    public string $extend;

}
```

##### TokenDto
```php
class TokenDto
{
    public string $id;

    public ?bool $refresh = null;
    
    public string $platform;

    public int $expireTime;
}
```

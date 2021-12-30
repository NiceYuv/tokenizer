> [v1.1 简体中文](./doc-v1/README.zh-CN.md) | [v1.1 English](./doc-v1/README.md)

> [v1.2 简体中文](README.zh-CN.md) | [v1.2 English](README.md)

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


##### That's what I think:<br/>
1. When a user logs in and registers, a token is generated `Tokenizer`<br/>
2. During temporary token time `Token`, The user can request the interface directly using the temporary token<br/>
3.When the temporary token expires, it is automatically used `extend` Request refresh `Token` <br/>

##### Generate token
```php
$app = new \NiceYuv\Tokenizer();
/** generate value string */
$resp = $app->generate('1');

/** Generate token return value */
NiceYuv\TokenizerDto {#5 ▼
  + token: "qiC77BlTQZGkk61Bk1NOcX+zYzzF/73J2i3Nh/O2Z2FtdA83tsCoAOzFnEGnBDaHh8OSwOxgVEw="
  + extend: ""
}
```


##### Validate temporary token
```php
$app = new \NiceYuv\Tokenizer();
$resp = $app->generate('1');
$data = $app->verify($resp->token);

/** Verify temporary token return value */
true
// OR
false
```

##### Refresh the temporary token `refreshToken`
- You need to set `way` to `true` to return `ExtendToken`
- `token` will be refreshed after calling
- `Attention` `Attention` `Attention`
- The incoming `extend` will not be refreshed
```php
$app = new \NiceYuv\Tokenizer();
$app->setWay(true);
$resp         = $app->generate('1');
$refreshToken = $app->refreshToken($resp->extend);

/** 
 * Refresh temporary token Return value
 */
^ NiceYuv\TokenizerDto {#108 ▼
  + token: "qiC77BlTQZGkk61Bk1NOcX+zYzzF/73J2i3Nh/O2Z2FtdA83tsCoAAPW9jG3T85lUquL4biDeL8="
  + extend: "qiC77BlTQZGK6roRwTJcVpH2Hqlik+Xh5RTGs5P/djh/s2M8xf+9yYatvZoK+vESBrLXfb3u+6a+r0/hz3UowsUTNREYcR/4"
}
// OR
null
```


##### Refresh extension token `refreshExtend`
- Need to `way` `refresh` Are set to `true`
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

##### There are several parameters that need attention
```php

/** Whether to generate long-term tokens, default (false)  */
private bool $way = false;

/** Whether to allow `extend` to be refreshed */ 
private bool $refresh = false;

/** Temporary token available time */
private string $expireDate = "+1 day";

/** Extend token availability time */
private string $extendDate = '+7 day';

/** Encrypted string */
private string $secret = '8a8b57b12684504f511e85ad5073d1b2b430d143a';
```


##### You can set the main parameters like this
```php
$app = new \NiceYuv\Tokenizer();
$app->setWay(true);
$app->setRefresh(true);
$app->setExpireDate('+3 day');
$app->setExtendDate('+15 day');


/** Tokenizer Example */
NiceYuv\Tokenizer {#2 ▼
  -way: true
  -refresh: true
  -expireDate: "+3 day"
  -extendDate: "+15 day"
  -secret: "8a8b57b12684504f511e85ad5073d1b2b430d143a"
}
```

#### The following are a few used `Dto` need to pay attention
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
    
    public string $platform;

    public int $expireTime;
}
```

##### ExtendDto
```php
class ExtendDto
{
    public string $id;

    public bool $refresh;

    public string $platform;

    public int $extendTime;

}
```
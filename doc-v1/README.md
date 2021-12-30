> [v1.1 简体中文](./doc-v1/README.zh-CN.md) | [v1.1 English](./doc-v1/README.md)

> [v1.2 简体中文](README.zh-CN.md) | [v1.2 English](README.md)

> #### background
> Frequently used token solutions
> Two tokens are generated at one time
>
> One is the temporary token 'token', which is used for current use. The general time is' 1 + 'days.
> This token can be used freely within '1 +' days
>
> One is the long-time token 'maximum', which is used to maintain login. The general time is' 7 + 'days
> This token allows the user to refresh the temporary token within '7 +' days after the temporary token expires
> When this token expires, it is best to ask the user to log in again
>
> So we specially designed this 'token' scheme


##### That's what I think:<br/>
1. When a user logs in and registers, a token is generated `Tokenizer`<br/>
2. During temporary token time `Token`, The user can request the interface directly using the temporary token<br/>
3.When the temporary token expires, it is automatically used `Maximum` Request refresh `Token` <br/>

##### Generate token
```php
$app = new \NiceYuv\Tokenizer();
/** generate value string */
$app->generate('1');

/** Generate token return value */
NiceYuv\TokenizerDto {
  token: "xxx"
  maximum: "xxx"
}
```


##### Validate temporary token
```php
$app = new \NiceYuv\Tokenizer();
$app->verify($token);

/** Verify temporary token return value */
class TokenDto
// OR
false
```

##### Retrieve temporary token `update`
```php
$app = new \NiceYuv\Tokenizer();
$app->update($maximum);

/** Retrieve the return value of the temporary token */
NiceYuv\TokenizerDto {
   token: "xxx"
   maximum: "xxx"
}
// OR
null
```

##### There are several parameters to note
```php
/** Generate persistent token */
private bool $way = true;

/** Temporary token availability time */
private string $ttl = "+1 day";

/** Long token availability */
private string $effectiveDay = '+15 day';

/** Encryption string */
private string $secret = 'dbcb8a8b57b12684504f511e85ad5073430d143a';
```

##### You can set the main parameters like this
```php
$app = new \NiceYuv\Tokenizer();
$app->setWay(false);
$app->setTtl('+3 day');
$app->setEffectiveDay('+30 day');
$app->setSecret($secret);
```

#### Here are a few `dto` that need attention
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
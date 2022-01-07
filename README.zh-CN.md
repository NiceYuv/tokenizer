> [简体中文](README.zh-CN.md) | [English](README.md)

> ### 背景
> 经常会使用到的token令牌解决方案
> 一次会生成两面令牌
> 
> 一个是临时令牌 `token` ，用于当前使用，一般时间为 `1+` 天。
> 此令牌可以在 `1+` 天内随意使用
> 
> 一个是长时间令牌 `extend` ，用于保持登录，一般时间为 `7+` 天
> 此令牌可以让用户在 `7+` 天内，当临时令牌过期后，用于刷新临时令牌
> 当此令牌过期后，最好要求用户重新登录
> 
> 所以我们专门设计了这个 `token` 令牌方案

##### 在我的想法当中是这样的:<br/>
1. 用户登录注册时, 生成令牌 `Tokenizer`<br/>
2. 在临时令牌时间内 `Token`, 将用户可以直接使用临时令牌请求接口<br/>
3. 临时令牌过期后, 自动使用 `Extend` 请求刷新 `Token` <br/>

##### 生成令牌 `generate`
```php
/** 只需要引入即可 */
$app = new \NiceYuv\Tokenizer();
/** generate 值为 string */
$resp = $app->generate('1');

/** 生成令牌 返回值 */
^ NiceYuv\TokenizerDto {#15 ▼
  +token: "qiC77BlTQZGkk61Bk1NOcX+zYzzF/73J2i3Nh/O2Z2FtdA83tsCoALd6+F7ftGofgrAXlIGKfFD3o2KlhxziD+yEfr8KS78V"
  +extend: "qiC77BlTQZGK6roRwTJcVlc0b58w7HlzumffGveAMg2v8jFxklOCaBJVuzVAdY+kJPaRZVM0k+cyN9/uCKZhVIbPUgX36K8SwI6eVW8O7NZBlhnJrxqEWA=="
  +login_date: DateTime @1641541496 {#58 ▼
    date: 2022-01-07 15:44:56.908481 Asia/Shanghai (+08:00)
  }
  +expire_date: DateTime @1641800696 {#2 ▼
    date: 2022-01-10 15:44:56.908440 Asia/Shanghai (+08:00)
  }
}
// OR
null
```

##### 验证临时令牌 `verify`
```php
$app = new \NiceYuv\Tokenizer();
$resp = $app->generate('1');
$data = $app->verify($resp->token);

/** 验证临时令牌 返回值 */
^ NiceYuv\TokenDto {#90 ▼
  +id: "1"
  +refresh: null
  +platform: "web"
  +expireTime: DateTime @1641547514 {#99 ▼
    date: 2022-01-07 17:25:14.0 +08:00
  }
}
// OR
null
```

##### 刷新临时令牌 `refreshToken`
- 需要将 `way` 设定为 `true`, 才可以返回 `ExtendToken`
- 调用后 `refreshToken` 会刷新 `token`
- `注意` `注意` `注意` 
- 传入的 `extend`不会刷新
```php
$app = new \NiceYuv\Tokenizer();
$app->setWay(true);

$resp = $app->generate('1');
$data = $app->refreshToken($resp->extend);

/** 
 * 刷新临时令牌 返回值
 */
^ NiceYuv\TokenizerDto {#34 ▼
  +token: "qiC77BlTQZGkk61Bk1NOcX+zYzzF/73J2i3Nh/O2Z2FtdA83tsCoALd6+F7ftGofgrAXlIGKfFCL+az9o02XyeyEfr8KS78V"
  +extend: "qiC77BlTQZGK6roRwTJcVlc0b58w7HlzumffGveAMg2v8jFxklOCaBJVuzVAdY+kJPaRZVM0k+cyN9/uCKZhVIbPUgX36K8SJALMiFMrUwtBlhnJrxqEWA=="
  +login_date: DateTime @1641541631 {#146 ▼
    date: 2022-01-07 15:47:11.492178 Asia/Shanghai (+08:00)
  }
  +expire_date: DateTime @1641800831 {#80 ▼
    date: 2022-01-10 15:47:11.491920 Asia/Shanghai (+08:00)
  }
}
// OR
null
```

##### 刷新延长令牌 `refreshExtend`
- 需要将 `way` 设定为 `true`, 才可以返回 `ExtendToken`
- 需要将 `refresh` 设定为 `true`, 才会允许刷新 `ExtendToken`
```php
$app = new \NiceYuv\Tokenizer();
$app->setWay(true);
$app->setRefresh(true);
$resp    = $app->generate('1');
$refresh = $app->refreshExtend($resp->extend);


/** 
 * 刷新延期令牌 返回值
 */
^ NiceYuv\TokenizerDto {#91 ▼
  +token: "qiC77BlTQZGkk61Bk1NOcX+zYzzF/73J2i3Nh/O2Z2FtdA83tsCoALd6+F7ftGofgrAXlIGKfFD8y1M69PzOMuyEfr8KS78V"
  +extend: "qiC77BlTQZGK6roRwTJcVlc0b58w7HlzumffGveAMg2v8jFxklOCaBJVuzVAdY+kJPaRZVM0k+cyN9/uCKZhVIbPUgX36K8ShGcwqFr2dtxBlhnJrxqEWA=="
  +login_date: DateTime @1641541659 {#237 ▼
    date: 2022-01-07 15:47:39.956036 Asia/Shanghai (+08:00)
  }
  +expire_date: DateTime @1641800859 {#185 ▼
    date: 2022-01-10 15:47:39.955783 Asia/Shanghai (+08:00)
  }
}
OR
null
```

##### 有几个参数需要注意
```php

/** 
 * 是否生成长久令牌, 默认 (false)  
 * 当设置为 (true) 时, 则 `token` 允许被刷新
 */
private bool $way = false;

/** 是否允许 `extend` 被刷新, 默认 (false) */ 
private bool $refresh = false;

/** 临时令牌可用时间, 默认 (+1天)   */
private string $expireDate = "+1 day";

/** 延长令牌可用时间, 默认 (+7天) */
private string $extendDate = '+7 day';

/** 设置内部服务器时区 */
private string $dateTimeZone = 'Asia/Shanghai';

/** 加密串 */
private string $secret = '8a8b57b12684504f511e85ad5073d1b2b430d143a';
```


##### 你可以这样设置主要参数
```php
$app = new \NiceYuv\Tokenizer();
$app->setWay(true);
$app->setRefresh(true);
$app->setExpireDate('+3 day');
$app->setExtendDate('+15 day');
$app->setSecret('8a8b57b12684504f511e85ad5073d1b2b430d143a');
$app->setDateTimeZone('Asia/Shanghai');



/** Tokenizer 示例 */
^ NiceYuv\Tokenizer {#3 ▼
  -way: true
  -refresh: true
  -expireDate: "+3 day"
  -extendDate: "+7 day"
  -dateTimeZone: "Asia/Shanghai"
  -secret: "8a8b57b12684504f511e85ad5073d1b2b430d143a"
}
```

#### 下面是两个用到的 `Dto` 需要注意
##### TokenizerDto 
```php
class TokenizerDto
{

    public string $token;

    public string $extend;

    public DateTime $login_date;

    public DateTime $expire_date;

}
```

##### TokenDto
```php
class TokenDto
{
    public string $id;

    public ?bool $refresh = null;
    
    public string $platform;

    public DateTime $expireTime;
}
```

<?php
declare(strict_types=1);

namespace NiceYuv;

use DateTime;

class TokenizerDto
{

    public string $token;

    public string $extend;

    public DateTime $login_date;

    public DateTime $expire_date;

}
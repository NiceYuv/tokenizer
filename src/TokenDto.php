<?php
declare(strict_types=1);

namespace NiceYuv;

/** Token the validity period */
class TokenDto
{
    public string $id;

    public ?bool $refresh = null;
    
    public string $platform;

    public int $expireTime;
}
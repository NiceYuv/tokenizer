<?php
declare(strict_types=1);

namespace NiceYuv;

/** Extend the validity period */
class ExtendDto
{
    public string $id;

    public bool $refresh;

    public string $platform;

    public int $extendTime;

}
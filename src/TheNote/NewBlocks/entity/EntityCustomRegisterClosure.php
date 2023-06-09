<?php

namespace TheNote\NewBlocks\entity;

use Closure;

interface EntityCustomRegisterClosure{

    public static function getRegisterClosure(): Closure;
}
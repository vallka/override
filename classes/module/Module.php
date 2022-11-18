<?php

class Module extends ModuleCore
{
    protected function getCacheId($name = null)
    {
        $result = parent::getCacheId($name);

        return $result . '|' .  Context::getContext()->getDevice();
    }
}

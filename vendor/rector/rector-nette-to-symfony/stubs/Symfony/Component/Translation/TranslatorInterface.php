<?php

declare (strict_types=1);
namespace RectorPrefix20210515\Symfony\Component\Translation;

if (\interface_exists('Symfony\\Component\\Translation\\TranslatorInterface')) {
    return;
}
interface TranslatorInterface
{
}
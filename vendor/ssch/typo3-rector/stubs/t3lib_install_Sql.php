<?php

namespace RectorPrefix20210602;

if (\class_exists('t3lib_install_Sql')) {
    return;
}
class t3lib_install_Sql
{
}
\class_alias('t3lib_install_Sql', 't3lib_install_Sql', \false);
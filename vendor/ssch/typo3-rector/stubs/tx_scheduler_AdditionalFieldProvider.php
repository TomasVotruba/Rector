<?php

namespace RectorPrefix20210602;

if (\class_exists('tx_scheduler_AdditionalFieldProvider')) {
    return;
}
class tx_scheduler_AdditionalFieldProvider
{
}
\class_alias('tx_scheduler_AdditionalFieldProvider', 'tx_scheduler_AdditionalFieldProvider', \false);
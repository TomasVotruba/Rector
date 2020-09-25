<?php

declare(strict_types=1);

namespace Rector\Naming\Guard\PropertyConflictingNameGuard;

use Rector\Naming\ExpectedNameResolver\UnderscoreCamelCaseExpectedNameResolver;

class UnderscoreCamelCaseConflictingNameGuard extends AbstractPropertyConflictingNameGuard
{
    /**
     * @required
     */
    public function autowireUnderscoreCamelCasePropertyConflictingNameGuard(
        UnderscoreCamelCaseExpectedNameResolver $underscoreCamelCaseExpectedNameResolver
    ): void {
        $this->expectedNameResolver = $underscoreCamelCaseExpectedNameResolver;
    }
}

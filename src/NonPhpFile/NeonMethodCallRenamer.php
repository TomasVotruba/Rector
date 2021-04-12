<?php
declare(strict_types=1);

namespace Rector\Core\NonPhpFile;

use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\NonPhpFile\NonPhpFileChange;
use Rector\Renaming\Configuration\MethodCallRenameCollector;
use Symplify\SmartFileSystem\SmartFileInfo;

class NeonMethodCallRenamer implements FileProcessorInterface
{
    /**
     * @var MethodCallRenameCollector
     */
    private $methodCallRenameCollector;

    public function __construct(MethodCallRenameCollector $methodCallRenameCollector)
    {
        $this->methodCallRenameCollector = $methodCallRenameCollector;
    }

    public function process(SmartFileInfo $smartFileInfo): ?NonPhpFileChange
    {
        $oldContent = $newContent = $smartFileInfo->getContents();

        foreach ($this->methodCallRenameCollector->getMethodCallRenames() as $methodCallRename) {
            $oldObjectType = $methodCallRename->getOldObjectType();
            $objectClassName = $oldObjectType->getClassName();
            $className = str_replace('\\', '\\\\', $objectClassName);

            $oldMethodName = $methodCallRename->getOldMethod();
            $newMethodName = $methodCallRename->getNewMethod();

            $pattern = '/\n(.*?)(class|factory): ' . $className . '(\n|\((.*?)\)\n)\1setup:(.*?)- ' . $oldMethodName . '\(/s';
            while (preg_match($pattern, $newContent, $matches)) {
                $replacedMatch = str_replace($oldMethodName . '(', $newMethodName . '(', $matches[0]);
                $newContent = str_replace($matches[0], $replacedMatch, $newContent);
            }
        }

        return new NonPhpFileChange($oldContent, $newContent);
    }

    public function supports(SmartFileInfo $smartFileInfo): bool
    {
        return in_array($smartFileInfo->getExtension(), $this->getSupportedFileExtensions());
    }

    public function getSupportedFileExtensions(): array
    {
        return ['neon'];
    }
}

<?php

declare(strict_types=1);

namespace Rector\FileSystemRector\Rector\Removing;

use PhpParser\Node;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\PhpParser\Node\CustomNode\FileNode;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Webmozart\Assert\Assert;

final class RemoveProjectFileRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const FILE_PATHS_TO_REMOVE = 'file_paths_to_remove';

    /**
     * @var string[]
     */
    private $filePathsToRemove = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove file relative to project directory', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
// someFile/ToBeRemoved.txt
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
CODE_SAMPLE
                ,
                [
                    self::FILE_PATHS_TO_REMOVE => ['someFile/ToBeRemoved.txt'],
                ]
            ),
        ]);
    }

    public function getNodeTypes(): array
    {
        return [FileNode::class];
    }

    /**
     * @param FileNode $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->filePathsToRemove === []) {
            return null;
        }

        $projectDirectory = getcwd();

        $smartFileInfo = $node->getFileInfo();
        $relativePathInProject = $smartFileInfo->getRelativeFilePathFromDirectory($projectDirectory);

        foreach ($this->filePathsToRemove as $filePathsToRemove) {
            if ($relativePathInProject !== $filePathsToRemove) {
                continue;
            }

            $this->removeFile($smartFileInfo);
        }

        return null;
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        $filePathsToRemove = $configuration[self::FILE_PATHS_TO_REMOVE] ?? [];
        Assert::allString($filePathsToRemove);

        $this->filePathsToRemove = $filePathsToRemove;
    }
}

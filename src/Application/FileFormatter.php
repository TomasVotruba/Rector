<?php
declare(strict_types=1);

namespace Rector\Core\Application;

use Rector\Core\Contract\EditorConfig\EditorConfigParserInterface;
use Rector\Core\Contract\Formatter\FormatterInterface;
use Rector\Core\ValueObject\Application\File;

final class FileFormatter
{
    /**
     * @var EditorConfigParserInterface
     */
    private $editorConfigParser;

    /**
     * @var FormatterInterface[]
     */
    private $fileFormatters;

    /**
     * @param FormatterInterface[] $fileFormatters
     */
    public function __construct(EditorConfigParserInterface $editorConfigParser, array $fileFormatters = [])
    {
        $this->editorConfigParser = $editorConfigParser;
        $this->fileFormatters = $fileFormatters;
    }

    /**
     * @param File[] $files
     */
    public function format(array $files): void
    {
        foreach ($files as $file) {

            if (! $file->hasChanged()) {
                continue;
            }

            foreach ($this->fileFormatters as $fileFormatter) {
                if (! $fileFormatter->supports($file)) {
                    continue;
                }

                $editorConfiguration = $this->editorConfigParser->extractConfigurationForFile(
                    $file,
                    $fileFormatter->createEditorConfigConfigurationBuilder()
                );

                $fileFormatter->format($file, $editorConfiguration);
            }
        }
    }
}

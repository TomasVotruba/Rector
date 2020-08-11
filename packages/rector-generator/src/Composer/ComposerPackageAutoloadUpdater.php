<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Composer;

use Rector\RectorGenerator\FileSystem\JsonFileSystem;
use Rector\RectorGenerator\ValueObject\Configuration;
use Rector\RectorGenerator\ValueObject\Package;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

final class ComposerPackageAutoloadUpdater
{
    /**
     * @var string
     */
    private const PSR_4 = 'psr-4';

    /**
     * @var string
     */
    private const AUTOLOAD_DEV = 'autoload-dev';

    /**
     * @var JsonFileSystem
     */
    private $jsonFileSystem;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(JsonFileSystem $jsonFileSystem, SymfonyStyle $symfonyStyle)
    {
        $this->jsonFileSystem = $jsonFileSystem;
        $this->symfonyStyle = $symfonyStyle;
    }

    public function processComposerAutoload(Configuration $configuration): void
    {
        $composerJsonFilePath = getcwd() . '/composer.json';
        $composerJson = $this->jsonFileSystem->loadFileToJson($composerJsonFilePath);

        $package = $this->resolvePackage($configuration);

        if ($this->isPackageAlreadyLoaded($composerJson, $package)) {
            return;
        }

        // ask user
        $questionText = sprintf(
            'Should we update "composer.json" autoload with "%s" namespace?',
            $package->getSrcNamespace()
        );

        $isConfirmed = $this->symfonyStyle->confirm($questionText);
        if (! $isConfirmed) {
            return;
        }

        $srcAutoload = $configuration->isRectorRepository() ? 'autoload' : self::AUTOLOAD_DEV;
        $composerJson[$srcAutoload][self::PSR_4][$package->getSrcNamespace()] = $package->getSrcDirectory();

        $composerJson[self::AUTOLOAD_DEV][self::PSR_4][$package->getTestsNamespace()] = $package->getTestsDirectory();

        $this->jsonFileSystem->saveJsonToFile($composerJsonFilePath, $composerJson);

        $this->rebuildAutoload();
    }

    private function resolvePackage(Configuration $configuration): Package
    {
        if (! $configuration->isRectorRepository()) {
            return new Package(
                'Utils\\Rector\\',
                'Utils\\Rector\\Tests\\',
                'utils/rector/src',
                'utils/rector/tests'
            );
        }

        return new Package(
            'Rector\\' . $configuration->getPackage() . '\\',
            'Rector\\' . $configuration->getPackage() . '\\Tests\\',
            'rules/' . $configuration->getPackageDirectory() . '/src',
            'rules/' . $configuration->getPackageDirectory() . '/tests'
        );
    }

    private function isPackageAlreadyLoaded(array $composerJson, Package $package): bool
    {
        foreach (['autoload', self::AUTOLOAD_DEV] as $autoloadSection) {
            if (isset($composerJson[$autoloadSection][self::PSR_4][$package->getSrcNamespace()])) {
                return true;
            }
        }

        return false;
    }

    private function rebuildAutoload(): void
    {
        $composerDumpProcess = new Process(['composer', 'dump']);
        $composerDumpProcess->run();
    }
}

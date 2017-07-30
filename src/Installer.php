<?php

namespace Devbr\Composer;

use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;

class Installer extends LibraryInstaller
{
    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        $prefix = substr($package->getPrettyName(), 0, 11);
        if ('devbr/pack-' !== $prefix) {
            throw new \InvalidArgumentException(
                'Unable to install pack, devbr packs '
                .'should always start their package name with '
                .'"devbr/pack-"'
            );
        }

        return '.php/Pack/'.substr($package->getPrettyName(), 11);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return 'devbr-pack' === $packageType;
    }
}

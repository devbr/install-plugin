<?php

namespace Devbr;

use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;

class Installer extends LibraryInstaller
{
    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        $prefix = substr($package->getPrettyName(), 0, 23);
        if ('devbr/template-' !== $prefix) {
            throw new \InvalidArgumentException(
                'Unable to install template, devbr templates '
                .'should always start their package name with '
                .'"devbr/template-"'
            );
        }

        return 'data/templates/'.substr($package->getPrettyName(), 23);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return 'devbr-template' === $packageType;
    }
}

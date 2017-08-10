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

        return '.php/Pack/'.ucfirst(substr($package->getPrettyName(), 11));
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return 'devbr-pack' === $packageType;
    }
    
    
    /**
     * {@inheritDoc}
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $this->initializeVendorDir();
        $downloadPath = $this->getInstallPath($package);
        // remove the binaries if it appears the package files are missing
        if (!is_readable($downloadPath) && $repo->hasPackage($package)) {
            $this->binaryInstaller->removeBinaries($package);
        }
        $this->installCode($package);
        $this->binaryInstaller->installBinaries($package, $this->getInstallPath($package));
        if (!$repo->hasPackage($package)) {
            $repo->addPackage(clone $package);
        }
        
        //ME Code ----
        if(file_exists($downloadPath.'/Config') && is_read...
    }
}

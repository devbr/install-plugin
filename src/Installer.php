<?php

namespace Devbr\Composer;

use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Util\Filesystem;
use Composer\Util\Silencer;

class Installer extends LibraryInstaller
{

    private $phpDir = '';

    /**
     * {@inheritDoc}
     */
    public function __construct(
        IOInterface $io, 
        Composer $composer, 
        $type = 'library', 
        Filesystem $filesystem = null, 
        BinaryInstaller $binaryInstaller = null
    ){
        parent::__construct($io, $composer, $type = 'library', $filesystem = null, $binaryInstaller = null);
        $this->phpDir = dirname(rtrim($composer->getConfig()->get('vendor-dir'), '/'));
    }


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
        $packConfig = $downloadPath.'/Config';
        $appConfig =  $this->phpDir.'/Config';

        echo "\n\n\tPackConfig: $packConfig\n\tAppConfig: $appConfig\n\n";
        echo "\n\n\tHtmlPath: ".(defined('_HTML') ? _HTML : 'indefinido...')."\n\n";

        if(file_exists($packConfig) && is_readable($packConfig)){
            echo "\n\t--- Config exists ---";
            self::checkAndOrCreateDir($appConfig, true);
            self::copyDirectoryContents($packConfig, $appConfig);
        } else {
            echo "\n\t--- Config Not Exists!! ---";
        }
    }


    /**
     * Check or create a directory
     * @param  string  $dir    path of the directory
     * @param  boolean $create False/true for create
     * @param  string  $perm   indiucates a permission - default 0777
     *
     * @return bool          status of directory (exists/created = false or true)
     */
    static private function checkAndOrCreateDir($dir, $create = false, $perm = 0777)
    {
        if (is_dir($dir) && is_writable($dir)) {
            return true;
        } elseif ($create === false) {
            return false;
        }

        @mkdir($dir, $perm, true);
        @chmod($dir, $perm);

        if (!is_writable($dir)) {
            return false;
        }
        
        return true;
    }

    /**
     * Copy entire content of the $dir[ectory]
     * @param  string $dir    Origin
     * @param  string $target Destination
     * @return bool         True/false success
     */
    static private function copyDirectoryContents($dir, $target, $overwrite = true, $base = '')
    {
        $dir = rtrim($dir, "\\/ ").'/';
        $target = rtrim($target, "\\/ ").'/';
        $report = ['error'=>[],'copied'=>[]];

        if (!static::checkAndOrCreateDir($target, true, 0777)) {
            $report['error']['permission'] = $taget;
            return $report;
        }

        foreach (scandir($dir) as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            if (is_dir($dir.$file)) {
                if (!static::checkAndOrCreateDir($target.$file, true, 0777)) {
                    $report['error']['permission'] = $taget.$file;
                    return $report;
                } else {
                    $copy = static::copyDirectoryContents($dir.$file, $target.$file, $overwrite, $base);
                    $report = array_merge_recursive($report, $copy);
                }
            } elseif (is_file($dir.$file)) {
                if ($overwrite === false && file_exists($target.$file)) {
                    $report['error']['overwrite'][] = str_replace($base.'/', '', $target.$file);
                    continue;
                }
                if (!copy($dir.$file, $target.$file)) {
                    $report['error']['permission'] = $target.$file;
                    return $report;
                } else {
                    $report['copied'][] = str_replace($base.'/', '', $target.$file);
                }
            }
        }
        return $report;
    }
}

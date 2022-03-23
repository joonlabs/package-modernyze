<?php

namespace Modernyze\Facades;

use Illuminate\Support\Facades\Facade;
use Modernyze\ModernyzeManager;

/**
 * @method static ModernyzeManager setToken(string $token)
 * @method static ModernyzeManager setSecret(string $secret)
 * @method static ModernyzeManager setUrl(string $url)
 * @method static array allProducts()
 * @method static array allVersions(string $product)
 * @method static string|null latestVersion(string $product)
 * @method static array versionInformation(string $product, string $version)
 * @method static void downloadAndVerify(string $product, string $version, string $directory)
 * @method static bool update(string $product, string $version, string $directory)
 *
 * @see \Modernyze\ModernyzeManager
 */
class Modernyze extends Facade{

    protected static function getFacadeAccessor(): string
    {
        return "Modernyze";
    }
}
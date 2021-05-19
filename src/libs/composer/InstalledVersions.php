<?php

namespace Composer;

use Composer\Semver\VersionParser;






class InstalledVersions
{
private static $installed = array (
  'root' => 
  array (
    'pretty_version' => '3.0.x-dev',
    'version' => '3.0.9999999.9999999-dev',
    'aliases' => 
    array (
    ),
    'reference' => 'bf68271a5bbedecc69c024a19eb088ffbfa0d9af',
    'name' => 'thorsten/phpmyfaq',
  ),
  'versions' => 
  array (
    'abraham/twitteroauth' => 
    array (
      'pretty_version' => '0.7.4',
      'version' => '0.7.4.0',
      'aliases' => 
      array (
      ),
      'reference' => 'c6f9e692552dd037b2324ed0dfa28a4e60875acf',
    ),
    'elasticsearch/elasticsearch' => 
    array (
      'pretty_version' => 'v7.8.0',
      'version' => '7.8.0.0',
      'aliases' => 
      array (
      ),
      'reference' => '5c2d039ae7bdaa1e28f1e66971c5b3314fc36383',
    ),
    'erusev/parsedown' => 
    array (
      'pretty_version' => '1.7.4',
      'version' => '1.7.4.0',
      'aliases' => 
      array (
      ),
      'reference' => 'cb17b6477dfff935958ba01325f2e8a2bfa6dab3',
    ),
    'erusev/parsedown-extra' => 
    array (
      'pretty_version' => '0.7.1',
      'version' => '0.7.1.0',
      'aliases' => 
      array (
      ),
      'reference' => '0db5cce7354e4b76f155d092ab5eb3981c21258c',
    ),
    'ezimuel/guzzlestreams' => 
    array (
      'pretty_version' => '3.0.1',
      'version' => '3.0.1.0',
      'aliases' => 
      array (
      ),
      'reference' => 'abe3791d231167f14eb80d413420d1eab91163a8',
    ),
    'ezimuel/ringphp' => 
    array (
      'pretty_version' => '1.1.2',
      'version' => '1.1.2.0',
      'aliases' => 
      array (
      ),
      'reference' => '0b78f89d8e0bb9e380046c31adfa40347e9f663b',
    ),
    'monolog/monolog' => 
    array (
      'pretty_version' => '1.25.4',
      'version' => '1.25.4.0',
      'aliases' => 
      array (
      ),
      'reference' => '3022efff205e2448b560c833c6fbbf91c3139168',
    ),
    'myclabs/deep-copy' => 
    array (
      'pretty_version' => '1.9.5',
      'version' => '1.9.5.0',
      'aliases' => 
      array (
      ),
      'reference' => 'b2c28789e80a97badd14145fda39b545d83ca3ef',
      'replaced' => 
      array (
        0 => '1.9.5',
      ),
    ),
    'phpseclib/phpseclib' => 
    array (
      'pretty_version' => '2.0.27',
      'version' => '2.0.27.0',
      'aliases' => 
      array (
      ),
      'reference' => '34620af4df7d1988d8f0d7e91f6c8a3bf931d8dc',
    ),
    'psr/log' => 
    array (
      'pretty_version' => '1.1.3',
      'version' => '1.1.3.0',
      'aliases' => 
      array (
      ),
      'reference' => '0f73288fd15629204f9d42b7055f72dacbe811fc',
    ),
    'psr/log-implementation' => 
    array (
      'provided' => 
      array (
        0 => '1.0.0',
      ),
    ),
    'react/promise' => 
    array (
      'pretty_version' => 'v2.8.0',
      'version' => '2.8.0.0',
      'aliases' => 
      array (
      ),
      'reference' => 'f3cff96a19736714524ca0dd1d4130de73dbbbc4',
    ),
    'swiftmailer/swiftmailer' => 
    array (
      'pretty_version' => 'v5.4.12',
      'version' => '5.4.12.0',
      'aliases' => 
      array (
      ),
      'reference' => '181b89f18a90f8925ef805f950d47a7190e9b950',
    ),
    'tecnickcom/tcpdf' => 
    array (
      'pretty_version' => '6.3.5',
      'version' => '6.3.5.0',
      'aliases' => 
      array (
      ),
      'reference' => '19a535eaa7fb1c1cac499109deeb1a7a201b4549',
    ),
    'thorsten/phpmyfaq' => 
    array (
      'pretty_version' => '3.0.x-dev',
      'version' => '3.0.9999999.9999999-dev',
      'aliases' => 
      array (
      ),
      'reference' => 'bf68271a5bbedecc69c024a19eb088ffbfa0d9af',
    ),
  ),
);







public static function getInstalledPackages()
{
return array_keys(self::$installed['versions']);
}









public static function isInstalled($packageName)
{
return isset(self::$installed['versions'][$packageName]);
}














public static function satisfies(VersionParser $parser, $packageName, $constraint)
{
$constraint = $parser->parseConstraints($constraint);
$provided = $parser->parseConstraints(self::getVersionRanges($packageName));

return $provided->matches($constraint);
}










public static function getVersionRanges($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

$ranges = array();
if (isset(self::$installed['versions'][$packageName]['pretty_version'])) {
$ranges[] = self::$installed['versions'][$packageName]['pretty_version'];
}
if (array_key_exists('aliases', self::$installed['versions'][$packageName])) {
$ranges = array_merge($ranges, self::$installed['versions'][$packageName]['aliases']);
}
if (array_key_exists('replaced', self::$installed['versions'][$packageName])) {
$ranges = array_merge($ranges, self::$installed['versions'][$packageName]['replaced']);
}
if (array_key_exists('provided', self::$installed['versions'][$packageName])) {
$ranges = array_merge($ranges, self::$installed['versions'][$packageName]['provided']);
}

return implode(' || ', $ranges);
}





public static function getVersion($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

if (!isset(self::$installed['versions'][$packageName]['version'])) {
return null;
}

return self::$installed['versions'][$packageName]['version'];
}





public static function getPrettyVersion($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

if (!isset(self::$installed['versions'][$packageName]['pretty_version'])) {
return null;
}

return self::$installed['versions'][$packageName]['pretty_version'];
}





public static function getReference($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

if (!isset(self::$installed['versions'][$packageName]['reference'])) {
return null;
}

return self::$installed['versions'][$packageName]['reference'];
}





public static function getRootPackage()
{
return self::$installed['root'];
}







public static function getRawData()
{
return self::$installed;
}



















public static function reload($data)
{
self::$installed = $data;
}
}

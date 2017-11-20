<?php

namespace Magium\AwsFactory\Tests;

use Aws\Ec2\Ec2Client;
use Magium\AwsFactory\AwsFactory;
use PHPUnit\Framework\TestCase;

class ValidateVersionTest extends TestCase
{

    public function testClientVersionExtractor()
    {
        $classesToIgnore = [
            'Aws\MultiRegionClient'
        ];
        $srcDir = __DIR__ . '/../vendor/aws/aws-sdk-php/src';
        $Directory = new \RecursiveDirectoryIterator($srcDir);
        $Iterator = new \RecursiveIteratorIterator($Directory);
        $Regex = new \RegexIterator($Iterator, '/^.+Client\.php$/i', \RecursiveRegexIterator::GET_MATCH);
        foreach ($Regex as $classFilename) {
            $classFilename = array_shift($classFilename);
            if (strpos($classFilename, 'AwsClient') !== false) {
                continue;  // We don't need the version for the base client class
            }
            $fileContents = file_get_contents($classFilename);
            $matches = null;
            preg_match('/namespace ([^;]+).*/s', $fileContents, $matches);
            if (!$matches) continue;
            $namespace = $matches[1];
            $className = substr(basename($classFilename), 0, -4);
            $class = $namespace . '\\' . $className;
            if (in_array($class, $classesToIgnore)) continue;
            $version = AwsFactory::getVersionFor($class);
            self::assertNotNull($version, 'Failed to find version for ' . $class);
         }
    }

    public function testEc2Client()
    {
        $version = AwsFactory::getVersionFor(Ec2Client::class);
        self::assertNotNull($version);
    }

}

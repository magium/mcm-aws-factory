<?php

namespace Magium\AwsFactory;

use Aws\AwsClient;
use Magium\Configuration\Config\Repository\ConfigInterface;

class AwsFactory
{
    const CONFIG_REGION = 'aws/credentials/region';
    const CONFIG_KEY = 'aws/credentials/key';
    const CONFIG_SECRET = 'aws/credentials/secret';

    /**
     * These are all the known API versions as of the last time this file was edited.  If this list is out of date,
     * please correct and issue a pull request to https://github.com/magium/mcm-aws-factory
     *
     * @var array
     */

    private static $versions = [];

    private $config;
    private static $self;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
        self::$self = $this;
    }

    private static function getDataDir($class)
    {
        // We use the class to navigate our way to the data directory
        $classParts = explode('\\', $class);
        array_pop($classParts);
        $reflection = new \ReflectionClass($class);
        $dataDirName = dirname($reflection->getFileName());
        $parts = explode(DIRECTORY_SEPARATOR, $dataDirName);
        do {
            $cwd = array_pop($parts);
        } while (count($parts) > 0 && $cwd != 'aws-sdk-php');

        return implode(DIRECTORY_SEPARATOR, $parts)
            . DIRECTORY_SEPARATOR
            . implode(DIRECTORY_SEPARATOR , ['aws-sdk-php', 'src', 'data']);
    }

    public static function getVersionFor($class)
    {
        $namespace = explode('\\', $class);
        array_pop($namespace); // Get rid of the class name
        $namespace = array_pop($namespace);  // Get the highest level namespace

        if (!isset(self::$versions[$namespace])) {
            $manifestFile = self::getDataDir($class) . DIRECTORY_SEPARATOR . 'manifest.json.php';
            $manifest = include $manifestFile;
            foreach ($manifest as $entry) {
                self::$versions[$entry['namespace']] = $entry['versions']['latest'];
            }
            if (!isset(self::$versions[$namespace])) {
                throw new UnknownVersionException('Could not find version for ' . $class);
            }
        }
        return self::$versions[$namespace];
    }


    /**
     * @param string $class
     * @return AwsClient
     */

    public function factory($class = AwsClient::class)
    {
        return new $class([
            'region'    => $this->config->getValue(self::CONFIG_REGION),
            'credentials' => [
                'key'    => $this->config->getValue(self::CONFIG_KEY),
                'secret' => $this->config->getValue(self::CONFIG_SECRET)
            ],
            'version'   => self::getVersionFor($class),
        ]);
    }

    public static function staticFactory(ConfigInterface $config, $class = AwsClient::class)
    {
        if (!self::$self instanceof self) {
            self::$self = new self($config);
        }
        return self::$self->factory($class);
    }

}

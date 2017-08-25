<?php

namespace Magium\AwsFactory;

use Aws\ApiGateway\ApiGatewayClient;
use Aws\AutoScaling\AutoScalingClient;
use Aws\AwsClient;
use Aws\CloudDirectory\CloudDirectoryClient;
use Aws\CloudFormation\CloudFormationClient;
use Aws\CloudFront\CloudFrontClient;
use Aws\CloudSearch\CloudSearchClient;
use Aws\CloudWatch\CloudWatchClient;
use Aws\CodeDeploy\CodeDeployClient;
use Aws\CognitoIdentity\CognitoIdentityClient;
use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Aws\DirectoryService\DirectoryServiceClient;
use Aws\DynamoDb\DynamoDbClient;
use Aws\Ec2\Ec2Client;
use Aws\Ecr\EcrClient;
use Aws\Ecs\EcsClient;
use Aws\Efs\EfsClient;
use Aws\ElastiCache\ElastiCacheClient;
use Aws\ElasticLoadBalancing\ElasticLoadBalancingClient;
use Aws\ElasticLoadBalancingV2\ElasticLoadBalancingV2Client;
use Aws\ElasticsearchService\ElasticsearchServiceClient;
use Aws\Iam\IamClient;
use Aws\Rds\RdsClient;
use Aws\Route53\Route53Client;
use Aws\S3\S3Client;
use Aws\Ses\SesClient;
use Aws\Sms\SmsClient;
use Aws\Sns\SnsClient;
use Aws\Sqs\SqsClient;
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

    private static $versions = [
        Ec2Client::class => '2016-11-15',
        ApiGatewayClient::class => '2015-07-09',
        AutoScalingClient::class => '2011-01-01',
        CloudDirectoryClient::class => '2016-05-10',
        CloudFormationClient::class => '2010-05-15',
        CloudFrontClient::class => '2017-03-25',
        CloudSearchClient::class => '2013-01-01',
        CloudWatchClient::class => '2010-08-01',
        CodeDeployClient::class => '2014-10-06',
        CognitoIdentityClient::class => '2014-06-30',
        CognitoIdentityProviderClient::class => '2016-04-18',
        DirectoryServiceClient::class => '2015-04-16',
        DynamoDbClient::class => '2012-08-10',
        EcsClient::class => '2014-11-13',
        EcrClient::class => '2015-09-21',
        EfsClient::class => '2015-02-01',
        ElastiCacheClient::class => '2015-02-02',
        ElasticLoadBalancingClient::class => '2012-06-01',
        ElasticLoadBalancingV2Client::class => '2015-12-01',
        ElasticsearchServiceClient::class => '2015-01-01',
        IamClient::class => '2010-05-08',
        RdsClient::class => '2014-10-31',
        Route53Client::class => '2013-04-01',
        S3Client::class => '2006-03-01',
        SesClient::class => '2010-12-01',
        SmsClient::class => '2016-10-24',
        SnsClient::class => '2010-03-31',
        SqsClient::class => '2012-11-05'
    ];

    private $config;
    private static $self;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
        self::$self = $this;
    }

    public static function setVersion($class, $version)
    {
        self::$versions[$class] = $version;
    }

    public static function getVersionFor($class)
    {
        if (!isset(self::$versions[$class])) {
            throw new UnknownVersionException('Could not find version for ' . $class);
        }
        return self::$versions[$class];
    }


    /**
     * @param AwsClient $class
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

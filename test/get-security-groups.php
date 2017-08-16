<?php

require_once __DIR__ . '/../vendor/autoload.php';

$magiumFactory = new \Magium\Configuration\MagiumConfigurationFactory();
$awsFactory = new \Magium\AwsFactory\AwsFactory($magiumFactory->getConfiguration());

$ec2Client = $awsFactory->factory(\Aws\Ec2\Ec2Client::class);
/** @var $ec2Client \Aws\Ec2\Ec2Client */
$result = $ec2Client->describeSecurityGroups();
/** @var $result \Aws\Result */
$groups = $result->get('SecurityGroups');

foreach ($groups as $count => $group) {
    echo sprintf("\nSecurity Group: %d\n", $count);
    foreach ($group as $name => $value) {
        if (is_string($value)) {
            echo sprintf("%s: %s\n", $name, $value);
        }
    }
}

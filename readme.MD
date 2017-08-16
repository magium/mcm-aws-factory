# AWS API Factory for the Magium Configuration Manager

This library provides an interface for the aws/aws-sdk-php library so you can use it with the [Magium Configuration Manager](https://magiumlib.com/components/configuration).  Often applications will have some kind of static configuration mechanism, such as XML files, JSON files, YAML files, or PHP files.  There's nothing necessarily wrong with that, but what it does is merge your deployment and configuration concerns.  The Magium Configuration Manager (MCM) breaks that dependency so you can manage configuration separately from your deployment.

## Setup

```
composer require magium/mcm-aws-factory
```

Once it is installed you need to initialize the Magium Configuration Manager (MCM) for your project using the `magium-configuration` commnand.  You can find it in `vendor/bin/magium-configuration` or, if that doesn't work you can run `php vendor/magium/configuration-manager/bin/magium-configuration`.  For the purpose of this documentation we will simple call it `magium-configuration`.

## Configuration

First, list all the configuration keys so you can see what they are.

```
$ magium-configuration magium:configuration:list-keys
Valid configuration keys
aws/credentials/region

aws/credentials/key

aws/credentials/secret
```

Then you need to set the settings:

```
$ magium-configuration set aws/general/region us-east-1
Set aws/general/region to us-east1 (context: default)
Don't forget to rebuild your configuration cache with magium:configuration:build

$ magium-configuration set aws/general/key xxxxxxxxxxxxxxxxxxx
Set aws/general/key to xxxxxxxxxxxxxxxxxxx (context: default)
Don't forget to rebuild your configuration cache with magium:configuration:build

$ magium-configuration set aws/general/secret xxxxxxxxxx
Set aws/general/secret to xxxxxxxxxxx (context: default)
Don't forget to rebuild your configuration cache with magium:configuration:build
```

Then you need to build the configuration:

```
$ magium-configuration build
Building context: default
Building context: production
Building context: development
```

## Usage

Next up, in your application code run something like this:

```
$magiumFactory = new \Magium\Configuration\MagiumConfigurationFactory();
$awsFactory = new \Magium\AwsFactory\AwsFactory($magiumFactory->getConfiguration());

$ec2Client = $awsFactory->factory(\Aws\Ec2\Ec2Client::class);
$result = $ec2Client->describeSecurityGroups();
$groups = $result->get('SecurityGroups');

foreach ($groups as $count => $group) {
    echo sprintf("\nSecurity Group: %d\n", $count);
    foreach ($group as $name => $value) {
        if (is_string($value)) {
            echo sprintf("%s: %s\n", $name, $value);
        }
    }
}

```

You can try this in the [test/get-security-groups.php](sample test script).

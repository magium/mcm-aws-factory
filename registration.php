<?php

$repository = \Magium\Configuration\File\Configuration\ConfigurationFileRepository::getInstance();
$repository->addSecureBase(__DIR__ . '/etc');
$repository->registerConfigurationFile(new \Magium\Configuration\File\Configuration\XmlFile(realpath(__DIR__ . '/etc/settings.xml')));

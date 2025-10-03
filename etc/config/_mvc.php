<?php

// cli colors
$sColCmd = "\033[0;36m";
$sColOff = "\033[0m";

/*
 * add command for emvicy console
 */
$aConfig['EMVICY_CONSOLE'][] = array(

    'register' => 'openapi:datatype',
    'aliases' => ['oadt'],
    'description' => $sColCmd . "php emvicy openapi:datatype" . $sColOff . " => generates local DataType Classes from OpenAPi YAML Documents",
    'argumentName' => '',
    'argumentMode' => 2, # 1=REQUIRED; 2=OPTIONAL
    'code' => function (\Symfony\Component\Console\Input\InputInterface $oInputInterface, \Symfony\Component\Console\Output\OutputInterface $oOutputInterface): int {

        // get services from config
        $aService = (\MVC\Config::MODULE()['service'] ?? array());

        // iterate
        foreach ($aService as $sKey => $aData)
        {
            // skip
            if (false === isset($aData['aOpenApi']['sLocation']) || true === empty($aData['aOpenApi']['sLocation']))
            {
                continue;
            }

            // generate from location
            \OpenApi\Model\Generate::DTClassesOnOpenapi3yaml(
                sOpenApiFile: $aData['aOpenApi']['sLocation'],
                sSubDirName: $sKey,
                bUnlinkDir: true,
                bValueFromExample: false,
                bDebug: true
            );
        }

        return \Symfony\Component\Console\Command\Command::SUCCESS;
    }
);
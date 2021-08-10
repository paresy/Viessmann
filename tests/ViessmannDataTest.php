<?php

declare(strict_types=1);

include_once __DIR__ . '/stubs/GlobalStubs.php';
include_once __DIR__ . '/stubs/KernelStubs.php';
include_once __DIR__ . '/stubs/ModuleStubs.php';
include_once __DIR__ . '/stubs/ConstantStubs.php';

use PHPUnit\Framework\TestCase;

class ViessmannDataTest extends TestCase
{
    protected function setUp(): void
    {
        //Reset
        IPS\Kernel::reset();

        //Register our library we need for testing
        IPS\ModuleLoader::loadLibrary(__DIR__ . '/../library.json');

        //Create required profiles
        if (!IPS\ProfileManager::variableProfileExists('~Switch')) {
            IPS\ProfileManager::createVariableProfile('~Switch', 0);
        }
        if (!IPS\ProfileManager::variableProfileExists('~Temperature')) {
            IPS\ProfileManager::createVariableProfile('~Temperature', 2);
        }
        if (!IPS\ProfileManager::variableProfileExists('~Temperature.Room')) {
            IPS\ProfileManager::createVariableProfile('~Temperature.Room', 2);
        }
        if (!IPS\ProfileManager::variableProfileExists('~Electricity')) {
            IPS\ProfileManager::createVariableProfile('~Electricity', 2);
        }
        if (!IPS\ProfileManager::variableProfileExists('~Gas')) {
            IPS\ProfileManager::createVariableProfile('~Gas', 2);
        }
        if (!IPS\ProfileManager::variableProfileExists('~Watt.3680')) {
            IPS\ProfileManager::createVariableProfile('~Watt.3680', 2);
        }
        if (!IPS\ProfileManager::variableProfileExists('~Power')) {
            IPS\ProfileManager::createVariableProfile('~Power', 2);
        }
        if (!IPS\ProfileManager::variableProfileExists('~Valve.F')) {
            IPS\ProfileManager::createVariableProfile('~Valve.F', 2);
        }
        if (!IPS\ProfileManager::variableProfileExists('~UnixTimestamp')) {
            IPS\ProfileManager::createVariableProfile('~UnixTimestamp', 1);
        }

        parent::setUp();
    }

    public function testData1(): void
    {
        $this->testData('1');
    }

    public function testData2(): void
    {
        $this->testData('2');
    }

    public function testData3(): void
    {
        $this->testData('3');
    }

    public function testData4(): void
    {
        $this->testData('4');
    }

    public function testData5(): void
    {
        $this->testData('5');
    }

    public function testData6(): void
    {
        $this->testData('6');
    }

    private function testData($name, $write = false): void
    {
        $iid = IPS_CreateInstance('{3BF2B1B8-BD31-4A06-8C70-FD0FF95FE22E}');
        $interface = IPS\InstanceManager::getInstanceInterface($iid);
        $interface->DebugParseDeviceData(json_decode(file_get_contents(__DIR__ . '/data/' . $name . '.json')));

        $values = [];
        foreach (IPS_GetChildrenIDs($iid) as $id) {
            $values[IPS_GetObject($id)['ObjectIdent']] = [
                'type'    => IPS_GetVariable($id)['VariableType'],
                'profile' => IPS_GetVariable($id)['VariableProfile'],
                'action'  => IPS_GetVariable($id)['VariableAction'] > 0,
                'value'   => GetValue($id)
            ];
        }

        if ($write) {
            file_put_contents(__DIR__ . '/results/' . $name . '.json', json_encode($values, JSON_PRETTY_PRINT));
        }

        $results = json_decode(file_get_contents(__DIR__ . '/results/' . $name . '.json'), true);

        $this->assertEquals(count($results), count($values));

        foreach ($results as $key => $value) {
            $this->assertTrue(isset($values[$key]));
            $this->assertEquals($values[$key], $value);
        }
    }
}

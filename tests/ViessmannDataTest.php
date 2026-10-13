<?php

declare(strict_types=1);

include_once __DIR__ . '/stubs/autoload.php';

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

    public function testData7(): void
    {
        $this->testData('7');
    }

    public function testData8(): void
    {
        $this->testData('8');
    }

    public function testActions(): void
    {
        $iid = $this->createInstance('1');
        $this->assertEquals([['action' => 'heating.circuits.0.operating.programs.comfort/commands/setTemperature', 'params' => ['targetTemperature' => 21]]], $this->requestAction($iid, 'heating_circuits_0_operating_programs_comfort_temperature', 21));
        $this->assertEquals([['action' => 'heating.circuits.0.operating.programs.comfort/commands/activate', 'params' => []]], $this->requestAction($iid, 'heating_circuits_0_operating_programs_comfort_active', true));
        $this->assertEquals([['action' => 'heating.circuits.0.operating.modes.active/commands/setMode', 'params' => ['mode' => 'standby']]], $this->requestAction($iid, 'heating_circuits_0_operating_modes_active_value', 'standby'));
        $this->assertEquals([['action' => 'heating.dhw.temperature.main/commands/setTargetTemperature', 'params' => ['temperature' => 45]]], $this->requestAction($iid, 'heating_dhw_temperature_main_value', 45));

        $iid = $this->createInstance('7');
        // Required parameters are filled with the current values of the other properties
        $this->assertEquals([['action' => 'device.time.daylightSaving/commands/activate', 'params' => ['begin' => '25-03', 'end' => '25-10']]], $this->requestAction($iid, 'device_time_daylightSaving_active', true));
        $this->assertEquals([['action' => 'device.time.daylightSaving/commands/deactivate', 'params' => []]], $this->requestAction($iid, 'device_time_daylightSaving_active', false));
        $this->assertEquals([['action' => 'heating.dhw.hygiene/commands/setEnabled', 'params' => ['enabled' => true]]], $this->requestAction($iid, 'heating_dhw_hygiene_enabled', true));

        $iid = $this->createInstance('8');
        $this->assertEquals([['action' => 'heating.dhw.temperature.hysteresis/commands/setHysteresisSwitchOnValue', 'params' => ['hysteresis' => 4]]], $this->requestAction($iid, 'heating_dhw_temperature_hysteresis_switchonvalue', 4));
        $this->assertEquals([['action' => 'heating.compressors.0/commands/setActive', 'params' => ['active' => false]]], $this->requestAction($iid, 'heating_compressors_0_active', false));
    }

    public function testActionsWithParameters(): void
    {
        $feature = function ($commands)
        {
            return json_decode(json_encode(['data' => [[
                'feature'    => 'heating.test',
                'properties' => [
                    'active' => ['type' => 'boolean', 'value' => false],
                    'begin'  => ['type' => 'string', 'value' => '25-03'],
                ],
                'commands' => $commands
            ]]]));
        };

        $iid = IPS_CreateInstance('{3BF2B1B8-BD31-4A06-8C70-FD0FF95FE22E}');
        $interface = IPS\InstanceManager::getInstanceInterface($iid);

        // setActive requires all parameters, which are filled from the other properties
        $interface->DebugParseDeviceData($feature(['setActive' => ['name' => 'setActive', 'params' => [
            'active' => ['type' => 'boolean', 'required' => true],
            'begin'  => ['type' => 'string', 'required' => true],
        ]]]));
        $this->assertEquals([['action' => 'heating.test/commands/setActive', 'params' => ['active' => true, 'begin' => '25-03']]], $this->requestAction($iid, 'heating_test_active', true));

        // We cannot fill a required parameter without a matching property. Therefore, no action
        $interface->DebugParseDeviceData($feature(['setActive' => ['name' => 'setActive', 'params' => [
            'active' => ['type' => 'boolean', 'required' => true],
            'end'    => ['type' => 'string', 'required' => true],
        ]]]));
        $this->assertFalse(IPS_GetVariable(IPS_GetObjectIDByIdent('heating_test_active', $iid))['VariableAction'] > 0);

        // Actions are disabled, if the command is not available anymore
        $interface->DebugParseDeviceData($feature(['setActive' => ['name' => 'setActive', 'params' => [
            'active' => ['type' => 'boolean', 'required' => true],
        ]]]));
        $this->assertTrue(IPS_GetVariable(IPS_GetObjectIDByIdent('heating_test_active', $iid))['VariableAction'] > 0);
        $interface->DebugParseDeviceData($feature([]));
        $this->assertFalse(IPS_GetVariable(IPS_GetObjectIDByIdent('heating_test_active', $iid))['VariableAction'] > 0);

        $this->expectExceptionMessage('Invalid Ident');
        $this->requestAction($iid, 'heating_test_active', true);
    }

    private function createInstance($name)
    {
        $iid = IPS_CreateInstance('{3BF2B1B8-BD31-4A06-8C70-FD0FF95FE22E}');
        IPS\InstanceManager::getInstanceInterface($iid)->DebugParseDeviceData(json_decode(file_get_contents(__DIR__ . '/data/' . $name . '.json')));
        return $iid;
    }

    private function requestAction($iid, $ident, $value)
    {
        return IPS\InstanceManager::getInstanceInterface($iid)->DebugRequestAction($ident, $value);
    }

    private function supportedCommands(): array
    {
        return [
            'setTemperature',
            'setTargetTemperature',
            'setMode',
            'setHysteresis',
            'setHysteresisSwitchOnValue',
            'setHysteresisSwitchOffValue',
            'setMin',
            'setMax',
            'setLevels', // This combines setMin/setMax which we support separately
            'activate',
            'deactivate',
            'setName', // We don't need that
            'setCurve', // TODO
            'enable', // We probably don't need that [Hygiene related]
            'disable', // We probably don't need that [Hygiene related]
            'triggerDaily', // We probably don't need that [Hygiene related]
            'triggerOncePerWeek', // We probably don't need that [Hygiene related]
            'schedule', // We don't have a nice UI element for that [Holiday related]
            'setSchedule', // We don't have a nice UI element for that [Holiday related]
            'changeEndDate', // We don't have a nice UI element for that [Holiday related]
            'unschedule', // We don't have a nice UI element for that [Holiday related]
            'resetSchedule', // We don't have a nice UI element for that [Schedule related]
            'resetDay', // We don't have a nice UI element for that [Schedule related]
            'setActive', // Supported as fallback, if activate/deactivate are not available
            'setEnabled',
            'setPrin', // We don't need that [Serial related]
            'setPhase', // We don't need that [Compressor related]
        ];
    }

    private function testData($name, $write = false): void
    {
        $iid = IPS_CreateInstance('{3BF2B1B8-BD31-4A06-8C70-FD0FF95FE22E}');
        $interface = IPS\InstanceManager::getInstanceInterface($iid);
        $device = json_decode(file_get_contents(__DIR__ . '/data/' . $name . '.json'));
        $interface->DebugParseDeviceData($device);

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

        foreach ($device->data as $entity) {
            foreach ($entity->commands as $command) {
                $this->assertTrue(in_array($command->name, $this->supportedCommands()), 'Unsupported command: ' . $command->name);
            }
        }
    }
}

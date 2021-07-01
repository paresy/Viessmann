<?php

declare(strict_types=1);

include_once __DIR__ . '/stubs/GlobalStubs.php';
include_once __DIR__ . '/stubs/KernelStubs.php';
include_once __DIR__ . '/stubs/ModuleStubs.php';

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

        parent::setUp();
    }

    public function testData1(): void
    {
        $iid = IPS_CreateInstance('{3BF2B1B8-BD31-4A06-8C70-FD0FF95FE22E}');
        $interface = IPS\InstanceManager::getInstanceInterface($iid);
        $interface->DebugParseDeviceData(json_decode(file_get_contents(__DIR__ . '/data/1.json')));

        $values = [];
        foreach (IPS_GetChildrenIDs($iid) as $id) {
            $values[IPS_GetObject($id)['ObjectIdent']] = GetValue($id);
        }

        //var_export($values);
        $testValues = [
            'heating_device_time_offset_value'                           => 105.0,
            'heating_controller_serial_value'                            => '7545825525025102',
            'heating_circuits_0_operating_modes_standby_active'          => false,
            'heating_circuits_0_operating_programs_normal_active'        => true,
            'heating_circuits_0_operating_programs_normal_demand'        => 'unknown',
            'heating_circuits_0_operating_programs_normal_temperature'   => 21.0,
            'heating_dhw_temperature_value'                              => 50.0,
            'heating_dhw_sensors_temperature_hotWaterStorage_value'      => 49.5,
            'heating_boiler_temperature_value'                           => 5.0,
            'heating_circuits_0_operating_programs_external_active'      => false,
            'heating_circuits_0_operating_programs_external_temperature' => 0.0,
            'heating_dhw_active'                                         => true,
            'heating_circuits_0_operating_programs_comfort_active'       => false,
            'heating_circuits_0_operating_programs_comfort_demand'       => 'unknown',
            'heating_circuits_0_operating_programs_comfort_temperature'  => 22.0,
            'heating_dhw_charging_active'                                => false,
            'heating_circuits_0_operating_modes_dhwAndHeating_active'    => true,
            'heating_sensors_temperature_outside_value'                  => 26.9,
            'heating_circuits_0_operating_programs_active_value'         => 'normal',
            'heating_boiler_serial_value'                                => '7519080502092101',
            'heating_circuits_enabled'                                   => '["0"]',
            'heating_boiler_sensors_temperature_main_value'              => 48.0,
            'heating_circuits_0_operating_modes_dhw_active'              => false,
            'heating_dhw_sensors_temperature_outlet_value'               => 35.8,
            'heating_circuits_0_active'                                  => true,
            'heating_circuits_0_name'                                    => 'Heizkreis 1',
            'heating_circuits_0_type'                                    => 'heatingCircuit',
            'heating_circuits_0_sensors_temperature_supply_value'        => 48.0,
            'heating_circuits_0_operating_programs_standby_active'       => false,
            'heating_configuration_multiFamilyHouse_active'              => false,
            'heating_circuits_0_sensors_temperature_room_value'          => 25.3,
            'heating_dhw_temperature_main_value'                         => 50.0,
            'heating_circuits_0_operating_programs_reduced_active'       => false,
            'heating_circuits_0_operating_programs_reduced_demand'       => 'unknown',
            'heating_circuits_0_operating_programs_reduced_temperature'  => 18.0,
            'heating_dhw_schedule_active'                                => true,
            'heating_circuits_0_operating_programs_eco_active'           => false,
            'heating_circuits_0_operating_programs_eco_temperature'      => 21.0,
            'heating_burner_active'                                      => false,
            'heating_circuits_0_heating_schedule_active'                 => true,
            'heating_dhw_pumps_circulation_schedule_active'              => true,
            'heating_operating_programs_holiday_active'                  => false,
            'heating_operating_programs_holiday_start'                   => '',
            'heating_operating_programs_holiday_end'                     => '',
            'heating_circuits_0_operating_modes_active_value'            => 'dhwAndHeating',
        ];

        $this->assertEquals(count($testValues), count($values));

        foreach ($testValues as $key => $value) {
            $this->assertTrue(isset($values[$key]));
            $this->assertEquals($values[$key], $value);
        }
    }

    public function testData2(): void
    {
        $iid = IPS_CreateInstance('{3BF2B1B8-BD31-4A06-8C70-FD0FF95FE22E}');
        $interface = IPS\InstanceManager::getInstanceInterface($iid);
        $interface->DebugParseDeviceData(json_decode(file_get_contents(__DIR__ . '/data/2.json')));

        $values = [];
        foreach (IPS_GetChildrenIDs($iid) as $id) {
            $values[IPS_GetObject($id)['ObjectIdent']] = GetValue($id);
        }

        //var_export($values);
        $testValues = [
            'device_serial_value'                                                 => '7722310001608124',
            'heating_circuits_0_operating_programs_comfort_active'                => false,
            'heating_circuits_0_operating_programs_comfort_demand'                => 'unknown',
            'heating_circuits_0_operating_programs_comfort_temperature'           => 20.0,
            'heating_dhw_hygiene_trigger_weekdays'                                => '["Mon"]',
            'heating_dhw_hygiene_trigger_starthour'                               => 22.0,
            'heating_dhw_hygiene_trigger_startminute'                             => 30.0,
            'heating_burner_active'                                               => false,
            'heating_circuits_0_operating_programs_reduced_active'                => false,
            'heating_circuits_0_operating_programs_reduced_demand'                => 'unknown',
            'heating_circuits_0_operating_programs_reduced_temperature'           => 20.0,
            'heating_operating_programs_holidayAtHome_active'                     => false,
            'heating_operating_programs_holidayAtHome_start'                      => '',
            'heating_operating_programs_holidayAtHome_end'                        => '',
            'heating_circuits_0_active'                                           => true,
            'heating_circuits_0_name'                                             => 'Heizkreis 1',
            'heating_circuits_0_type'                                             => 'heatingCircuit',
            'heating_dhw_temperature_value'                                       => 50.0,
            'heating_circuits_0_sensors_temperature_supply_value'                 => 34.4,
            'heating_circuits_0_operating_modes_dhw_active'                       => true,
            'heating_circuits_0_temperature_value'                                => 0.0,
            'heating_dhw_oneTimeCharge_active'                                    => false,
            'heating_dhw_sensors_temperature_hotWaterStorage_value'               => 60.4,
            'heating_dhw_temperature_main_value'                                  => 50.0,
            'heating_circuits_0_operating_programs_normal_active'                 => false,
            'heating_circuits_0_operating_programs_normal_demand'                 => 'unknown',
            'heating_circuits_0_operating_programs_normal_temperature'            => 20.0,
            'heating_circuits_0_operating_modes_heating_active'                   => false,
            'heating_circuits_0_operating_modes_standby_active'                   => false,
            'heating_boiler_sensors_temperature_commonSupply_value'               => 34.4,
            'heating_dhw_hygiene_enabled'                                         => true,
            'heating_dhw_hygiene_active'                                          => false,
            'heating_circuits_0_operating_modes_active_value'                     => 'dhw',
            'heating_configuration_multiFamilyHouse_active'                       => false,
            'heating_circuits_0_operating_programs_forcedLastFromSchedule_active' => false,
            'heating_circuits_enabled'                                            => '["0"]',
            'heating_circuits_0_operating_programs_active_value'                  => 'standby',
            'heating_dhw_schedule_active'                                         => true,
            'heating_boiler_temperature_value'                                    => 20.0,
            'heating_dhw_active'                                                  => true,
            'heating_circuits_0_operating_modes_dhwAndHeating_active'             => false,
            'heating_circuits_0_operating_programs_standby_active'                => true,
            'heating_circuits_0_heating_schedule_active'                          => false,
            'heating_boiler_serial_value'                                         => '7722310001608124',
            'heating_operating_programs_holiday_active'                           => false,
            'heating_operating_programs_holiday_start'                            => '',
            'heating_operating_programs_holiday_end'                              => '',
            'heating_device_time_offset_value'                                    => 64.0,
            'heating_dhw_temperature_hygiene_value'                               => 65.0,
        ];

        $this->assertEquals(count($testValues), count($values));

        foreach ($testValues as $key => $value) {
            $this->assertTrue(isset($values[$key]));
            $this->assertEquals($values[$key], $value);
        }
    }

    public function testData3(): void
    {
        $iid = IPS_CreateInstance('{3BF2B1B8-BD31-4A06-8C70-FD0FF95FE22E}');
        $interface = IPS\InstanceManager::getInstanceInterface($iid);
        $interface->DebugParseDeviceData(json_decode(file_get_contents(__DIR__ . '/data/3.json')));

        $values = [];
        foreach (IPS_GetChildrenIDs($iid) as $id) {
            $values[IPS_GetObject($id)['ObjectIdent']] = GetValue($id);
        }

        //var_export($values);
        $testValues = [
            'heating_controller_serial_value'                           => '',
            'ventilation_operating_programs_standard_active'            => false,
            'ventilation_operating_programs_standard_temperature'       => 16.0,
            'ventilation_operating_programs_reduced_active'             => false,
            'heating_circuits_0_operating_programs_reduced_active'      => false,
            'heating_circuits_0_operating_programs_reduced_demand'      => 'unknown',
            'heating_circuits_0_operating_programs_reduced_temperature' => 22.0,
            'heating_circuits_0_active'                                 => true,
            'heating_circuits_0_name'                                   => '',
            'heating_circuits_0_type'                                   => 'heatingCircuit',
            'ventilation_operating_programs_basic_active'               => true,
            'heating_dhw_oneTimeCharge_active'                          => false,
            'heating_circuits_2_temperature_value'                      => 0.0,
            'heating_dhw_sensors_temperature_hotWaterStorage_value'     => 49.3,
            'heating_circuits_0_operating_programs_normal_active'       => false,
            'heating_circuits_0_operating_programs_normal_demand'       => 'unknown',
            'heating_circuits_0_operating_programs_normal_temperature'  => 22.0,
            'ventilation_operating_modes_standby_active'                => false,
            'heating_circuits_0_operating_modes_standby_active'         => false,
            'heating_dhw_sensors_temperature_hotWaterStorage_top_value' => 49.3,
            'heating_circuits_0_operating_programs_active_value'        => 'standby',
            'heating_dhw_temperature_temp2_value'                       => 50.0,
            'heating_circuits_0_temperature_levels_min'                 => 10.0,
            'heating_circuits_0_temperature_levels_minunit'             => 'celsius',
            'heating_circuits_0_temperature_levels_max'                 => 40.0,
            'heating_circuits_0_temperature_levels_maxunit'             => 'celsius',
            'ventilation_operating_modes_ventilation_active'            => false,
            'ventilation_operating_modes_standard_active'               => true,
            'heating_circuits_0_operating_modes_dhwAndHeating_active'   => false,
            'ventilation_operating_modes_active_value'                  => 'standard',
            'heating_sensors_temperature_return_value'                  => 22.7,
            'heating_dhw_temperature_hysteresis_value'                  => 5.0,
            'heating_circuits_0_operating_programs_eco_active'          => false,
            'heating_circuits_0_operating_programs_eco_temperature'     => 22.0,
            'heating_operating_programs_holiday_active'                 => false,
            'heating_operating_programs_holiday_start'                  => '',
            'heating_operating_programs_holiday_end'                    => '',
            'heating_device_time_offset_value'                          => 120.0,
            'ventilation_operating_programs_intensive_active'           => false,
            'heating_circuits_0_operating_programs_comfort_active'      => false,
            'heating_circuits_0_operating_programs_comfort_demand'      => 'unknown',
            'heating_circuits_0_operating_programs_comfort_temperature' => 22.0,
            'heating_sensors_temperature_outside_value'                 => 33.4,
            'heating_circuits_1_temperature_value'                      => 0.0,
            'ventilation_schedule_active'                               => false,
            'heating_compressors_enabled'                               => '["0"]',
            'heating_dhw_temperature_value'                             => 50.0,
            'heating_circuits_0_sensors_temperature_supply_value'       => 24.4,
            'heating_circuits_0_operating_modes_dhw_active'             => true,
            'heating_circuits_0_temperature_value'                      => 0.0,
            'ventilation_operating_programs_standby_active'             => false,
            'heating_dhw_temperature_main_value'                        => 50.0,
            'heating_secondaryCircuit_sensors_temperature_return_value' => 22.7,
            'heating_primaryCircuit_sensors_temperature_supply_value'   => 21.7,
            'heating_compressors_0_active'                              => false,
            'heating_compressors_0_phase'                               => 'off',
            'heating_circuits_0_operating_modes_normalStandby_active'   => false,
            'heating_circuits_0_operating_modes_active_value'           => 'dhw',
            'heating_configuration_multiFamilyHouse_active'             => false,
            'heating_circuits_0_operating_programs_fixed_active'        => false,
            'heating_secondaryCircuit_sensors_temperature_supply_value' => 24.4,
            'heating_circuits_enabled'                                  => '["0"]',
            'heating_dhw_charging_active'                               => false,
            'heating_dhw_schedule_active'                               => true,
            'heating_dhw_active'                                        => true,
            'ventilation_operating_programs_active_value'               => 'basic',
            'ventilation_active'                                        => true,
            'heating_circuits_0_operating_programs_standby_active'      => true,
            'heating_dhw_pumps_circulation_schedule_active'             => true,
            'heating_circuits_0_heating_schedule_active'                => false,
            'heating_boiler_serial_value'                               => '7501983301033102',
        ];

        $this->assertEquals(count($testValues), count($values));

        foreach ($testValues as $key => $value) {
            $this->assertTrue(isset($values[$key]));
            $this->assertEquals($values[$key], $value);
        }
    }
}

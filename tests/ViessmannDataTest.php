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
            'heating_circuits_1_operating_programs_standby_active'       => false,
            'heating_dhw_temperature_main_value'                         => 45.0,
            'heating_circuits_1_sensors_temperature_supply_value'        => 50.5,
            'heating_configuration_multiFamilyHouse_active'              => false,
            'heating_dhw_schedule_active'                                => true,
            'heating_circuits_1_operating_modes_active_value'            => 'dhwAndHeating',
            'heating_controller_serial_value'                            => '0000000000000000',
            'heating_burner_statistics_hours'                            => 6148.7,
            'heating_burner_statistics_starts'                           => 54663.0,
            'heating_circuits_1_operating_programs_comfort_active'       => false,
            'heating_circuits_1_operating_programs_comfort_temperature'  => 20.0,
            'heating_circuits_1_operating_modes_dhwAndHeating_active'    => true,
            'heating_circuits_1_heating_schedule_active'                 => true,
            'heating_circuits_1_operating_programs_eco_active'           => false,
            'heating_circuits_1_operating_programs_eco_temperature'      => 23.0,
            'heating_circuits_1_operating_programs_reduced_active'       => false,
            'heating_circuits_1_operating_programs_reduced_temperature'  => 18.0,
            'heating_dhw_charging_active'                                => false,
            'heating_burner_active'                                      => true,
            'heating_circuits_1_operating_programs_normal_active'        => true,
            'heating_circuits_1_operating_programs_normal_temperature'   => 23.0,
            'heating_dhw_pumps_circulation_schedule_active'              => true,
            'heating_boiler_serial_value'                                => '0000000000000000',
            'heating_solar_active'                                       => true,
            'heating_device_time_offset_value'                           => 53.0,
            'heating_solar_sensors_temperature_dhw_value'                => 35.1,
            'heating_boiler_sensors_temperature_main_value'              => 58.0,
            'heating_boiler_sensors_temperature_commonSupply_value'      => 53.6,
            'heating_burner_automatic_errorcode'                         => 0.0,
            'heating_circuits_1_operating_programs_external_active'      => false,
            'heating_circuits_1_operating_programs_external_temperature' => 0.0,
            'heating_operating_programs_holiday_active'                  => false,
            'heating_boiler_temperature_value'                           => 49.4,
            'heating_circuits_1_operating_modes_standby_active'          => false,
            'heating_circuits_1_operating_programs_holiday_active'       => false,
            'heating_circuits_1_active'                                  => true,
            'heating_circuits_1_name'                                    => '',
            'heating_circuits_1_heating_curve_shift'                     => 5.0,
            'heating_circuits_1_heating_curve_slope'                     => 1.4,
            'heating_dhw_temperature_value'                              => 45.0,
            'heating_sensors_temperature_outside_value'                  => 13.8,
            'heating_circuits_1_operating_modes_forcedReduced_active'    => false,
            'heating_solar_power_production_day'                         => 26.623,
            'heating_dhw_sensors_temperature_hotWaterStorage_value'      => 59.2,
            'heating_burner_modulation_value'                            => 43.0,
            'heating_circuits_1_operating_modes_dhw_active'              => false,
            'heating_circuits_1_operating_modes_forcedNormal_active'     => false,
            'heating_dhw_active'                                         => true,
            'heating_circuits_1_operating_programs_active_value'         => 'normal',
            'heating_circuits_0_geofencing_active'                       => false,
            'heating_circuits_1_geofencing_active'                       => false,
            'heating_circuits_2_geofencing_active'                       => false,
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
            'heating_boiler_serial_value'                                => '0000000000000000',
            'heating_device_time_offset_value'                           => 115.0,
            'heating_circuits_0_operating_programs_external_active'      => false,
            'heating_circuits_0_operating_programs_external_temperature' => 0.0,
            'heating_circuits_0_operating_modes_dhw_active'              => false,
            'heating_circuits_0_heating_curve_shift'                     => 0.0,
            'heating_circuits_0_heating_curve_slope'                     => 1.4,
            'heating_dhw_active'                                         => true,
            'heating_operating_programs_holiday_active'                  => false,
            'heating_circuits_0_operating_modes_active_value'            => 'dhwAndHeating',
            'heating_circuits_0_sensors_temperature_room_value'          => 21.3,
            'heating_dhw_pumps_circulation_schedule_active'              => true,
            'heating_circuits_0_operating_programs_reduced_active'       => false,
            'heating_circuits_0_operating_programs_reduced_temperature'  => 18.0,
            'heating_circuits_0_operating_programs_active_value'         => 'normal',
            'heating_controller_serial_value'                            => '0000000000000000',
            'heating_circuits_0_operating_programs_comfort_active'       => false,
            'heating_circuits_0_operating_programs_comfort_temperature'  => 37.0,
            'heating_burner_modulation_value'                            => 0.0,
            'heating_circuits_0_sensors_temperature_supply_value'        => 24.0,
            'heating_burner_statistics_hours'                            => 7498.4,
            'heating_burner_statistics_starts'                           => 27165.0,
            'heating_circuits_0_operating_programs_normal_active'        => true,
            'heating_circuits_0_operating_programs_normal_temperature'   => 21.0,
            'heating_circuits_0_operating_modes_dhwAndHeating_active'    => true,
            'heating_circuits_0_operating_programs_standby_active'       => false,
            'heating_circuits_0_operating_programs_eco_active'           => false,
            'heating_circuits_0_operating_programs_eco_temperature'      => 21.0,
            'heating_boiler_sensors_temperature_main_value'              => 24.0,
            'heating_boiler_temperature_value'                           => 30.2,
            'heating_dhw_temperature_value'                              => 50.0,
            'heating_burner_active'                                      => false,
            'heating_circuits_0_heating_schedule_active'                 => true,
            'heating_sensors_temperature_outside_value'                  => 15.6,
            'heating_dhw_sensors_temperature_outlet_value'               => 37.3,
            'heating_circuits_0_operating_modes_forcedReduced_active'    => false,
            'heating_circuits_0_operating_modes_standby_active'          => false,
            'heating_configuration_multiFamilyHouse_active'              => false,
            'heating_circuits_0_active'                                  => true,
            'heating_circuits_0_name'                                    => 'Heizkreis 1',
            'heating_circuits_0_operating_programs_holiday_active'       => false,
            'heating_burner_automatic_errorcode'                         => 0.0,
            'heating_circuits_0_operating_modes_forcedNormal_active'     => false,
            'heating_dhw_schedule_active'                                => true,
            'heating_dhw_sensors_temperature_hotWaterStorage_value'      => 51.8,
            'heating_dhw_temperature_main_value'                         => 50.0,
            'heating_dhw_charging_active'                                => false,
            'heating_circuits_0_geofencing_active'                       => false,
            'heating_circuits_1_geofencing_active'                       => false,
            'heating_circuits_2_geofencing_active'                       => false,
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
            $values[IPS_GetObject($id)['ObjectIdent']] = [
                'value'   => IPS_GetVariable($id)['VariableValue'],
                'profile' => IPS_GetVariable($id)['VariableProfile']
            ];
        }

        //var_export($values);
        $testValues = [
            'heating_dhw_sensors_temperature_hotWaterStorage_value' => [
                'value'   => 54.7,
                'profile' => '~Temperature',
            ],
            'device_etn_value' => [
                'value'   => '7470379002130126',
                'profile' => '',
            ],
            'heating_circuits_0_operating_modes_heating_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_circuits_0_operating_programs_normal_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_circuits_0_operating_programs_normal_temperature' => [
                'value'   => 60.0,
                'profile' => '~Temperature',
            ],
            'heating_circuits_0_operating_programs_reduced_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_circuits_0_operating_programs_reduced_temperature' => [
                'value'   => 20.0,
                'profile' => '~Temperature',
            ],
            'device_zigbee_active_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_sensors_pressure_supply_value' => [
                'value'   => 1.8,
                'profile' => '',
            ],
            'heating_gas_consumption_total_day' => [
                'value'   => 0.0,
                'profile' => '~Gas',
            ],
            'heating_gas_consumption_total_week' => [
                'value'   => 1.1,
                'profile' => '~Gas',
            ],
            'heating_gas_consumption_total_month' => [
                'value'   => 4.3,
                'profile' => '~Gas',
            ],
            'heating_gas_consumption_total_year' => [
                'value'   => 4.3,
                'profile' => '~Gas',
            ],
            'heating_boiler_sensors_temperature_commonSupply_value' => [
                'value'   => 37.7,
                'profile' => '~Temperature',
            ],
            'heating_circuits_0_sensors_temperature_supply_value' => [
                'value'   => 37.7,
                'profile' => '~Temperature',
            ],
            'heating_burner_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_power_consumption_total_day' => [
                'value'   => 0.0,
                'profile' => '~Electricity',
            ],
            'heating_power_consumption_total_week' => [
                'value'   => 0.2,
                'profile' => '~Electricity',
            ],
            'heating_power_consumption_total_month' => [
                'value'   => 1.3,
                'profile' => '~Electricity',
            ],
            'heating_power_consumption_total_year' => [
                'value'   => 1.3,
                'profile' => '~Electricity',
            ],
            'heating_circuits_0_operating_modes_dhw_active' => [
                'value'   => true,
                'profile' => '~Switch',
            ],
            'heating_configuration_multiFamilyHouse_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_power_consumption_dhw_day' => [
                'value'   => 0.0,
                'profile' => '~Electricity',
            ],
            'heating_power_consumption_dhw_week' => [
                'value'   => 0.0,
                'profile' => '~Electricity',
            ],
            'heating_power_consumption_dhw_month' => [
                'value'   => 0.1,
                'profile' => '~Electricity',
            ],
            'heating_power_consumption_dhw_year' => [
                'value'   => 0.1,
                'profile' => '~Electricity',
            ],
            'heating_dhw_temperature_main_value' => [
                'value'   => 50.0,
                'profile' => '',
            ],
            'heating_circuits_0_operating_modes_dhwAndHeating_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_boiler_temperature_value' => [
                'value'   => 20.0,
                'profile' => '~Temperature',
            ],
            'heating_power_consumption_day' => [
                'value'   => 0.0,
                'profile' => '~Electricity',
            ],
            'heating_power_consumption_week' => [
                'value'   => 0.2,
                'profile' => '~Electricity',
            ],
            'heating_power_consumption_month' => [
                'value'   => 1.3,
                'profile' => '~Electricity',
            ],
            'heating_power_consumption_year' => [
                'value'   => 1.3,
                'profile' => '~Electricity',
            ],
            'heating_dhw_schedule_active' => [
                'value'   => true,
                'profile' => '~Switch',
            ],
            'heating_burner_modulation_value' => [
                'value'   => 0.0,
                'profile' => '',
            ],
            'heating_flue_sensors_temperature_main_value' => [
                'value'   => 36.7,
                'profile' => '~Temperature',
            ],
            'heating_circuits_0_operating_programs_comfort_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_circuits_0_operating_programs_comfort_temperature' => [
                'value'   => 70.0,
                'profile' => '~Temperature',
            ],
            'heating_operating_programs_holidayAtHome_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_circuits_0_operating_programs_forcedLastFromSchedule_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_dhw_active' => [
                'value'   => true,
                'profile' => '~Switch',
            ],
            'heating_gas_consumption_heating_day' => [
                'value'   => 0.0,
                'profile' => '~Gas',
            ],
            'heating_gas_consumption_heating_week' => [
                'value'   => 0.0,
                'profile' => '~Gas',
            ],
            'heating_gas_consumption_heating_month' => [
                'value'   => 0.1,
                'profile' => '~Gas',
            ],
            'heating_gas_consumption_heating_year' => [
                'value'   => 0.1,
                'profile' => '~Gas',
            ],
            'heating_circuits_0_operating_programs_holidayAtHome_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_operating_programs_holiday_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_heat_production_day' => [
                'value'   => 0.0,
                'profile' => '~Electricity',
            ],
            'heating_heat_production_week' => [
                'value'   => 0.0,
                'profile' => '~Electricity',
            ],
            'heating_heat_production_month' => [
                'value'   => 1.0,
                'profile' => '~Electricity',
            ],
            'heating_heat_production_year' => [
                'value'   => 1.0,
                'profile' => '~Electricity',
            ],
            'heating_boiler_serial_value' => [
                'value'   => '7722310001608124',
                'profile' => '',
            ],
            'heating_circuits_0_operating_programs_holiday_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_dhw_oneTimeCharge_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_power_consumption_heating_day' => [
                'value'   => 0.0,
                'profile' => '~Electricity',
            ],
            'heating_power_consumption_heating_week' => [
                'value'   => 0.2,
                'profile' => '~Electricity',
            ],
            'heating_power_consumption_heating_month' => [
                'value'   => 1.2,
                'profile' => '~Electricity',
            ],
            'heating_power_consumption_heating_year' => [
                'value'   => 1.2,
                'profile' => '~Electricity',
            ],
            'heating_device_time_offset_value' => [
                'value'   => 119.0,
                'profile' => '',
            ],
            'heating_circuits_0_operating_programs_active_value' => [
                'value'   => 'standby',
                'profile' => '',
            ],
            'heating_circuits_0_active' => [
                'value'   => true,
                'profile' => '~Switch',
            ],
            'heating_circuits_0_name' => [
                'value'   => '',
                'profile' => '',
            ],
            'heating_configuration_regulation_mode' => [
                'value'   => 'ConstantControlled',
                'profile' => '',
            ],
            'heating_circuits_0_operating_modes_standby_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_dhw_temperature_value' => [
                'value'   => 50.0,
                'profile' => '',
            ],
            'heating_circuits_0_heating_schedule_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_gas_consumption_dhw_day' => [
                'value'   => 0.0,
                'profile' => '~Gas',
            ],
            'heating_gas_consumption_dhw_week' => [
                'value'   => 1.1,
                'profile' => '~Gas',
            ],
            'heating_gas_consumption_dhw_month' => [
                'value'   => 4.2,
                'profile' => '~Gas',
            ],
            'heating_gas_consumption_dhw_year' => [
                'value'   => 4.2,
                'profile' => '~Gas',
            ],
            'device_serial_value' => [
                'value'   => '7722310001608124',
                'profile' => '',
            ],
            'heating_circuits_0_operating_modes_active_value' => [
                'value'   => 'dhw',
                'profile' => '',
            ],
            'heating_circuits_0_operating_programs_standby_active' => [
                'value'   => true,
                'profile' => '~Switch',
            ],
            'heating_circuits_0_geofencing_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_circuits_1_geofencing_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_circuits_2_geofencing_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_circuits_3_geofencing_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
        ];

        $this->assertEquals(count($testValues), count($values));

        foreach ($testValues as $key => $value) {
            $this->assertTrue(isset($values[$key]));
            $this->assertEquals($values[$key], $value);
        }
    }

    public function testData4(): void
    {
        $iid = IPS_CreateInstance('{3BF2B1B8-BD31-4A06-8C70-FD0FF95FE22E}');
        $interface = IPS\InstanceManager::getInstanceInterface($iid);
        $interface->DebugParseDeviceData(json_decode(file_get_contents(__DIR__ . '/data/4.json')));

        $values = [];
        foreach (IPS_GetChildrenIDs($iid) as $id) {
            $values[IPS_GetObject($id)['ObjectIdent']] = [
                'value'   => IPS_GetVariable($id)['VariableValue'],
                'profile' => IPS_GetVariable($id)['VariableProfile']
            ];
        }

        //var_export($values);
        $testValues = [
            'heating_circuits_0_active' => [
                'value'   => true,
                'profile' => '~Switch',
            ],
            'heating_circuits_0_name' => [
                'value'   => 'Fussbodenheizung',
                'profile' => '',
            ],
            'heating_circuits_2_temperature_value' => [
                'value'   => 0.0,
                'profile' => '',
            ],
            'heating_dhw_temperature_main_value' => [
                'value'   => 45.0,
                'profile' => '',
            ],
            'heating_device_mainECU_runtime' => [
                'value'   => 214685558.0,
                'profile' => '',
            ],
            'heating_sensors_temperature_return_value' => [
                'value'   => 24.8,
                'profile' => '~Temperature',
            ],
            'heating_circuits_0_sensors_temperature_supply_value' => [
                'value'   => 24.8,
                'profile' => '~Temperature',
            ],
            'heating_circuits_0_operating_programs_normal_active' => [
                'value'   => true,
                'profile' => '~Switch',
            ],
            'heating_circuits_0_operating_programs_normal_demand' => [
                'value'   => 'unknown',
                'profile' => '',
            ],
            'heating_circuits_0_operating_programs_normal_temperature' => [
                'value'   => 19.0,
                'profile' => '~Temperature',
            ],
            'heating_compressors_0_power_consumption_current_value' => [
                'value'   => 0.0,
                'profile' => '~Watt.3680',
            ],
            'heating_circuits_0_operating_modes_dhwAndHeatingCooling_active' => [
                'value'   => true,
                'profile' => '~Switch',
            ],
            'heating_circuits_0_operating_modes_active_value' => [
                'value'   => 'dhwAndHeatingCooling',
                'profile' => '',
            ],
            'heating_circuits_0_heating_curve_shift' => [
                'value'   => -2.0,
                'profile' => '',
            ],
            'heating_circuits_0_heating_curve_slope' => [
                'value'   => 0.3,
                'profile' => '',
            ],
            'heating_circuits_0_operating_programs_comfort_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_circuits_0_operating_programs_comfort_demand' => [
                'value'   => 'unknown',
                'profile' => '',
            ],
            'heating_circuits_0_operating_programs_comfort_temperature' => [
                'value'   => 20.0,
                'profile' => '~Temperature',
            ],
            'heating_compressors_0_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_compressors_0_phase' => [
                'value'   => 'off',
                'profile' => '',
            ],
            'heating_circuits_1_temperature_value' => [
                'value'   => 0.0,
                'profile' => '',
            ],
            'heating_secondaryCircuit_sensors_temperature_supply_value' => [
                'value'   => 24.8,
                'profile' => '~Temperature',
            ],
            'heating_circuits_0_operating_modes_forcedNormal_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_circuits_0_operating_programs_screedDrying_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_circuits_0_operating_programs_screedDrying_profile' => [
                'value'   => 'none',
                'profile' => '',
            ],
            'heating_compressors_0_heat_production_current_value' => [
                'value'   => 0.0,
                'profile' => '~Watt.3680',
            ],
            'heating_circuits_0_operating_modes_forcedReduced_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_circuits_1_heating_curve_shift' => [
                'value'   => 0.0,
                'profile' => '',
            ],
            'heating_circuits_1_heating_curve_slope' => [
                'value'   => 0.6,
                'profile' => '',
            ],
            'heating_configuration_cooling_mode' => [
                'value'   => 'active',
                'profile' => '',
            ],
            'heating_circuits_0_operating_modes_normalStandby_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_operating_programs_holiday_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_circuits_0_temperature_levels_min' => [
                'value'   => 15.0,
                'profile' => '~Temperature',
            ],
            'heating_circuits_0_temperature_levels_max' => [
                'value'   => 45.0,
                'profile' => '~Temperature',
            ],
            'heating_dhw_pumps_circulation_schedule_active' => [
                'value'   => true,
                'profile' => '~Switch',
            ],
            'heating_circuits_0_operating_modes_cooling_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_dhw_oneTimeCharge_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_compressor_statistics_starts' => [
                'value'   => 54177072.0,
                'profile' => '',
            ],
            'heating_compressor_statistics_hours' => [
                'value'   => 15049.2,
                'profile' => '',
            ],
            'heating_dhw_temperature_value' => [
                'value'   => 45.0,
                'profile' => '',
            ],
            'heating_dhw_temperature_temp2_value' => [
                'value'   => 50.0,
                'profile' => '',
            ],
            'heating_dhw_sensors_temperature_hotWaterStorage_top_value' => [
                'value'   => 43.7,
                'profile' => '~Temperature',
            ],
            'heating_boiler_serial_value' => [
                'value'   => '7502078401718109',
                'profile' => '',
            ],
            'heating_circuits_0_temperature_value' => [
                'value'   => 26.0,
                'profile' => '',
            ],
            'heating_circuits_0_operating_programs_screedDrying_heatpump_useapproved' => [
                'value'   => false,
                'profile' => '',
            ],
            'heating_device_initialSetup_date' => [
                'value'   => '2014-04-02',
                'profile' => '',
            ],
            'heating_circuits_0_heating_schedule_active' => [
                'value'   => true,
                'profile' => '~Switch',
            ],
            'heating_compressor_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_coolingCircuits_0_type_value' => [
                'value'   => 'unknown',
                'profile' => '',
            ],
            'heating_circuits_0_operating_programs_eco_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_circuits_0_operating_programs_eco_temperature' => [
                'value'   => 19.0,
                'profile' => '~Temperature',
            ],
            'heating_sensors_temperature_outside_value' => [
                'value'   => 4.2,
                'profile' => '~Temperature',
            ],
            'heating_secondaryCircuit_sensors_temperature_return_value' => [
                'value'   => 24.8,
                'profile' => '~Temperature',
            ],
            'heating_coolingCircuits_0_eev_type_value' => [
                'value'   => 'Airwell',
                'profile' => '',
            ],
            'heating_circuits_0_operating_modes_dhw_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_compressors_0_statistics_starts' => [
                'value'   => 54177072.0,
                'profile' => '',
            ],
            'heating_compressors_0_statistics_hours' => [
                'value'   => 15049.2,
                'profile' => '',
            ],
            'heating_compressors_0_statistics_hoursloadclassone' => [
                'value'   => 1226.0,
                'profile' => '',
            ],
            'heating_compressors_0_statistics_hoursloadclasstwo' => [
                'value'   => 4279.0,
                'profile' => '',
            ],
            'heating_compressors_0_statistics_hoursloadclassthree' => [
                'value'   => 7628.0,
                'profile' => '',
            ],
            'heating_compressors_0_statistics_hoursloadclassfour' => [
                'value'   => 794.0,
                'profile' => '',
            ],
            'heating_compressors_0_statistics_hoursloadclassfive' => [
                'value'   => 383.0,
                'profile' => '',
            ],
            'heating_device_time_offset_value' => [
                'value'   => 120.0,
                'profile' => '',
            ],
            'heating_circuits_0_circulation_schedule_active' => [
                'value'   => true,
                'profile' => '~Switch',
            ],
            'heating_dhw_schedule_active' => [
                'value'   => true,
                'profile' => '~Switch',
            ],
            'heating_circuits_2_heating_curve_shift' => [
                'value'   => 0.0,
                'profile' => '',
            ],
            'heating_circuits_2_heating_curve_slope' => [
                'value'   => 0.6,
                'profile' => '',
            ],
            'heating_circuits_0_operating_programs_reduced_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_circuits_0_operating_programs_reduced_demand' => [
                'value'   => 'unknown',
                'profile' => '',
            ],
            'heating_circuits_0_operating_programs_reduced_temperature' => [
                'value'   => 18.0,
                'profile' => '~Temperature',
            ],
            'heating_circuits_0_operating_programs_fixed_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_compressors_0_power_value' => [
                'value'   => 10.0,
                'profile' => '~Power',
            ],
            'heating_dhw_active' => [
                'value'   => true,
                'profile' => '~Switch',
            ],
            'heating_configuration_multiFamilyHouse_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_circuits_0_operating_programs_active_value' => [
                'value'   => 'normal',
                'profile' => '',
            ],
            'heating_dhw_temperature_hysteresis_value' => [
                'value'   => 5.0,
                'profile' => '',
            ],
            'heating_dhw_charging_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_circuits_0_operating_modes_standby_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_heatingRod_status_overall' => [
                'value'   => false,
                'profile' => '',
            ],
            'heating_heatingRod_status_level1' => [
                'value'   => false,
                'profile' => '',
            ],
            'heating_heatingRod_status_level2' => [
                'value'   => false,
                'profile' => '',
            ],
            'heating_heatingRod_status_level3' => [
                'value'   => false,
                'profile' => '',
            ],
            'heating_circuits_0_operating_programs_standby_active' => [
                'value'   => false,
                'profile' => '~Switch',
            ],
            'heating_controller_serial_value' => [
                'value'   => '????????????????',
                'profile' => '',
            ],
            'heating_dhw_sensors_temperature_hotWaterStorage_value' => [
                'value'   => 43.7,
                'profile' => '~Temperature',
            ],
        ];

        $this->assertEquals(count($testValues), count($values));

        foreach ($testValues as $key => $value) {
            $this->assertTrue(isset($values[$key]));
            $this->assertEquals($values[$key], $value);
        }
    }
}
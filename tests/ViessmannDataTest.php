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
        if (!IPS\ProfileManager::variableProfileExists('~UnixTimestamp')) {
            IPS\ProfileManager::createVariableProfile('~UnixTimestamp', 1);
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

    public function testData4(): void
    {
        $iid = IPS_CreateInstance('{3BF2B1B8-BD31-4A06-8C70-FD0FF95FE22E}');
        $interface = IPS\InstanceManager::getInstanceInterface($iid);
        $interface->DebugParseDeviceData(json_decode(file_get_contents(__DIR__ . '/data/4.json')));

        $values = [];
        foreach (IPS_GetChildrenIDs($iid) as $id) {
            $values[IPS_GetObject($id)['ObjectIdent']] = GetValue($id);
        }

        //var_export($values);
        $testValues = [
            'heating_burners_0_active' => false,
            'heating_operating_programs_holidayAtHome_active' => false,
            'heating_operating_programs_holidayAtHome_start' => '',
            'heating_operating_programs_holidayAtHome_end' => '',
            'heating_dhw_hygiene_trigger_weekdays' => '["Sat"]',
            'heating_dhw_hygiene_trigger_starthour' => 22.0,
            'heating_dhw_hygiene_trigger_startminute' => 30.0,
            'heating_circuits_0_zone_mode_active' => false,
            'heating_circuits_3_operating_programs_summerEco_active' => false,
            'heating_circuits_1_heating_curve_shift' => 0.0,
            'heating_circuits_1_heating_curve_slope' => 1.4,
            'heating_circuits_0_operating_modes_dhwAndHeating_active' => true,
            'heating_dhw_temperature_main_value' => 55.0,
            'heating_dhw_oneTimeCharge_active' => false,
            'heating_circuits_0_operating_modes_heating_active' => false,
            'heating_circuits_1_operating_programs_summerEco_active' => false,
            'heating_circuits_2_zone_mode_active' => false,
            'heating_dhw_temperature_value' => 55.0,
            'heating_boiler_temperature_value' => 20.0,
            'heating_circuits_0_operating_programs_summerEco_active' => true,
            'heating_circuits_0_operating_programs_standby_active' => true,
            'heating_circuits_2_heating_curve_shift' => 0.0,
            'heating_circuits_2_heating_curve_slope' => 1.4,
            'heating_dhw_schedule_active' => true,
            'heating_circuits_0_operating_modes_active_value' => 'dhwAndHeating',
            'heating_circuits_0_operating_modes_dhw_active' => false,
            'heating_sensors_temperature_outside_value' => 18.1,
            'heating_circuits_0_operating_programs_normal_active' => false,
            'heating_circuits_0_operating_programs_normal_demand' => 'unknown',
            'heating_circuits_0_operating_programs_normal_temperature' => 12.0,
            'device_serial_value' => '7946798101641124',
            'heating_circuits_0_operating_programs_reduced_active' => false,
            'heating_circuits_0_operating_programs_reduced_demand' => 'unknown',
            'heating_circuits_0_operating_programs_reduced_temperature' => 12.0,
            'heating_operating_programs_holiday_active' => false,
            'heating_operating_programs_holiday_start' => '',
            'heating_operating_programs_holiday_end' => '',
            'heating_circuits_3_zone_mode_active' => false,
            'heating_circuits_1_zone_mode_active' => false,
            'heating_circuits_0_operating_programs_active_value' => 'summerEco',
            'heating_dhw_active' => true,
            'heating_boiler_serial_value' => '7946798101641124',
            'heating_burner_active' => false,
            'heating_circuits_0_operating_programs_comfort_active' => false,
            'heating_circuits_0_operating_programs_comfort_demand' => 'unknown',
            'heating_circuits_0_operating_programs_comfort_temperature' => 25.0,
            'heating_dhw_temperature_hygiene_value' => 70.0,
            'heating_circuits_0_operating_programs_forcedLastFromSchedule_active' => false,
            'heating_circuits_enabled' => '["0"]',
            'heating_dhw_sensors_temperature_hotWaterStorage_value' => 53.4,
            'heating_dhw_hygiene_enabled' => true,
            'heating_dhw_hygiene_active' => false,
            'heating_circuits_0_heating_curve_shift' => 0.0,
            'heating_circuits_0_heating_curve_slope' => 1.4,
            'heating_gas_consumption_heating_day' => '[0,0,0,0,0,0,0,0]',
            'heating_gas_consumption_heating_week' => '[0,0,0,0,0,0]',
            'heating_gas_consumption_heating_month' => '[0,0,1.8,59.3,40.7,0,0,0,0,0,0,0,0]',
            'heating_gas_consumption_heating_year' => '[101.8,0]',
            'heating_gas_consumption_heating_dayvaluereadat' => 1628551495,
            'heating_gas_consumption_heating_weekvaluereadat' => 1628551501,
            'heating_gas_consumption_heating_monthvaluereadat' => 1628551495,
            'heating_gas_consumption_heating_yearvaluereadat' => 1628551495,
            'heating_gas_consumption_dhw_day' => '[0,1.1,1.2,2.2,1.7,1.3,0.8,1.6]',
            'heating_gas_consumption_dhw_week' => '[1.1,9.700000000000001,9.4,8.200000000000001,7.7,7.1000000000000005]',
            'heating_gas_consumption_dhw_month' => '[12.6,36.5,38.6,55.7,13.9,0,0,0,0,0,0,0,0]',
            'heating_gas_consumption_dhw_year' => '[157.4,0]',
            'heating_gas_consumption_dhw_dayvaluereadat' => 1628551495,
            'heating_gas_consumption_dhw_weekvaluereadat' => 1628551501,
            'heating_gas_consumption_dhw_monthvaluereadat' => 1628551495,
            'heating_gas_consumption_dhw_yearvaluereadat' => 1628551495,
            'heating_boiler_sensors_temperature_commonSupply_value' => 32.2,
            'heating_power_consumption_day' => '[0,0.2,0.1,0.2,0.2,0.1,0.1,0.1]',
            'heating_power_consumption_week' => '[0.2,0.8999999999999999,0.7999999999999999,0.7,0.7999999999999999,0.7999999999999999]',
            'heating_power_consumption_month' => '[1.8,5.6,7.1000000000000005,21.900000000000002,8.5,0,0,0,0,0,0,0,0]',
            'heating_power_consumption_year' => '[45.4,0]',
            'heating_power_consumption_dayvaluereadat' => 1628569762,
            'heating_power_consumption_weekvaluereadat' => 1628551500,
            'heating_power_consumption_monthvaluereadat' => 1628569762,
            'heating_power_consumption_yearvaluereadat' => 1628569762,
            'heating_burners_0_modulation_value' => 0.0,
            'heating_configuration_multiFamilyHouse_active' => false,
            'heating_device_time_offset_value' => 120.0,
            'heating_circuits_0_active' => true,
            'heating_circuits_0_name' => '',
            'heating_circuits_0_type' => 'heatingCircuit',
            'heating_circuits_2_operating_programs_summerEco_active' => false,
            'heating_burners_0_statistics_hours' => 361.0,
            'heating_burners_0_statistics_starts' => 917.0,
            'heating_circuits_0_operating_modes_standby_active' => false,
            'heating_circuits_0_heating_schedule_active' => true,
            'heating_circuits_3_heating_curve_shift' => 0.0,
            'heating_circuits_3_heating_curve_slope' => 1.4,
        ];

        $this->assertEquals(count($testValues), count($values));

        foreach ($testValues as $key => $value) {
            $this->assertTrue(isset($values[$key]));
            $this->assertEquals($values[$key], $value);
        }
    }

    public function testData5(): void
    {
        $iid = IPS_CreateInstance('{3BF2B1B8-BD31-4A06-8C70-FD0FF95FE22E}');
        $interface = IPS\InstanceManager::getInstanceInterface($iid);
        $interface->DebugParseDeviceData(json_decode(file_get_contents(__DIR__ . '/data/5.json')));

        $values = [];
        foreach (IPS_GetChildrenIDs($iid) as $id) {
            $values[IPS_GetObject($id)['ObjectIdent']] = GetValue($id);
        }

        //var_export($values);
        $testValues = [
            'heating_dhw_active' => true,
            'heating_circuits_0_operating_programs_active_value' => 'standby',
            'heating_boiler_serial_value' => '7722310001608124',
            'heating_power_consumption_day' => '[0,0.2,0.2,0.2,0.2,0.2,0.2,0.2]',
            'heating_power_consumption_week' => '[0.6000000000000001,1.4,1.4,1.4,1.4,1.4]',
            'heating_power_consumption_month' => '[4,8.5,10.3,15,19.400000000000002,15.4,28.2,30.7,30.5,25.9,9.700000000000001,8.7,8.3]',
            'heating_power_consumption_year' => '[101.4,114.2]',
            'heating_power_consumption_dayvaluereadat' => 1626331364,
            'heating_power_consumption_weekvaluereadat' => 1626309629,
            'heating_power_consumption_monthvaluereadat' => 1626331364,
            'heating_power_consumption_yearvaluereadat' => 1626331364,
            'heating_boiler_temperature_value' => 20.0,
            'heating_circuits_0_operating_programs_normal_active' => false,
            'heating_circuits_0_operating_programs_normal_demand' => 'unknown',
            'heating_circuits_0_operating_programs_normal_temperature' => 20.0,
            'heating_circuits_0_operating_modes_active_value' => 'dhw',
            'heating_circuits_0_operating_programs_comfort_active' => false,
            'heating_circuits_0_operating_programs_comfort_demand' => 'unknown',
            'heating_circuits_0_operating_programs_comfort_temperature' => 20.0,
            'heating_circuits_0_temperature_value' => 0.0,
            'heating_dhw_temperature_main_value' => 50.0,
            'heating_dhw_temperature_value' => 50.0,
            'heating_dhw_oneTimeCharge_active' => false,
            'heating_circuits_0_operating_modes_heating_active' => false,
            'heating_burners_0_statistics_hours' => 1096.0,
            'heating_burners_0_statistics_starts' => 3252.0,
            'heating_operating_programs_holidayAtHome_active' => false,
            'heating_operating_programs_holidayAtHome_start' => '',
            'heating_operating_programs_holidayAtHome_end' => '',
            'heating_dhw_temperature_hygiene_value' => 65.0,
            'heating_gas_consumption_dhw_day' => '[0.1,0.8,0,1.1,0.7,0.7,0.7,0.7]',
            'heating_gas_consumption_dhw_week' => '[2,4.8,4.7,4.5,4.8,6.699999999999999]',
            'heating_gas_consumption_dhw_month' => '[9.5,22.8,34.9,37,34.4,43.5,36.5,31.8,23.9,19.6,23.2,22.6,23]',
            'heating_gas_consumption_dhw_year' => '[219,144.4]',
            'heating_gas_consumption_dhw_dayvaluereadat' => 1626330884,
            'heating_gas_consumption_dhw_weekvaluereadat' => 1626330884,
            'heating_gas_consumption_dhw_monthvaluereadat' => 1626330884,
            'heating_gas_consumption_dhw_yearvaluereadat' => 1626330884,
            'device_serial_value' => '7722310001608124',
            'heating_device_time_offset_value' => 65.0,
            'heating_operating_programs_holiday_active' => false,
            'heating_operating_programs_holiday_start' => '',
            'heating_operating_programs_holiday_end' => '',
            'heating_dhw_hygiene_trigger_weekdays' => '["Mon"]',
            'heating_dhw_hygiene_trigger_starthour' => 22.0,
            'heating_dhw_hygiene_trigger_startminute' => 30.0,
            'heating_dhw_sensors_temperature_hotWaterStorage_value' => 52.7,
            'heating_dhw_schedule_active' => true,
            'heating_circuits_0_heating_schedule_active' => false,
            'heating_circuits_enabled' => '["0"]',
            'heating_circuits_0_operating_modes_dhw_active' => true,
            'heating_circuits_0_sensors_temperature_supply_value' => 70.3,
            'heating_circuits_0_operating_programs_reduced_active' => false,
            'heating_circuits_0_operating_programs_reduced_demand' => 'unknown',
            'heating_circuits_0_operating_programs_reduced_temperature' => 20.0,
            'heating_circuits_0_operating_modes_standby_active' => false,
            'heating_circuits_0_operating_modes_dhwAndHeating_active' => false,
            'heating_burner_active' => false,
            'heating_boiler_sensors_temperature_commonSupply_value' => 70.1,
            'heating_circuits_0_operating_programs_forcedLastFromSchedule_active' => false,
            'heating_dhw_hygiene_enabled' => true,
            'heating_dhw_hygiene_active' => false,
            'heating_gas_consumption_heating_day' => '[0,0,0,0,0,0,0,0]',
            'heating_gas_consumption_heating_week' => '[0,0,0,0,0,0]',
            'heating_gas_consumption_heating_month' => '[0,0,8.3,40.3,60.2,44.6,52.1,42.6,74.5,49.5,5.9,0,0.5]',
            'heating_gas_consumption_heating_year' => '[205.6,173.1]',
            'heating_gas_consumption_heating_dayvaluereadat' => 1626309627,
            'heating_gas_consumption_heating_weekvaluereadat' => 1626309630,
            'heating_gas_consumption_heating_monthvaluereadat' => 1626309627,
            'heating_gas_consumption_heating_yearvaluereadat' => 1626309627,
            'heating_circuits_0_active' => true,
            'heating_circuits_0_name' => 'Heizkreis 1',
            'heating_circuits_0_type' => 'heatingCircuit',
            'heating_burners_0_modulation_value' => 0.0,
            'heating_circuits_0_operating_programs_standby_active' => true,
            'heating_configuration_multiFamilyHouse_active' => false,
        ];

        $this->assertEquals(count($testValues), count($values));

        foreach ($testValues as $key => $value) {
            $this->assertTrue(isset($values[$key]));
            $this->assertEquals($values[$key], $value);
        }
    }

    public function testData6(): void
    {
        $iid = IPS_CreateInstance('{3BF2B1B8-BD31-4A06-8C70-FD0FF95FE22E}');
        $interface = IPS\InstanceManager::getInstanceInterface($iid);
        $interface->DebugParseDeviceData(json_decode(file_get_contents(__DIR__ . '/data/6.json')));

        $values = [];
        foreach (IPS_GetChildrenIDs($iid) as $id) {
            $values[IPS_GetObject($id)['ObjectIdent']] = GetValue($id);
        }

        //var_export($values);
        $testValues = [
            'heating_circuits_0_heating_curve_shift' => 0.0,
            'heating_circuits_0_heating_curve_slope' => 1.4,
            'heating_dhw_schedule_active' => true,
            'heating_circuits_1_operating_programs_standby_active' => true,
            'heating_burners_0_modulation_value' => 0.0,
            'heating_solar_active' => true,
            'heating_controller_serial_value' => '7535651013161129',
            'heating_circuits_enabled' => '["1"]',
            'heating_burners_0_statistics_hours' => 3278.5,
            'heating_burners_0_statistics_starts' => 6702.0,
            'heating_dhw_temperature_main_value' => 57.0,
            'heating_configuration_multiFamilyHouse_active' => false,
            'heating_solar_power_production_day' => '[0,28.512,4.183,73.754,25.252,62.916,27.986,0]',
            'heating_solar_power_production_week' => '[]',
            'heating_solar_power_production_month' => '[]',
            'heating_solar_power_production_year' => '[]',
            'heating_solar_power_production_dayvaluereadat' => 1626308145,
            'heating_solar_power_production_weekvaluereadat' => 0,
            'heating_solar_power_production_monthvaluereadat' => 0,
            'heating_solar_power_production_yearvaluereadat' => 0,
            'heating_circuits_1_operating_modes_dhw_active' => true,
            'heating_circuits_1_operating_programs_normal_active' => false,
            'heating_circuits_1_operating_programs_normal_demand' => 'unknown',
            'heating_circuits_1_operating_programs_normal_temperature' => 21.0,
            'heating_circuits_1_operating_programs_external_active' => false,
            'heating_circuits_1_operating_programs_external_temperature' => 0.0,
            'heating_boiler_serial_value' => '',
            'heating_burner_active' => false,
            'heating_circuits_1_operating_programs_reduced_active' => false,
            'heating_circuits_1_operating_programs_reduced_demand' => 'unknown',
            'heating_circuits_1_operating_programs_reduced_temperature' => 16.0,
            'heating_dhw_pumps_circulation_schedule_active' => true,
            'heating_circuits_1_operating_programs_active_value' => 'standby',
            'heating_boiler_sensors_temperature_main_value' => 52.0,
            'heating_dhw_temperature_value' => 57.0,
            'heating_dhw_sensors_temperature_hotWaterStorage_value' => 52.1,
            'heating_circuits_1_active' => true,
            'heating_circuits_1_name' => 'Heizkörper       ' . "\0" . '' . "\0" . '' . "\0" . '',
            'heating_circuits_1_type' => 'heatingCircuit',
            'heating_solar_sensors_temperature_dhw_value' => 34.9,
            'heating_circuits_1_operating_modes_dhwAndHeating_active' => false,
            'heating_circuits_1_operating_modes_active_value' => 'dhw',
            'heating_operating_programs_holiday_active' => false,
            'heating_operating_programs_holiday_start' => '',
            'heating_operating_programs_holiday_end' => '',
            'heating_solar_sensors_temperature_collector_value' => 23.1,
            'heating_circuits_2_heating_curve_shift' => 0.0,
            'heating_circuits_2_heating_curve_slope' => 1.4,
            'heating_sensors_temperature_outside_value' => 15.0,
            'heating_circuits_1_sensors_temperature_supply_value' => 38.5,
            'heating_circuits_1_heating_curve_shift' => 6.0,
            'heating_circuits_1_heating_curve_slope' => 1.2,
            'heating_dhw_active' => true,
            'heating_boiler_temperature_value' => 5.0,
            'heating_circuits_1_heating_schedule_active' => false,
            'heating_dhw_charging_active' => false,
            'heating_circuits_1_operating_modes_standby_active' => false,
            'heating_circuits_1_operating_programs_eco_active' => false,
            'heating_circuits_1_operating_programs_eco_temperature' => 21.0,
            'heating_device_time_offset_value' => 115.0,
            'heating_circuits_1_operating_programs_comfort_active' => false,
            'heating_circuits_1_operating_programs_comfort_demand' => 'unknown',
            'heating_circuits_1_operating_programs_comfort_temperature' => 20.0,
        ];

        $this->assertEquals(count($testValues), count($values));

        foreach ($testValues as $key => $value) {
            $this->assertTrue(isset($values[$key]));
            $this->assertEquals($values[$key], $value);
        }
    }
}

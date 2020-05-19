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

        parent::setUp();
    }

    public function testData1(): void
    {
        $iid = IPS_CreateInstance("{3BF2B1B8-BD31-4A06-8C70-FD0FF95FE22E}");
        $interface = IPS\InstanceManager::getInstanceInterface($iid);
        $interface->DebugParseDeviceData(json_decode(file_get_contents(__DIR__ . '/data/1.json')));

        foreach(IPS_GetChildrenIDs($iid) as $id) {
            $values[IPS_GetObject($id)['ObjectIdent']] = GetValue($id);
        }

        //var_export($values);
        $testValues = array (
            'heating_circuits_1_operating_programs_standby_active' => false,
            'heating_dhw_temperature_main_value' => 45.0,
            'heating_circuits_1_sensors_temperature_supply_value' => 50.5,
            'heating_configuration_multiFamilyHouse_active' => false,
            'heating_dhw_schedule_active' => true,
            'heating_circuits_1_operating_modes_active_value' => 'dhwAndHeating',
            'heating_controller_serial_value' => '0000000000000000',
            'heating_burner_statistics_hours' => 6148.7,
            'heating_burner_statistics_starts' => 54663.0,
            'heating_circuits_1_operating_programs_comfort_active' => false,
            'heating_circuits_1_operating_programs_comfort_temperature' => 20.0,
            'heating_circuits_1_operating_modes_dhwAndHeating_active' => true,
            'heating_circuits_1_heating_schedule_active' => true,
            'heating_circuits_1_operating_programs_eco_active' => false,
            'heating_circuits_1_operating_programs_eco_temperature' => 23.0,
            'heating_circuits_1_operating_programs_reduced_active' => false,
            'heating_circuits_1_operating_programs_reduced_temperature' => 18.0,
            'heating_dhw_charging_active' => false,
            'heating_burner_active' => true,
            'heating_circuits_1_operating_programs_normal_active' => true,
            'heating_circuits_1_operating_programs_normal_temperature' => 23.0,
            'heating_dhw_pumps_circulation_schedule_active' => true,
            'heating_boiler_serial_value' => '0000000000000000',
            'heating_solar_active' => true,
            'heating_device_time_offset_value' => 53.0,
            'heating_solar_sensors_temperature_dhw_value' => 35.1,
            'heating_boiler_sensors_temperature_main_value' => 58.0,
            'heating_boiler_sensors_temperature_commonSupply_value' => 53.6,
            'heating_burner_automatic_errorcode' => 0.0,
            'heating_circuits_1_operating_programs_external_active' => false,
            'heating_circuits_1_operating_programs_external_temperature' => 0.0,
            'heating_operating_programs_holiday_active' => false,
            'heating_boiler_temperature_value' => 49.4,
            'heating_circuits_1_operating_modes_standby_active' => false,
            'heating_circuits_1_operating_programs_holiday_active' => false,
            'heating_circuits_1_active' => true,
            'heating_circuits_1_name' => '',
            'heating_circuits_1_heating_curve_shift' => 5.0,
            'heating_circuits_1_heating_curve_slope' => 1.4,
            'heating_dhw_temperature_value' => 45.0,
            'heating_sensors_temperature_outside_value' => 13.8,
            'heating_circuits_1_operating_modes_forcedReduced_active' => false,
            'heating_solar_power_production_day' => 26.623,
            'heating_dhw_sensors_temperature_hotWaterStorage_value' => 59.2,
            'heating_burner_modulation_value' => 43.0,
            'heating_circuits_1_operating_modes_dhw_active' => false,
            'heating_circuits_1_operating_modes_forcedNormal_active' => false,
            'heating_dhw_active' => true,
            'heating_circuits_1_operating_programs_active_value' => 'normal',
            'heating_circuits_0_geofencing_active' => false,
            'heating_circuits_1_geofencing_active' => false,
            'heating_circuits_2_geofencing_active' => false,
        );

        $this->assertEquals(sizeof($testValues), sizeof($values));

        foreach($testValues as $key => $value) {
            $this->assertTrue(isset($values[$key]));
            $this->assertEquals($values[$key], $value);
        }

    }

    public function testData2(): void
    {
        $iid = IPS_CreateInstance("{3BF2B1B8-BD31-4A06-8C70-FD0FF95FE22E}");
        $interface = IPS\InstanceManager::getInstanceInterface($iid);
        $interface->DebugParseDeviceData(json_decode(file_get_contents(__DIR__ . '/data/2.json')));

        foreach(IPS_GetChildrenIDs($iid) as $id) {
            $values[IPS_GetObject($id)['ObjectIdent']] = GetValue($id);
        }

        //var_export($values);
        $testValues = array (
            'heating_boiler_serial_value' => '0000000000000000',
            'heating_device_time_offset_value' => 115.0,
            'heating_circuits_0_operating_programs_external_active' => false,
            'heating_circuits_0_operating_programs_external_temperature' => 0.0,
            'heating_circuits_0_operating_modes_dhw_active' => false,
            'heating_circuits_0_heating_curve_shift' => 0.0,
            'heating_circuits_0_heating_curve_slope' => 1.4,
            'heating_dhw_active' => true,
            'heating_operating_programs_holiday_active' => false,
            'heating_circuits_0_operating_modes_active_value' => 'dhwAndHeating',
            'heating_circuits_0_sensors_temperature_room_value' => 21.3,
            'heating_dhw_pumps_circulation_schedule_active' => true,
            'heating_circuits_0_operating_programs_reduced_active' => false,
            'heating_circuits_0_operating_programs_reduced_temperature' => 18.0,
            'heating_circuits_0_operating_programs_active_value' => 'normal',
            'heating_controller_serial_value' => '0000000000000000',
            'heating_circuits_0_operating_programs_comfort_active' => false,
            'heating_circuits_0_operating_programs_comfort_temperature' => 37.0,
            'heating_burner_modulation_value' => 0.0,
            'heating_circuits_0_sensors_temperature_supply_value' => 24.0,
            'heating_burner_statistics_hours' => 7498.4,
            'heating_burner_statistics_starts' => 27165.0,
            'heating_circuits_0_operating_programs_normal_active' => true,
            'heating_circuits_0_operating_programs_normal_temperature' => 21.0,
            'heating_circuits_0_operating_modes_dhwAndHeating_active' => true,
            'heating_circuits_0_operating_programs_standby_active' => false,
            'heating_circuits_0_operating_programs_eco_active' => false,
            'heating_circuits_0_operating_programs_eco_temperature' => 21.0,
            'heating_boiler_sensors_temperature_main_value' => 24.0,
            'heating_boiler_temperature_value' => 30.2,
            'heating_dhw_temperature_value' => 50.0,
            'heating_burner_active' => false,
            'heating_circuits_0_heating_schedule_active' => true,
            'heating_sensors_temperature_outside_value' => 15.6,
            'heating_dhw_sensors_temperature_outlet_value' => 37.3,
            'heating_circuits_0_operating_modes_forcedReduced_active' => false,
            'heating_circuits_0_operating_modes_standby_active' => false,
            'heating_configuration_multiFamilyHouse_active' => false,
            'heating_circuits_0_active' => true,
            'heating_circuits_0_name' => 'Heizkreis 1',
            'heating_circuits_0_operating_programs_holiday_active' => false,
            'heating_burner_automatic_errorcode' => 0.0,
            'heating_circuits_0_operating_modes_forcedNormal_active' => false,
            'heating_dhw_schedule_active' => true,
            'heating_dhw_sensors_temperature_hotWaterStorage_value' => 51.8,
            'heating_dhw_temperature_main_value' => 50.0,
            'heating_dhw_charging_active' => false,
            'heating_circuits_0_geofencing_active' => false,
            'heating_circuits_1_geofencing_active' => false,
            'heating_circuits_2_geofencing_active' => false,
        );

        $this->assertEquals(sizeof($testValues), sizeof($values));

        foreach($testValues as $key => $value) {
            $this->assertTrue(isset($values[$key]));
            $this->assertEquals($values[$key], $value);
        }

    }

}
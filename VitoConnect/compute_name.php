<?php

define("READABLE_IDS", [
    "heating.power.consumption" => "Energieverbrauch",
    "heating.gas.consumption.dhw" => "Gasverbrauch Warmwasserbereitung",
    "heating.gas.consumption.heating" => "Gasverbrauch Raumbeheizung",
    "heating.sensors.temperature.outside" => "Außentemperatur",
    "heating.dhw.temperature" => "Warmwasser Wunschtemperatur",
    "heating.dhw.temperature.main" => "Warmwasser Primäre Wunschtemperatur",
    "heating.dhw.sensors.temperature.hotWaterStorage" => "Warmwasser Temperatur",
    "heating.dhw.schedule" => "Warmwasser Zeitprogramm",
    "heating.dhw.pumps.circulation.schedule" => "Warmwasser Zirkulationspumpe Zeitprogramm",
    "heating.dhw.oneTimeCharge" => "Warmwasser Einmalaufladung",
    "heating.dhw.charging" => "Warmwasser Aufladung",
    "heating.configuration.multiFamilyHouse" => "Mehrfamilienhaus",
    "heating.circuits.%d.sensors.temperature.supply" => "Heizkreis %d: Kesseltemperatur",
    "heating.circuits.%d.operating.programs.reduced" => "Heizkreis %d: Reduziert",
    "heating.circuits.%d.operating.programs.normal" => "Heizkreis %d: Normal",
    "heating.circuits.%d.operating.programs.holiday" => "Heizkreis %d: Ferienprogramm",
    "heating.circuits.%d.operating.programs.active" => "Heizkreis %d: Aktiver Heizmodus",
    "heating.circuits.%d.operating.modes.forcedReduced" => "Heizkreis %d: Dauernd reduziert",
    "heating.circuits.%d.operating.modes.forcedNormal" => "Heizkreis %d: Dauernd Tagbetrieb",
    "heating.circuits.%d.operating.modes.dhwAndHeating" => "Heizkreis %d: Heizung & Warmwasser aktiv",
    "heating.circuits.%d.operating.modes.dhw" => "Heizkreis %d: Nur Warmwasser aktiv",
    "heating.circuits.%d.operating.modes.forcedReduced" => "Heizkreis %d: Dauernd reduziert",
    "heating.circuits.%d.operating.modes.active" => "Heizkreis %d: Betriebsart",
    "heating.circuits.%d" => "Heizkreis %d",
    "heating.burner.statistics" => "",
    "heating.burner.modulation" => "Modulation",
    "heating.burner" => "Brennerstatus",
    "heating.boiler.serial" => "Meine Anlage: Seriennummer",
    "heating.boiler.sensors.temperature.main" => "Kesseltemperatur"
]);

define("READABLE_NAMES", [
    "value" => "",
    "active" => "",
    "hours" => "Brennerbetriebsstunden",
    "starts" => "Brennerstarts",
    "name" => " " . "(Name)",
    "temperature" => " " . "(Temperatur)",
    "day" => " " . "(heute)",
    "week" => " " . "(diese Woche)",
    "month" => " " . "(diesen Monat)",
    "year" => " " . "(dieses Jahr)"
]);

// Generated names are currently German only, as we only know the names within the German app
function computeName($id, $name)
{
    $numbers = [];
    $splitID = split('.', '$id');

    for($i = 0; $i < count($splitID); $i++) {
        if (is_int($splitID[$i])) {
            $numbers[] = intval($splitID[$i]) + 1; // Add 1, so we start enumerating at 1, not 0
            $splitID[$i] = "%d";
        }
    }

    $placeholderID = join(".", $splitID);

    if (array_key_exists($placeholderID, READABLE_IDS) && array_key_exists($name, READABLE_NAMES)) {
        return sprintf(READABLE_IDS[$placeholderID], ...$numbers) . READABLE_NAMES[$name];
    }
    else {
        return "$id ($name)";
    }
}

<?php

define("READABLE_IDS", [
    "heating compressors.%d.statistics" => "Heizkreis %d: Verdichter Laufzeit",
    "heating.boiler.sensors.temperature.main" => "Kesseltemperatur",
    "heating.boiler.serial" => "Meine Anlage: Seriennummer",
    "heating.burner.modulation" => "Modulation",
    "heating.burner.statistics" => "Brenner",
    "heating.burner" => "Brennerstatus",
    "heating.circuits.%d.circulation.schedule.active" => "Zeitprogramm Zirkulation",
    "heating.circuits.%d.heating.curve" => "Heizkreis %d: Heizkennlinie",
    "heating.circuits.%d.heating.schedule" => "Heizkreis %d: Zeitprogramm Heizen",
    "heating.circuits.%d.operating.modes.active" => "Heizkreis %d: Betriebsart",
    "heating.circuits.%d.operating.modes.dhw" => "Heizkreis %d: Nur Warmwasser aktiv",
    "heating.circuits.%d.operating.modes.dhwAndHeating" => "Heizkreis %d: Heizung & Warmwasser aktiv",
    "heating.circuits.%d.operating.modes.forcedNormal" => "Heizkreis %d: Dauernd Tagbetrieb",
    "heating.circuits.%d.operating.modes.forcedReduced" => "Heizkreis %d: Dauernd reduziert",
    "heating.circuits.%d.operating.modes.forcedReduced" => "Heizkreis %d: Dauernd reduziert",
    "heating.circuits.%d.operating.programs.active" => "Heizkreis %d: Aktiver Heizmodus",
    "heating.circuits.%d.operating.programs.comfort" => "Heizkreis %d: Raumtemperatur",
    "heating.circuits.%d.operating.programs.eco" => "Heizkreis %d: Reduzierte Raumtemperatur",
    "heating.circuits.%d.operating.programs.holiday" => "Heizkreis %d: Ferienprogramm",
    "heating.circuits.%d.operating.programs.normal" => "Heizkreis %d: Normal",
    "heating.circuits.%d.operating.programs.reduced" => "Heizkreis %d: Reduziert",
    "heating.circuits.%d.operating.programs.standby" => "Heizkreis %d: Heizung Standby (AUS)",
    "heating.circuits.%d.sensors.temperature.supply" => "Heizkreis %d: Kesseltemperatur",
    "heating.circuits.%d" => "Heizkreis %d",
    "heating.compressor.%d.statistics" => "Heizkreis %d: Verdichter",
    "heating.compressor.%d" => "Heizkreis %d: Betrieb Verdichter",
    "heating.compressor.statistics" => "Verdichter",
    "heating.compressor" => "Betrieb Verdichter",
    "heating.configuration.multiFamilyHouse" => "Mehrfamilienhaus",
    "heating.dhw.charging" => "Warmwasser Aufladung",
    "heating.dhw.oneTimeCharge" => "Warmwasser Einmalaufladung",
    "heating.dhw.pumps.circulation.schedule" => "Warmwasser Zirkulationspumpe Zeitprogramm",
    "heating.dhw.schedule" => "Warmwasser Zeitprogramm",
    "heating.dhw.sensors.temperature.hotWaterStorage.top" => "Warmwassertemperatur",
    "heating.dhw.sensors.temperature.hotWaterStorage" => "Warmwasser Temperatur",
    "heating.dhw.temperature.hysteresis" => "Warmwassertemperatur Hysterese",
    "heating.dhw.temperature.main" => "Warmwasser Primäre Wunschtemperatur",
    "heating.dhw.temperature.temp2" => "Warmwassertemperatur 2 Soll",
    "heating.dhw.temperature" => "Warmwasser Wunschtemperatur",
    "heating.dhw" => "Warmwasser",
    "heating.gas.consumption.dhw" => "Gasverbrauch Warmwasserbereitung",
    "heating.gas.consumption.heating" => "Gasverbrauch Raumbeheizung",
    "heating.power.consumption" => "Energieverbrauch",
    "heating.sensors.temperature.outside" => "Außentemperatur",
    "heating.sensors.temperature.return" => "Rücklauftemperatur Sekundärkreis",
    "ventilation.operating.modes.active" => "Lüftung Betriebsart",
    "ventilation.operating.modes.standard" => "Lüftung Grundbetrieb",
    "ventilation.operating.modes.standby" => "Lüftung Standby (AUS)",
    "ventilation.operating.modes.ventilation" => "Lüftungsautomatik",
    "ventilation.operating.programs.active" => "Lüftung Betriebsart",
    "ventilation.operating.programs.basic" => "Lüftung MIN Betrieb",
    "ventilation.operating.programs.intensive" => "Lüftung intensiever Betrieb",
    "ventilation.operating.programs.reduced" => "Lüftung reduzierter Betrieb",
    "ventilation.operating.programs.standard" => "Lüftung normaler Betrieb",
    "ventilation.operating.programs.standby" => "Lüftung Standby (AUS)",
    "ventilation.schedule" => "Lüftung Zeitprogramm",
    "ventilation" => "Betrieb Lüftung"

]);

define("READABLE_NAMES", [
    "value" => "",
    "active" => "",
    "hours" => " " . "(Betriebsstunden)",
    "starts" => " " . "(Starts)",
    "name" => " " . "(Name)",
    "temperature" => " " . "(Soll)",
    "day" => " " . "(heute)",
    "week" => " " . "(diese Woche)",
    "month" => " " . "(diesen Monat)",
    "year" => " " . "(dieses Jahr)",
    "shift" => " " . "(Niveau)",
    "slope" => " " . "(Neigung)",
    "hoursLoadClassFive" => " " . "Belastungsklasse 5",
    "hoursLoadClassFour" => " " . "Belastungsklasse 4",
    "hoursLoadClassThree" => " " . "Belastungsklasse 3",
    "hoursLoadClassTwo" => " " . "Belastungsklasse 2",
    "hoursLoadClassOne" => " " . "Belastungsklasse 1",
]);

// Generated names are currently German only, as we only know the names within the German app
function computeName($id, $name)
{
    $numbers = [];
    $splitID = explode('.', $id);

    for($i = 0; $i < count($splitID); $i++) {
        if (is_numeric($splitID[$i])) {
            $numbers[] = intval($splitID[$i]) + 1; // Add 1, so we start enumerating at 1, not 0
            $splitID[$i] = "%d";
        }
    }

    $placeholderID = implode(".", $splitID);

    if (array_key_exists($placeholderID, READABLE_IDS) && array_key_exists($name, READABLE_NAMES)) {
        return sprintf(READABLE_IDS[$placeholderID], ...$numbers) . READABLE_NAMES[$name];
    }
    else {
        return "$id ($name)";
    }
}

<?php

declare(strict_types=1);

include_once __DIR__ . '/compute_name.php';

if (defined('PHPUNIT_TESTSUITE')) {
    trait Simulate
    {
        public function DebugParseDeviceData(object $device): void
        {
            $this->failOnUnexpected = true;
            $this->ParseDeviceData($device);
        }

        public function DebugRequestAction(string $Ident, mixed $Value): array
        {
            $this->recordedActions = [];
            $this->RequestAction($Ident, $Value);
            $actions = $this->recordedActions;
            $this->recordedActions = null;
            return $actions;
        }

        // Required by the stubs for our timers
        protected function getTime(): int
        {
            return time();
        }
    }
} else {
    trait Simulate
    {
    }
}

class VitoConnect extends IPSModuleStrict
{
    use Simulate;

    // If set, variables will be renamed to our computed names while parsing the device data
    private $renameVariables = false;

    // Used by our tests: throw on unexpected data and record actions instead of sending them
    private $failOnUnexpected = false;
    private $recordedActions = null;

    private $authorize_url = 'https://iam.viessmann-climatesolutions.com/idp/v3/authorize';
    private $token_url = 'https://iam.viessmann-climatesolutions.com/idp/v3/token';

    private $user_url = 'https://api.viessmann-climatesolutions.com/users/v1/users/me';
    private $installation_data_url = 'https://api.viessmann-climatesolutions.com/iot/v2/equipment/installations?includeGateways=true';
    private $feature_data_url = 'https://api.viessmann-climatesolutions.com/iot/v2/features/installations/%s/gateways/%s/devices/%d/features/';

    public function Create(): void
    {
        //Never delete this line!
        parent::Create();

        $this->RegisterPropertyString('ClientID', '');

        $this->RegisterPropertyInteger('Interval', 15);

        $this->RegisterAttributeString('Token', '');

        $this->RegisterAttributeInteger('InstallationID', 0);
        $this->RegisterAttributeString('GatewaySerial', '');
        $this->RegisterAttributeString('DeviceID', "0");

        // Maps variable idents to commands, which are set up while parsing the device data
        $this->RegisterAttributeString('Commands', '{}');

        $this->RegisterTimer('Update', 0, 'VVC_Update($_IPS[\'TARGET\']);');

        // This is our redirect URI for the OAuth process
        $this->RegisterHook('viessmann/' . $this->InstanceID);
    }

    public function ApplyChanges(): void
    {

        //Never delete this line!
        parent::ApplyChanges();

        //Set Timer only if valid credentials are available
        if ($this->ReadAttributeString('Token')) {
            $this->SetTimerInterval('Update', $this->ReadPropertyInteger('Interval') * 60 * 1000);
        } else {
            $this->SetTimerInterval('Update', 0);
        }
    }

    public function Register(): string
    {
        $base64url_encode = function ($data)
        {
            return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
        };

        $random = random_bytes(64);
        $verifier = $base64url_encode($random);
        $code_challenge = $base64url_encode(hash('sha256', $verifier, true));

        // Remember the verifier for later use
        $this->SetBuffer('Verifier', $verifier);

        return $this->authorize_url . '?client_id=' . $this->ReadPropertyString('ClientID') . '&redirect_uri=' . $this->GetCallbackURL() . '&response_type=code&code_challenge_method=S256&code_challenge=' . $code_challenge . '&scope=IoT%20User%20offline_access';
    }

    public function GetConfigurationForm(): string
    {
        $data = json_decode(file_get_contents(__DIR__ . '/form.json'));

        $data->elements[1]->value = $this->GetCallbackURL();

        if ($this->ReadAttributeInteger('InstallationID') > 0) {
            $data->elements[7]->visible = true;
            $data->elements[7]->caption = sprintf($this->Translate($data->elements[7]->caption), $this->ReadAttributeInteger('InstallationID'));
        }
        if (strlen($this->ReadAttributeString('GatewaySerial')) > 0) {
            $data->elements[8]->visible = true;
            $data->elements[8]->caption = sprintf($this->Translate($data->elements[8]->caption), $this->ReadAttributeString('GatewaySerial'));
        }
        if ($data->elements[7]->visible || $data->elements[8]->visible) {
            $data->elements[6]->visible = true;
        }

        $data->actions[0]->enabled = strlen($this->ReadPropertyString('ClientID')) > 0;
        $data->actions[1]->enabled = strlen($this->ReadAttributeString('Token')) > 0;
        $data->actions[2]->enabled = strlen($this->ReadAttributeString('Token')) > 0;
        $data->actions[3]->enabled = strlen($this->ReadAttributeString('Token')) > 0;


        return json_encode($data);
    }

    public function Update(): void
    {
        $this->ParseDeviceData($this->RequestDeviceData());
    }

    public function RenameVariables(): string
    {
        $this->renameVariables = true;
        $this->ParseDeviceData($this->RequestDeviceData());
        $this->renameVariables = false;

        return $this->Translate('Variables have been renamed.');
    }

    public function Dump(): void
    {
        echo 'User: ' . $this->FetchData($this->user_url)->loginId . PHP_EOL;
        $installations = $this->FetchData($this->installation_data_url);
        foreach ($installations->data as $installation) {
            echo PHP_EOL;
            echo 'Installation Description: ' . $installation->description . PHP_EOL;
            echo 'Installation ID: ' . $installation->id . PHP_EOL;
            foreach($installation->gateways as $gateway) {
                echo ' - Gateway Type: ' . $gateway->gatewayType . PHP_EOL;
                echo ' - Gateway Serial: ' . $gateway->serial . PHP_EOL;
                foreach ($gateway->devices as $device) {
                    echo '   - Device: ' . $device->modelId . " (" . $device->id . ")" . PHP_EOL;
                }
            }
        }
    }

    public function UIShowChangeDevice(): void
    {
        $installations = $this->FetchData($this->installation_data_url);
        $values = [];
        foreach ($installations->data as $installation) {
            foreach($installation->gateways as $gateway) {
                $values[] = [
                    "caption" => $installation->description. " (" . $gateway->gatewayType . ")",
                    "value" => $installation->id . "," . $gateway->serial,
                ];
            }
        }
        $this->UpdateFormField("Device", "options", json_encode($values));
        $this->UpdateFormField("Device", "value", $this->ReadAttributeInteger('InstallationID') . "," . $this->ReadAttributeString('GatewaySerial'));
        $this->UpdateFormField("Loading", "visible", false);
        $this->UpdateFormField("Device", "visible", true);
        $this->UpdateFormField("Hint", "visible", true);
    }

    public function UIChangeDevice(string $Device): void
    {
        $split = explode(",", $Device);
        $this->WriteAttributeInteger('InstallationID', intval($split[0]));
        $this->WriteAttributeString('GatewaySerial', strval($split[1]));
        $this->ReloadForm();
    }

    public function RequestAction(string $Ident, mixed $Value): void
    {
        $commands = json_decode($this->ReadAttributeString('Commands'), true);
        $isKnown = function ($commands) use ($Ident)
        {
            return isset($commands[$Ident]['name']) || isset($commands[$Ident]['on']);
        };

        // Commands are collected while parsing the device data. Refresh them, if we do not know this ident yet
        if (!$isKnown($commands) && !is_array($this->recordedActions)) {
            $this->Update();
            $commands = json_decode($this->ReadAttributeString('Commands'), true);
        }

        if (!$isKnown($commands)) {
            throw new Exception('Invalid Ident');
        }

        $command = $commands[$Ident];

        // Booleans may be switched by two separate commands, e.g. activate/deactivate
        if (isset($command['on'])) {
            $command = $Value ? $command['on'] : $command['off'];
        }

        // Parameters are either our new value or have to be filled with the current value of another property
        $params = [];
        foreach ($command['params'] as $param => $property) {
            if ($property === null) {
                $params[$param] = $Value;
            } else {
                $params[$param] = $this->GetValue(str_replace('.', '_', $command['feature']) . '_' . strtolower($property));
            }
        }

        $this->SendDeviceAction($command['feature'] . '/commands/' . $command['name'], $params);
        $this->SetValue($Ident, $Value);
    }

    protected function ProcessHookData(): void
    {
        $this->SendDebug('GET', print_r($_GET, true), 0);
        $this->SendDebug('POST', file_get_contents('php://input'), 0);

        if (!isset($_GET['code'])) {
            die(isset($_GET['error_description']) ? $_GET['error_description'] : (isset($_GET['error']) ? $_GET['error'] : 'Authorization code is missing'));
        }

        $this->SendDebug('ExchangeCodeToRefreshToken', '', 0);

        $options = [
            'http' => [
                'header'  => "Content-Type: application/x-www-form-urlencoded;charset=utf-8\r\n",
                'method'  => 'POST',
                'content' => http_build_query([
                    'client_id'     => $this->ReadPropertyString('ClientID'),
                    'redirect_uri'  => $this->GetCallbackURL(),
                    'grant_type'    => 'authorization_code',
                    'code_verifier' => $this->GetBuffer('Verifier'),
                    'code'          => $_GET['code'],
                ]),
                'ignore_errors' => true
            ]
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($this->token_url, false, $context);

        $this->SendDebug('RESULT', $result, 0);

        $data = json_decode($result);

        if ($data === null) {
            die('Invalid response while fetching access token!');
        }

        if (isset($data->error)) {
            die($data->error);
        }

        if (!isset($data->token_type) || $data->token_type != 'Bearer') {
            die('Bearer Token expected');
        }

        $this->SendDebug('GotRefreshToken', $result, 0);

        $this->WriteAttributeString('Token', $data->refresh_token);
        $this->SetBuffer('Token', $data->access_token);
        $this->SetBuffer('Expires', strval(time() + $data->expires_in - 60));

        $this->Initialize();

        $this->SetTimerInterval('Update', $this->ReadPropertyInteger('Interval') * 60 * 1000);

        $this->UpdateFormField('Update', 'enabled', true);
        $this->UpdateFormField('Dump', 'enabled', true);
        $this->UpdateFormField('RenameVariables', 'enabled', true);

        echo $this->Translate("Successful. You can now close this window and press 'Update' inside the instance.");
    }

    private function GetCallbackURL(): string
    {
        $cc_ids = IPS_GetInstanceListByModuleID('{9486D575-BE8C-4ED8-B5B5-20930E26DE6F}');
        $cc_url = (count($cc_ids) > 0) ? @CC_GetConnectURL($cc_ids[0]) : false;

        if ($cc_url) {
            return $cc_url . '/hook/viessmann/' . $this->InstanceID;
        }

        return $this->Translate('Symcon Connect must be enabled!');
    }

    private function Initialize(): void
    {
        //Fetch Installation ID and Gateway Serial for later reuse.
        $installation = $this->FetchData($this->installation_data_url);
        $this->SendDebug('InstallationID', strval($installation->data[0]->id), 0);
        $this->SendDebug('GatewaySerial', strval($installation->data[0]->gateways[0]->serial), 0);

        $this->WriteAttributeInteger('InstallationID', intval($installation->data[0]->id));
        $this->WriteAttributeString('GatewaySerial', strval($installation->data[0]->gateways[0]->serial));
    }

    private function UpdateAccessToken(): string
    {

        //Request a new Access Token if required
        $accessToken = $this->GetBuffer('Token');
        if ($accessToken == '' || time() >= intval($this->GetBuffer('Expires'))) {
            $this->SendDebug('UpdateAccessToken', '', 0);

            $options = [
                'http' => [
                    'header'  => "Content-Type: application/x-www-form-urlencoded;charset=utf-8\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query([
                        'client_id'     => $this->ReadPropertyString('ClientID'),
                        'grant_type'    => 'refresh_token',
                        'refresh_token' => $this->ReadAttributeString('Token')
                    ]),
                    'ignore_errors' => true
                ]
            ];
            $context = stream_context_create($options);
            $result = file_get_contents($this->token_url, false, $context);

            $this->SendDebug('RESULT', $result, 0);

            $data = json_decode($result);

            if ($data === null) {
                die('Invalid response while fetching access token!');
            }

            if (isset($data->error)) {
                die($data->error);
            }

            if (!isset($data->token_type) || $data->token_type != 'Bearer') {
                die('Bearer Token expected');
            }

            $this->WriteAttributeString('Token', $data->refresh_token);
            $this->SetBuffer('Token', $data->access_token);
            $this->SetBuffer('Expires', strval(time() + $data->expires_in - 60));

            $accessToken = $data->access_token;
        }

        return $accessToken;
    }

    private function FetchData(string $url): object
    {
        $accessToken = $this->UpdateAccessToken();

        //FetchData with Access Token
        $this->SendDebug('FetchData', $url, 0);

        $options = [
            'http' => [
                'header' => 'Authorization: Bearer ' . $accessToken . "\r\n",
                'ignore_errors' => true,
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result === false) {
            die('Fetching data failed!');
        }

        $this->SendDebug('GotData', $result, 0);

        $data = json_decode($result);

        if ($data === null) {
            die('Invalid response while fetching data!');
        }

        if (isset($data->errorType)) {
            die($data->message);
        }

        return $data;
    }

    private function SendAction(string $url, ?array $post_data = null): void
    {
        $accessToken = $this->UpdateAccessToken();

        //SendAction with Access Token
        $this->SendDebug('SendAction', $url, 0);

        $options = [
            'http' => [
                'method'  => 'POST',
                'header'  => 'Authorization: Bearer ' . $accessToken . "\r\nContent-Type: application/json\r\nAccept: application/vnd.siren+json\r\n",
                'content' => ($post_data == null) ? '{}' : json_encode($post_data),
                'ignore_errors' => true
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result === false) {
            die('Sending action failed!');
        }

        $data = json_decode($result);
        if (isset($data->errorType)) {
            $this->SendDebug('Error', $result, 0);
            die(isset($data->message) ? $data->message : $data->errorType);
        }

        $this->SendDebug('Success', $result, 0);
    }

    private function RequestDeviceData(): object
    {
        return $this->FetchData($this->GetFeatureURL());
    }

    private function SendDeviceAction(string $action, array $post_data): void
    {
        // Our tests record actions instead of sending them
        if (is_array($this->recordedActions)) {
            $this->recordedActions[] = ['action' => $action, 'params' => $post_data];
            return;
        }

        $this->SendAction($this->GetFeatureURL() . $action, $post_data);
    }

    private function GetFeatureURL(): string
    {
        $i_id = $this->ReadAttributeInteger('InstallationID');
        $g_serial = $this->ReadAttributeString('GatewaySerial');
        $d_id = $this->ReadAttributeString('DeviceID');

        if ($i_id == 0 || $g_serial == '') {
            die('InstallationID or GatewaySerial are missing');
        }

        return sprintf($this->feature_data_url, $i_id, $g_serial, $d_id);
    }

    private function ParseDeviceData(object $device): void
    {
        $updateVariable = function ($id, $name, $type, $value, $profile)
        {
            $ident = str_replace('.', '_', $id) . '_' . strtolower($name);
            switch ($type) {
                case 'boolean':
                    $this->RegisterVariableBoolean($ident, computeName($id, $name), $profile);
                    $this->SetValue($ident, $value);
                    break;
                case 'number':
                    $this->RegisterVariableFloat($ident, computeName($id, $name), $profile);
                    $this->SetValue($ident, $value);
                    break;
                case 'string':
                    $this->RegisterVariableString($ident, computeName($id, $name), $profile);
                    $this->SetValue($ident, $value);
                    break;
                case 'array':
                case 'object':
                    $this->RegisterVariableString($ident, computeName($id, $name));
                    $this->SetValue($ident, json_encode($value));
                    break;
                case 'Schedule':
                    // Lets skip this
                    break;
                case '_Time':
                    // This is not a real type. We defined it to enforce a real integer variable
                    $this->RegisterVariableInteger($ident, computeName($id, $name), $profile);
                    $this->SetValue($ident, $value);
                    break;
                default:
                    // Unknown structured types (e.g. VinEntry, MaterialIdEntry) are stored as JSON
                    if (is_object($value) || is_array($value)) {
                        $this->RegisterVariableString($ident, computeName($id, $name));
                        $this->SetValue($ident, json_encode($value));
                    } elseif ($this->failOnUnexpected) {
                        throw new Exception(sprintf('Unsupported variable type: %s, id: %s', $type, $id));
                    } else {
                        $this->SendDebug('Unsupported Type', $type . ', id: ' . $id . ', value: ' . print_r($value, true), 0);
                    }
                    break;
            }

            // Overwrite names which might have been changed by the user or by an older version of this module
            if ($this->renameVariables && @$this->GetIDForIdent($ident)) {
                IPS_SetName($this->GetIDForIdent($ident), computeName($id, $name));
            }
        };

        $findCommand = function ($commands, $name)
        {
            foreach ($commands as $command) {
                if ($command->name == $name) {
                    return $command;
                }
            }
            return false;
        };

        // Commands which can be used to change a property. The first available command will be used
        // A pair of commands is used to switch a boolean on/off
        $propertyCommands = [
            'active'         => [['activate', 'deactivate'], 'setActive'],
            'enabled'        => ['setEnabled'],
            'value'          => ['setMode', 'setHysteresis', 'setTargetTemperature'],
            'temperature'    => ['setTemperature'],
            'switchOnValue'  => ['setHysteresisSwitchOnValue'],
            'switchOffValue' => ['setHysteresisSwitchOffValue'],
            'min'            => ['setMin'],
            'max'            => ['setMax'],
        ];

        // All commands of the parsed device data by variable ident, which will be used in RequestAction
        $identCommands = [];

        // Map all parameters of a command to our new value (null) or to another property of the feature
        $buildCommand = function ($feature, $properties, $command, $name, $hasValue)
        {
            $params = [];
            foreach ($command->params as $param => $definition) {
                if ($hasValue && (($param == $name) || (count((array) $command->params) == 1))) {
                    $params[$param] = null;
                } elseif (isset($definition->required) && $definition->required) {
                    // We can only fill required parameters, for which we have a variable
                    if (!isset($properties->$param)) {
                        return false;
                    }
                    $params[$param] = $param;
                }
            }
            // We need to be able to pass our new value
            if ($hasValue && !in_array(null, $params, true)) {
                return false;
            }
            return ['feature' => $feature, 'name' => $command->name, 'params' => $params];
        };

        $updateAction = function ($feature, $name, $properties, $commands) use ($findCommand, $buildCommand, $propertyCommands, &$identCommands)
        {
            $ident = str_replace('.', '_', $feature) . '_' . strtolower($name);

            $identCommand = false;
            if (isset($propertyCommands[$name])) {
                foreach ($propertyCommands[$name] as $candidate) {
                    if (is_array($candidate)) {
                        $on = $findCommand($commands, $candidate[0]);
                        $off = $findCommand($commands, $candidate[1]);
                        if ($on && $off) {
                            $on = $buildCommand($feature, $properties, $on, $name, false);
                            $off = $buildCommand($feature, $properties, $off, $name, false);
                            if ($on && $off) {
                                $identCommand = ['on' => $on, 'off' => $off];
                            }
                        }
                    } elseif ($command = $findCommand($commands, $candidate)) {
                        $identCommand = $buildCommand($feature, $properties, $command, $name, true);
                    }
                    if ($identCommand) {
                        break;
                    }
                }
            }

            if ($identCommand) {
                $identCommands[$ident] = $identCommand;
                $this->EnableAction($ident);
            } elseif (($id = @$this->GetIDForIdent($ident)) && (IPS_GetVariable($id)['VariableAction'] == $this->InstanceID)) {
                // The device does not offer a command anymore
                $this->DisableAction($ident);
            }
        };

        //Parse data
        foreach ($device->data as $entity) {
            foreach ($entity->properties as $name => $property) {
                //Convert unit to our profiles
                $unitToProfile = function ($unit)
                {
                    switch ($unit) {
                        case '':
                            return '';
                        case 'bar':
                            return $this->CreateNumberProfile('VVC.Pressure', ' bar', 1, 'Gauge');
                        case 'cubicMeter':
                            return 'Gas';
                        case 'celsius':
                            return 'Temperature.Room';
                        case 'kilowattHour':
                            return 'Electricity';
                        case 'watt':
                            return 'Watt.3680';
                        case 'kilowatt':
                            return 'Power';
                        case 'percent':
                            return 'Valve.F';
                        case 'seconds':
                            return $this->CreateNumberProfile('VVC.Seconds', ' s', 0, 'Clock');
                        case 'meter':
                            return $this->CreateNumberProfile('VVC.Meter', ' m', 0, 'Distance');
                        case 'hour':
                            return $this->CreateNumberProfile('VVC.Hours', ' h', 0, 'Clock');
                        case 'degree':
                            return $this->CreateNumberProfile('VVC.Degree', '°', 1, 'Compass');
                        case 'liter':
                            return $this->CreateNumberProfile('VVC.Liter', ' l', 0, 'Drops');
                        case 'kilowattHour/year':
                            return $this->CreateNumberProfile('VVC.EnergyPerYear', ' kWh/a', 0, 'Electricity');
                        case 'kelvin':
                            return $this->CreateNumberProfile('VVC.Kelvin', ' K', 1, 'Temperature');
                        case 'minute':
                            return $this->CreateNumberProfile('VVC.Minutes', ' min', 0, 'Clock');
                        case 'ampere':
                            return $this->CreateNumberProfile('VVC.Ampere', ' A', 1, 'Electricity');
                        case 'revolutionsPerSecond':
                            return $this->CreateNumberProfile('VVC.RevolutionsPerSecond', ' U/s', 1, 'Ventilation');
                        case 'liter/hour':
                            return $this->CreateNumberProfile('VVC.VolumetricFlow', ' l/h', 0, 'Drops');
                        default:
                            if ($this->failOnUnexpected) {
                                throw new Exception(sprintf('Unknown unit: %s', $unit));
                            } else {
                                $this->SendDebug('Unknown Unit', $unit, 0);
                            }
                            return '';
                    }
                };

                //Convert name to our profiles
                $nameToProfile = function ($name, $commands) use ($findCommand)
                {
                    switch ($name) {
                        case 'active':
                            return 'Switch';
                        case 'enabled':
                            return $findCommand($commands, 'setEnabled') ? 'Switch' : '';
                        case 'value':
                            $command = $findCommand($commands, 'setMode');
                            if ($command) {
                                // Every feature has its own set of modes. Therefore, we need a profile for every set
                                $enum = $command->params->mode->constraints->enum;
                                return $this->CreateProfile('VVC.Mode.' . substr(md5(json_encode($enum)), 0, 8), VARIABLETYPE_STRING, $enum);
                            } elseif ($findCommand($commands, 'setHysteresis')) {
                                return 'Temperature';
                            } elseif ($findCommand($commands, 'setTargetTemperature')) {
                                return 'Temperature';
                            }
                            return '';
                        case 'temperature':
                            return 'Temperature.Room';
                        case 'switchOnValue':
                            return $findCommand($commands, 'setHysteresisSwitchOnValue') ? 'Temperature' : '';
                        case 'switchOffValue':
                            return $findCommand($commands, 'setHysteresisSwitchOffValue') ? 'Temperature' : '';
                        default:
                            return '';
                    }
                };

                // If unit is not defined on this level, search if have a global defined unit
                // For now we only need to fix array units. Therefore limit this to array.
                // Maybe we should better read the docs on how to handle this global unit field
                if (!isset($property->unit) && ($property->type == 'array')) {
                    foreach ($entity->properties as $n => $p) {
                        if ($n == 'unit') {
                            $property->unit = $p->value;
                        }
                    }
                }

                switch ($name) {
                    //We want to skip a few fields
                    case 'unit':
                    case 'minUnit':
                    case 'maxUnit':
                        break;
                    case 'dayValueReadAt':
                    case 'weekValueReadAt':
                    case 'monthValueReadAt':
                    case 'yearValueReadAt':
                        $updateVariable($entity->feature, $name, '_Time', $property->value ? strtotime($property->value) : 0, 'UnixTimestamp');
                        break;
                    default:
                        // Deduct profile
                        $profile = '';
                        if (isset($property->unit)) {
                            $profile = $unitToProfile($property->unit);
                        }
                        if (!$profile) {
                            $profile = $nameToProfile($name, $entity->commands);
                        }

                        // Fix up a few array values, which we want to reduce to a single value
                        if ($property->type == 'array') {
                            switch ($profile) {
                                case 'Electricity':
                                case 'Gas':
                                    $property->type = 'number';
                                    if (count($property->value) == 0) {
                                        $property->value = 0;
                                    } else {
                                        $property->value = $property->value[0];
                                    }
                                    break;
                            }
                        }

                        // Create and updates variables
                        $updateVariable($entity->feature, $name, $property->type, $property->value, $profile);
                        $updateAction($entity->feature, $name, $entity->properties, $entity->commands);
                        break;
                }
            }
        }

        $identCommands = json_encode($identCommands);
        if ($this->ReadAttributeString('Commands') != $identCommands) {
            $this->WriteAttributeString('Commands', $identCommands);
        }
    }

    private function CreateNumberProfile(string $name, string $suffix, int $digits, string $icon): string
    {
        if (!IPS_VariableProfileExists($name)) {
            IPS_CreateVariableProfile($name, VARIABLETYPE_FLOAT);
            IPS_SetVariableProfileText($name, '', $suffix);
            IPS_SetVariableProfileDigits($name, $digits);
            IPS_SetVariableProfileIcon($name, $icon);
        }
        return $name;
    }

    private function CreateProfile(string $name, int $type, array $associations): string
    {
        if (!IPS_VariableProfileExists($name)) {
            IPS_CreateVariableProfile($name, $type);
            foreach ($associations as $association) {
                IPS_SetVariableProfileAssociation($name, $association, enumToName($association), '', -1);
            }
        }
        return $name;
    }
}

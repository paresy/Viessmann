<?php

declare(strict_types=1);

include_once __DIR__ . '/../libs/WebHookModule.php';
include_once __DIR__ . '/compute_name.php';

if (defined('PHPUNIT_TESTSUITE')) {
    trait Simulate
    {
        public function DebugParseDeviceData(object $device)
        {
            $this->failOnUnexpected = true;
            return $this->ParseDeviceData($device);
        }
    }
} else {
    trait Simulate
    {
    }
}

class VitoConnect extends WebHookModule
{
    use Simulate;

    private $authorize_url = 'https://iam.viessmann-climatesolutions.com/idp/v3/authorize';
    private $token_url = 'https://iam.viessmann-climatesolutions.com/idp/v3/token';

    private $user_url = 'https://api.viessmann-climatesolutions.com/users/v1/users/me';
    private $installation_data_url = 'https://api.viessmann-climatesolutions.com/iot/v2/equipment/installations?includeGateways=true';
    private $device_data_url = 'https://api.viessmann-climatesolutions.com/iot/v1/equipment/installations/%s/gateways/%s/devices';
    private $feature_data_url = 'https://api.viessmann-climatesolutions.com/iot/v2/features/installations/%s/gateways/%s/devices/%d/features/';

    public function __construct($InstanceID)
    {
        parent::__construct($InstanceID, 'viessmann/' . $InstanceID);
    }

    public function Create()
    {
        //Never delete this line!
        parent::Create();

        $this->RegisterPropertyString('ClientID', '');

        $this->RegisterPropertyInteger('Interval', 15);

        $this->RegisterAttributeString('Token', '');

        $this->RegisterAttributeInteger('InstallationID', 0);
        $this->RegisterAttributeString('GatewaySerial', '');
        $this->RegisterAttributeString('DeviceID', "0");

        $this->RegisterTimer('Update', 0, 'VVC_Update($_IPS[\'TARGET\']);');
    }

    public function ApplyChanges()
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

    public function Register()
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

        echo $this->authorize_url . '?client_id=' . $this->ReadPropertyString('ClientID') . '&redirect_uri=' . $this->GetCallbackURL() . '&response_type=code&code_challenge_method=S256&code_challenge=' . $code_challenge . '&scope=IoT%20User%20offline_access';
    }

    public function GetConfigurationForm()
    {
        $data = json_decode(file_get_contents(__DIR__ . '/form.json'));

        $data->elements[1]->value = $this->GetCallbackURL();

        $data->actions[0]->enabled = strlen($this->ReadPropertyString('ClientID')) > 0;
        $data->actions[1]->enabled = strlen($this->ReadAttributeString('Token')) > 0;

        return json_encode($data);
    }

    public function Update()
    {
        $this->ParseDeviceData($this->RequestDeviceData());
    }

    public function Dump()
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

    public function RequestAction($Ident, $Value)
    {
        $parts = explode('_', $Ident);
        $name = array_pop($parts);
        $id = implode('.', $parts);
        switch ($name) {
            case 'active':
                if ($Value) {
                    $this->RequestDeviceData($id . '/activate');
                } else {
                    $this->RequestDeviceData($id . '/deactivate');
                }
                $this->SetValue($Ident, $Value);
                break;
            case 'value':
                if (strpos($Ident, 'modes') !== false) {
                    $this->RequestDeviceData($id . '/setMode', [
                        'mode' => $Value
                    ]);
                    $this->SetValue($Ident, $Value);
                } elseif (strpos($Ident, 'hysteresis') !== false) {
                    $this->RequestDeviceData($id . '/setHysteresis', [
                        'hysteresis' => $Value
                    ]);
                    $this->SetValue($Ident, $Value);
                } elseif (strpos($Ident, 'temperature') !== false) {
                    $this->RequestDeviceData($id . '/setTargetTemperature', [
                        'temperature' => $Value
                    ]);
                    $this->SetValue($Ident, $Value);
                } else {
                    throw new Exception('Invalid Ident');
                }
                break;
            case 'temperature':
                $this->RequestDeviceData($id . '/setTemperature', [
                    'targetTemperature' => $Value
                ]);
                $this->SetValue($Ident, $Value);
                break;
            case 'min':
                $this->RequestDeviceData($id . '/setMin', [
                    'temperature' => $Value
                ]);
                $this->SetValue($Ident, $Value);
                break;
            case 'max':
                $this->RequestDeviceData($id . '/setMax', [
                    'temperature' => $Value
                ]);
                $this->SetValue($Ident, $Value);
                break;
            default:
                throw new Exception('Invalid Ident');
        }
    }

    protected function ProcessHookData()
    {
        $this->SendDebug('GET', print_r($_GET, true), 0);
        $this->SendDebug('POST', file_get_contents('php://input'), 0);

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
        $this->SetBuffer('Expires', $data->expires_in);

        $this->Initialize();

        $this->UpdateFormField('Update', 'enabled', true);

        echo $this->Translate("Successful. You can now close this window and press 'Update' inside the instance.");
    }

    private function GetCallbackURL()
    {
        $cc_id = IPS_GetInstanceListByModuleID('{9486D575-BE8C-4ED8-B5B5-20930E26DE6F}')[0];
        $cc_url = @CC_GetConnectURL($cc_id);

        if ($cc_url) {
            return $cc_url . '/hook/viessmann/' . $this->InstanceID;
        }

        return $this->Translate('Symcon Connect must be enabled!');
    }

    private function Initialize()
    {
        //Fetch Installation ID and Gateway Serial for later reuse.
        $installation = $this->FetchData($this->installation_data_url);
        $this->SendDebug('InstallationID', $installation->data[0]->id, 0);
        $this->SendDebug('GatewaySerial', $installation->data[0]->gateways[0]->serial, 0);

        $this->WriteAttributeInteger('InstallationID', $installation->data[0]->id);
        $this->WriteAttributeString('GatewaySerial', $installation->data[0]->gateways[0]->serial);
    }

    private function UpdateAccessToken()
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
            $this->SetBuffer('Expires', $data->expires_in);

            $accessToken = $data->access_token;
        }

        return $accessToken;
    }

    private function FetchData($url)
    {
        $accessToken = $this->UpdateAccessToken();

        //FetchData with Access Token
        $this->SendDebug('FetchData', $url, 0);

        $options = [
            'http' => [
                'header' => 'Authorization: Bearer ' . $accessToken . "\r\n",
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

        if (isset($data->error)) {
            die($data->error);
        }

        return $data;
    }

    private function SendAction($url, $post_data = null)
    {
        $accessToken = $this->UpdateAccessToken();

        //SendAction with Access Token
        $this->SendDebug('SendAction', $url, 0);

        $options = [
            'http' => [
                'method'  => 'POST',
                'header'  => 'Authorization: Bearer ' . $accessToken . "\r\nContent-Type: application/json\r\nAccept: application/vnd.siren+json\r\n",
                'content' => ($post_data == null) ? '{}' : json_encode($post_data)
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result === false) {
            die('Fetching data failed!');
        }

        $this->SendDebug('Success', $result, 0);
    }

    private function RequestDeviceData($action = '', $post_data = null)
    {
        $i_id = $this->ReadAttributeInteger('InstallationID');
        $g_serial = $this->ReadAttributeString('GatewaySerial');
        $d_id = $this->ReadAttributeString('DeviceID');

        if ($i_id == 0 || $g_serial == '') {
            die('InstallationID or GatewaySerial are missing');
        }

        if ($action) {
            return $this->SendAction(sprintf($this->feature_data_url, $i_id, $g_serial, $d_id) . $action, $post_data);
        } else {
            return $this->FetchData(sprintf($this->feature_data_url, $i_id, $g_serial, $d_id));
        }
    }

    private function ParseDeviceData($device)
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
                    die('Unsupported variable type:' . $type . ', id: ' . $id . ', value:' . print_r($value, true));
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

        $updateAction = function ($id, $name, $commands) use ($findCommand)
        {
            $ident = str_replace('.', '_', $id) . '_' . strtolower($name);
            switch ($name) {
                case 'active':
                    if ($findCommand($commands, 'activate') && $findCommand($commands, 'deactivate')) {
                        $this->EnableAction($ident);
                    }
                    break;
                case 'value':
                    if ($findCommand($commands, 'setMode')) {
                        $this->EnableAction($ident);
                    } elseif ($findCommand($commands, 'setHysteresis')) {
                        $this->EnableAction($ident);
                    } elseif ($findCommand($commands, 'setTargetTemperature')) {
                        $this->EnableAction($ident);
                    }
                    break;
                case 'temperature':
                    if ($findCommand($commands, 'setTemperature')) {
                        $this->EnableAction($ident);
                    }
                    break;
                case 'min':
                    if ($findCommand($commands, 'setMin')) {
                        $this->EnableAction($ident);
                    }
                    break;
                case 'max':
                    if ($findCommand($commands, 'setMax')) {
                        $this->EnableAction($ident);
                    }
                    break;
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
                            return ''; // We currently do not have a profile for bar
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
                            return ''; // We currently do not have a profile for seconds
                        default:
                            if (isset($this->failOnUnexpected)) {
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
                        case 'value':
                            $command = $findCommand($commands, 'setMode');
                            if ($command) {
                                return $this->CreateProfile('VVC.Mode', VARIABLETYPE_STRING, $command->params->mode->constraints->enum);
                            } elseif ($findCommand($commands, 'setHysteresis')) {
                                return 'Temperature';
                            } elseif ($findCommand($commands, 'setTargetTemperature')) {
                                return 'Temperature';
                            }
                            return '';
                        case 'temperature':
                            return 'Temperature.Room';
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
                        $updateAction($entity->feature, $name, $entity->commands);
                        break;
                }
            }
        }
    }

    private function CreateProfile($name, $type, $associations)
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

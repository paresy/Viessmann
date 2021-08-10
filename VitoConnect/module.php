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

    private $authorize_url = 'https://iam.viessmann.com/idp/v2/authorize';
    private $token_url = 'https://iam.viessmann.com/idp/v2/token';

    private $installation_data_url = 'https://api.viessmann.com/iot/v1/equipment/installations?includeGateways=true';
    private $device_data_url = 'https://api.viessmann.com/iot/v1/equipment/installations/%s/gateways/%s/devices/0/features/';

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

        $this->RegisterTimer('Update', 0, 'VVC_Update($_IPS[\'TARGET\']);');
    }

    public function ApplyChanges()
    {

        //Never delete this line!
        parent::ApplyChanges();

        //Set Timer only if valid credential are available
        if ($this->ReadAttributeString('Token')) {
            $this->SetTimerInterval('Update', $this->ReadPropertyInteger('Interval') * 60 * 1000);
        } else {
            $this->SetTimerInterval('Update', 0);
        }
    }

    public function Register()
    {
        $base64url_encode = function ($plainText)
        {
            $base64 = base64_encode($plainText);
            $base64 = trim($base64, '=');
            $base64url = strtr($base64, '+/', '-_');
            return $base64url;
        };

        $random = bin2hex(random_bytes(32));
        $this->SetBuffer('Verifier', $base64url_encode(pack('H*', $random)));
        $this->SetBuffer('Challenge', $base64url_encode(pack('H*', hash('sha256', $this->GetBuffer('Verifier')))));

        echo 'https://iam.viessmann.com/idp/v2/authorize?client_id=' . $this->ReadPropertyString('ClientID') . '&redirect_uri=' . $this->GetCallbackURL() . '&response_type=code&code_challenge=' . $this->GetBuffer('Verifier') . '&scope=IoT User offline_access';
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
            case 'temperature':
                $this->RequestDeviceData($id . '/setTemperature', [
                    'targetTemperature' => $Value
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
                    'code'          => $_GET['code'],
                    'redirect_uri'  => $this->GetCallbackURL(),
                    'grant_type'    => 'authorization_code',
                    'code_verifier' => $this->GetBuffer('Verifier')
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

        $this->SendDebug('GotRefreshToken', print_r($data, true), 0);

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
        $id = $this->ReadAttributeInteger('InstallationID');
        $serial = $this->ReadAttributeString('GatewaySerial');

        if ($id == 0 || $serial == '') {
            die('InstallationID or GatewaySerial are missing');
        }

        if ($action) {
            return $this->SendAction(sprintf($this->device_data_url, $id, $serial) . $action, $post_data);
        } else {
            return $this->FetchData(sprintf($this->device_data_url, $id, $serial));
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
                    $this->RegisterVariableString($ident, computeName($id, $name));
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

        $updateAction = function ($id, $name, $commands)
        {
            $hasCommand = function ($name) use ($commands)
            {
                foreach ($commands as $command) {
                    if ($command->name == $name) {
                        return true;
                    }
                }
                return false;
            };

            $ident = str_replace('.', '_', $id) . '_' . strtolower($name);
            switch ($name) {
                case 'active':
                    if ($hasCommand('activate') && $hasCommand('deactivate')) {
                        $this->EnableAction($ident);
                    }
                    break;
                case 'temperature':
                    if ($hasCommand('setTemperature')) {
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
                            return ''; //We currently  do not have a profile for bar
                        case 'cubicMeter':
                            return 'Gas';
                        case 'celsius':
                            return 'Temperature';
                        case 'kilowattHour':
                            return 'Electricity';
                        case 'watt':
                            return 'Watt.3680';
                        case 'kilowatt':
                            return 'Power';
                        case 'percent':
                            return 'Valve.F';
                        case 'seconds':
                            return ''; //We currently  do not have a profile for seconds
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
                $nameToProfile = function ($name)
                {
                    switch ($name) {
                        case 'active':
                            return 'Switch';
                        case 'temperature':
                            return 'Temperature';
                        default:
                            return '';
                    }
                };

                //We want to skip a few fields
                switch ($name) {
                    case 'unit':
                    case 'status':
                        break;
                    case 'dayValueReadAt':
                    case 'weekValueReadAt':
                    case 'monthValueReadAt':
                    case 'yearValueReadAt':
                        $updateVariable($entity->feature, $name, "_Time", $property->value ? strtotime($property->value) : 0, "UnixTimestamp");
                        break;
                    default:
                        $profile = isset($property->unit) ? $unitToProfile($property->unit) : $nameToProfile($name);
                        $updateVariable($entity->feature, $name, $property->type, $property->value, $profile);
                        $updateAction($entity->feature, $name, $entity->commands);
                        break;
                }
            }
        }
    }
}

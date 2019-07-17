<?

class VitoConnect extends IPSModule
{

    private $client_id = '79742319e39245de5f91d15ff4cac2a8';
    private $client_secret = '8ad97aceb92c5892e102b093c7c083fa';

    private $authorize_url = 'https://iam.viessmann.com/idp/v1/authorize';
    private $token_url = 'https://iam.viessmann.com/idp/v1/token';
    
    private $gateway_data_url = 'https://api.viessmann-platform.io/general-management/installations?expanded=true';
    private $device_data_url = 'https://api.viessmann-platform.io/operational-data/installations/%s/gateways/%s/devices/0/features/';

    private $callback_uri = 'vicare://oauth-callback/everest';
    
    public function Create()
    {
        //Never delete this line!
        parent::Create();

        $this->RegisterPropertyString("Username", "");
        $this->RegisterPropertyString("Password", "");

        $this->RegisterPropertyInteger("Interval", 5);

        $this->RegisterAttributeInteger("GatewayID", 0);
        $this->RegisterAttributeString("GatewaySerial", "");

        $this->RegisterTimer("Update", 0, 'VVC_Update($_IPS[\'TARGET\']);');
        
    }

    public function ApplyChanges(){
        
        //Never delete this line!
        parent::ApplyChanges();

        if($this->ReadPropertyString("Username") && $this->ReadPropertyString("Username")) {
            //Fetch Gateway ID and Serial for later reuse
            $gateway = $this->FetchData($this->gateway_data_url);

            $id = $gateway->entities[0]->properties->id;
            $serial = $gateway->entities[0]->entities[0]->properties->serial;

            $this->SendDebug("GatewayID", $id, 0);
            $this->SendDebug("GatewaySerial", print_r($serial, true), 0);
    
            $this->WriteAttributeInteger("GatewayID", $id);
            $this->WriteAttributeString("GatewaySerial", $serial);

            //Set Timer only if valid credential are available
            $this->SetTimerInterval("Update", $this->ReadPropertyInteger("Interval") * 60 * 1000);
        } else {
            $this->SetTimerInterval("Update", 0);
        }

    }
    
    private function FetchAuthorizationCode()
    {

        $this->SendDebug("FetchAuthorizationCode", "", 0);

        $basicAuth = base64_encode($this->ReadPropertyString("Username") . ":" . $this->ReadPropertyString("Password"));

        $options = array(
            'http' => array(
                'header' => "Authorization: Basic " . $basicAuth . "\r\nContent-Type: application/x-www-form-urlencoded\r\n",
                'method' => "POST",
                'content' => http_build_query(Array(
                    "client_id" => $this->client_id,
                    "scope" => "openid",
                    "redirect_uri" => $this->callback_uri,
                    "response_type" => "code"
                )),
                'follow_location' => false
            )
        );

        $context = stream_context_create($options);
        $result = file_get_contents($this->authorize_url, false, $context);

        if (!preg_match('/code=(.*)"/', $result, $matches)) {
            die("Cannot fetch authorization code. Is your username/password correct?");
        }

        $this->SendDebug("GotAuthorizationCode", $matches[1], 0);

        return $matches[1];

    }

    private function FetchAccessToken($code)
    {

        $this->SendDebug("FetchAccessToken", "", 0);

        $options = array(
            'http' => array(
                'header' => "Content-Type: application/x-www-form-urlencoded;charset=utf-8\r\n",
                'method' => "POST",
                'content' => http_build_query(Array(
                    "client_id" => $this->client_id,
                    "client_secret" => $this->client_secret,
                    "code" => $code,
                    "redirect_uri" => $this->callback_uri,
                    "grant_type" => "authorization_code"
                ))
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($this->token_url, false, $context);

        $data = json_decode($result);

        if($data === null) {
            die("Invalid response while fetching access token!");
        }
        
        if(isset($data->error)) {
            die($data->error);
        }
        
        if (!isset($data->token_type) || $data->token_type != "Bearer") {
            die("Bearer Token expected");
        }

        $this->SendDebug("GotAccessToken", print_r($data, true), 0);

        return $data->access_token;

    }

    private function UpdateAccessToken()
    {

        //Request a new Access Token if required
        $accessToken =  $this->GetBuffer("Token");
        if ($accessToken == "" || time() >= intval($this->GetBuffer("Expires"))) {
            $authorizationCode = $this->FetchAuthorizationCode();
            $accessToken = $this->FetchAccessToken($authorizationCode);
            
            $this->SetBuffer("Token", $accessToken);
            $this->SetBuffer("Expires", (time() + 3600));
        }

        return $accessToken;

    }

    private function FetchData($url)
    {

        $accessToken = $this->UpdateAccessToken();

        //FetchData with Access Token
        $this->SendDebug("FetchData", $url, 0);

        $options = array(
            'http' => array(
                'header' => "Authorization: Bearer " . $accessToken . "\r\n",
            )
        );

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        if($result === false) {
            die("Fetching data failed!");
        }

        $this->SendDebug("GotData", $result, 0);

        $data = json_decode($result);
        
        if($data === null) {
            die("Invalid response while fetching data!");
        }

        if(isset($data->error)) {
            die($data->error);
        }
        
        return $data;

    }

    private function SendAction($url, $post_data = null)
    {

        $accessToken = $this->UpdateAccessToken();

        //SendAction with Access Token
        $this->SendDebug("SendAction", $url, 0);

        $options = array(
            'http' => array(
                'method' => "POST",
                'header' => "Authorization: Bearer " . $accessToken . "\r\nContent-Type: application/json\r\nAccept: application/vnd.siren+json\r\n",
                'content' => ($post_data == null) ? "{}" : json_encode($post_data)
            )
        );

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        if($result === false) {
            die("Fetching data failed!");
        }

        $this->SendDebug("Success", $result, 0);
        
    }

    private function RequestDeviceData($action = "", $post_data = null) {

        $id = $this->ReadAttributeInteger("GatewayID");
        $serial = $this->ReadAttributeString("GatewaySerial");
        
        if($id == 0 || $serial == "") {
            die("GatewayID or GatewaySerial are missing");
        }

        if($action) {
            return $this->SendAction(sprintf($this->device_data_url, $id, $serial) . $action, $post_data);
        } else {
            return $this->FetchData(sprintf($this->device_data_url, $id, $serial));
        }

    }
    
    public function Update()
    {

        $device = $this->RequestDeviceData();
        
        $updateVariable = function($id, $name, $type, $value, $profile) {
            $ident = str_replace(".", "_", $id) . "_" . strtolower($name);
            switch($type) {
                case "boolean":
                    $this->RegisterVariableBoolean($ident, $id . " (" . $name . ")", $profile);
                    $this->SetValue($ident, $value);
                    break;
                case "number":
                    $this->RegisterVariableFloat($ident, $id . " (" . $name . ")", $profile);
                    $this->SetValue($ident, $value);
                    break;
                case "string":
                    $this->RegisterVariableString($ident, $id . " (" . $name . ")", $profile);
                    $this->SetValue($ident, $value);
                    break;
                default:
                    die("Unsupported variable type:" . $type);
            }
        };

        $updateAction = function($id, $name, $actions) {
            $hasAction = function($name) use($actions) {
                foreach($actions as $action) {
                    if($action->name == $name) {
                        return true;
                    }
                }
                return false;
            };

            $ident = str_replace(".", "_", $id) . "_" . strtolower($name);
            switch($name) {
                case "active":
                    if($hasAction("activate") && $hasAction("deactivate")) {
                        $this->EnableAction($ident);
                    }
                case "temperature":
                    if($hasAction("setTemperature")) {
                        $this->EnableAction($ident);
                    }
            }
        };
        
        //Parse data
        foreach($device->entities as $entity) {
            if(isset($entity->properties)) {
                foreach($entity->properties as $name => $property) {
                    switch($name) {
                        case "active":
                            $updateVariable($entity->class[0], $name, $property->type, $property->value, "Switch");
                            $updateAction($entity->class[0], $name, $entity->actions);
                            break;
                        case "status":
                        case "statusWired":
                        case "statusWireless":
                            //This is not very interesting
                            //$updateVariable($entity->class[0], $name, $property->type, $property->value, "");
                            break;
                        case "value":
                        case "slope":
                        case "shift":
                        case "name":
                        case "hours":
                        case "starts":
                        case "errorCode":
                            $updateVariable($entity->class[0], $name, $property->type, $property->value, "");
                            break;
                        case "temperature":
                            $updateVariable($entity->class[0], $name, $property->type, $property->value, "Temperature");
                            $updateAction($entity->class[0], $name, $entity->actions);
                            break;
                        case "entries":
                        case "enabled":
                            //Unsupported
                            break;
                        case "start":
                        case "end":
                            //We may convert this to our UnixTimeStamp
                            break;
                        case "serviceDue":
                        case "serviceIntervalMonths":
                        case "activeMonthSinceLastService":
                        case "lastService":
                            //I don't need this
                            break;
                        default:
                            $this->SendDebug($name, $entity->class[0] . " = " . print_r($property, true), 0);
                            break;
                    }
                }
            }
        }
        
    }

    public function RequestAction($Ident, $Value)
    {
        $parts = explode("_", $Ident);
        $name = array_pop($parts);
        $id = implode(".", $parts);
        switch($name) {
            case "active":
                if($Value) {
                    $this->RequestDeviceData($id . "/activate");
                } else {
                    $this->RequestDeviceData($id . "/deactivate");
                }
                SetValue($this->GetIDForIdent($Ident), $Value);
                break;
            case "temperature":
                $this->RequestDeviceData($id . "/setTemperature", [
                    "targetTemperature" => $Value
                ]);
                SetValue($this->GetIDForIdent($Ident), $Value);
                break;
            default:
                throw new Exception("Invalid Ident");
        }
     
    }

}

?>

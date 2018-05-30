<?php
/**
 * Created by IntelliJ IDEA.
 * User: ilies
 * Date: 14/04/18
 * Time: 11:09
 */

namespace EventHandler;

use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_ValueRange;
use GoogleSheetAPI\GoogleSheetAPIClient;
use Helpers\ResponseHelper;

class EventHandler
{

    /**
     * Parse event and execute the right function
     * @param $payload
     * @return bool
     */
    public static function parseEvent($payload)
    {
        $event = json_decode($payload, true);

        if (isset($event['eventType']))
        {
            if ($event['eventType'] == 5) {
                $GLOBALS['sender']->send(ResponseHelper::createTaskResponse(ResponseState::NotReady, null));
                return (self::stop());
            }
            $handler = EventType::getEvent($event['eventType']);

            self::$handler($event['data']);
        }
    }

    /**
     * Load eyes accounts
     * @param $data
     */
    public function loadEyesAccounts($data)
    {
        $GLOBALS['eyesAccounts'] = $data['accounts'];
    }

    /**
     * Load user accounts
     * @param $data
     */
    public function loadUserAccounts($data)
    {
        $GLOBALS['userAccounts'] = $data['accounts'];
    }

    /**
     * Load eyes messages
     * @param $data
     */
    public function loadEyesMessages($data)
    {
        $GLOBALS['eyesMessages'] = $data['messages'];
    }

    /**
     * Fire Start event that launch StartMessaging()
     * @param $data
     */
    public function start($data)
    {
        $GLOBALS['isStopped'] = false;
        echo 'Application has been started'.PHP_EOL;
        $GLOBALS['messagingEngine']->startMessaging();
    }

    public static function stop()
    {
        @system("rm pids.log");
        @system("ps -ef | grep instadm | grep -v grep | awk '{print $2}' >> pids.log");
        $pids = explode("\n", file_get_contents("pids.log"));

        foreach ($pids as $pid)
        {
            @system("kill ".$pid);
        }

        $GLOBALS['sender']->purge();
        echo 'Application has been stopped'.PHP_EOL;
        $GLOBALS['sender']->send(ResponseHelper::createTaskResponse(ResponseState::Ready, null));
        return 1;
    }

    public static function connectVpn($data)
    {
        $data = json_decode($data, true);
        $vpnNumber = $data["number"];
        $vpnLocalisation = $data["location"];
        $vpnLicence = explode(';', $data["licence"]);
        $openVpnServerPath = "/etc/openvpn/ovpn_tcp/";
        $vpnPid = explode("\n", file_get_contents("vpn.log"));

        if (isset($data) && $data != "") {
            system("printf \"" . $vpnLicence[0] . "\\n" . $vpnLicence[1] . "\" > " . $openVpnServerPath . "user.txt");
            //system("printf \"auth-user-path user.txt\n\" >> " . $openVpnServerPath . $vpnLocalisation . $vpnNumber . ".nordvpn.com.tcp.ovpn");
            @system("kill " . $vpnPid[0]);
            @system("rm vpn.log");
            system("cd ".$openVpnServerPath.";"." openvpn --config ". $vpnLocalisation . $vpnNumber . ".nordvpn.com.tcp.ovpn  --auth-user-pass user.txt  &");
            system("ps -ef | grep openvpn | grep -v grep | awk '{print $2}' >> vpn.log");
            sleep(5);
            $vpnStatus = system("ip link show dev tun0 > /dev/null; echo $?");
            if ($vpnStatus == "0" || $vpnStatus == 0) {
                //$GLOBALS['sender']->send(ResponseHelper::createTaskResponse(ResponseState::Ready, null));
                $GLOBALS['sender']->send(ResponseHelper::createTaskResponse(ResponseState::VpnConnected, ["email" => $vpnLicence[0], "password" => $vpnLicence[1], "localisation" => $vpnLocalisation, "number" => $vpnNumber]));
                $GLOBALS['vpn'] = ["state" => ResponseState::VpnConnected, "email" => $vpnLicence[0], "password" => $vpnLicence[1], "localisation" => $vpnLocalisation, "number" => $vpnNumber];
            } else {
                //$GLOBALS['sender']->send(ResponseHelper::createTaskResponse(ResponseState::NotReady, null));
                $GLOBALS['sender']->send(ResponseHelper::createTaskResponse(ResponseState::VpnNotConnected, ["email" => $vpnLicence[0], "password" => $vpnLicence[1], "localisation" => $vpnLocalisation, "number" => $vpnNumber]));
                $GLOBALS['vpn'] = ["state" => ResponseState::VpnNotConnected, "email" => $vpnLicence[0], "password" => $vpnLicence[1], "localisation" => $vpnLocalisation, "number" => $vpnNumber];
            }
        }
    }

    public static function disconnectVpn()
    {
        $vpnPid = explode("\n", file_get_contents("vpn.log"));
        @system("kill " . $vpnPid[0]);
        $GLOBALS['sender']->send(ResponseHelper::createTaskResponse(ResponseState::VpnNotConnected, null));
        $GLOBALS['vpn'] = ["state" => ResponseState::VpnNotConnected];
    }

    public static function requestState($data)
    {
        $GLOBALS['sender']->send(ResponseHelper::createTaskResponse(ResponseState::Ready, null));
        $GLOBALS['sender']->send(ResponseHelper::createTaskResponse($GLOBALS['vpn']["state"], ["email" => $GLOBALS['vpn']['email'], "password" => $GLOBALS['vpn']['password'], "localisation" => $GLOBALS['vpn']['localisation'], "number" => $GLOBALS['vpn']['number']]));
    }

    public static function updateUserBase($data)
    {
        $columnNumber = 8;
        $apiKey = "AIzaSyCxJ7U3CZXlCjhECGltVITRUILbwO43UXk";
        $spreadsheetId = '1XKFVaoCOYf5886DvxpK57O7QgoF-yIArAfsUzkjoxI4';

        $client = new Google_Client();
        $client->setScopes([Google_Service_Sheets::SPREADSHEETS]);
        $client->setAuthConfig('My Project-fe48b187403f.json');
        $service = new Google_Service_Sheets($client);
        $users = json_decode($data, true);
        $response = $service->spreadsheets_values->get($spreadsheetId, "A1:Z10000");

        $lineToInsert = count($users);
        $actualNbLines = count($response["values"]);
        $valuesToInsert = [];
        $date = new \DateTime();

        foreach ($users as $user) {
            array_push($valuesToInsert, [$user["Sender"], $user["Username"], $date->format("Y-m-d"), $user["UserURL"]]);
        }

        $postBody = new Google_Service_Sheets_ValueRange(['values' => $valuesToInsert]);
        var_dump(print_r($postBody, 1));
        $requests = [new \Google_Service_Sheets_Request(
            [

            ]
        )];

        $response = $service->spreadsheets_values->append($spreadsheetId, "A:E", $postBody, ["valueInputOption" => "RAW"]);
        var_dump(print_r($response, 1));
    }
}
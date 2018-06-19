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
        $pids = explode('\n', system("ps -ef | grep instadm | grep -v grep | awk '{print $2}'"));

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

        self::disconnectVpn();
        self::setVpnLicence($vpnLicence[0], $vpnLicence[1]);
        if (isset($data) && $data != "") {

            system("openvpn --config ".$openVpnServerPath.$vpnLocalisation.$vpnNumber.".nordvpn.com.tcp.ovpn --auth-user-pass " . $openVpnServerPath . "user.txt > /dev/null &");
            sleep(7);
            $vpnStatus = system("ip link show dev tun0 > /dev/null; echo $?");

            if ($vpnStatus == "1") {

                self::disconnectVpn();
                self::connectVpn($data);
                $GLOBALS['sender']->send(ResponseHelper::createTaskResponse(ResponseState::VpnNotConnected, ["email" => $vpnLicence[0], "password" => $vpnLicence[1], "localisation" => $vpnLocalisation, "number" => $vpnNumber]));
                $GLOBALS['vpn'] = ["state" => ResponseState::VpnNotConnected, "email" => $vpnLicence[0], "password" => $vpnLicence[1], "localisation" => $vpnLocalisation, "number" => $vpnNumber];
            }
            else {

                print "Vpn connected to " . $vpnLocalisation.$vpnNumber . PHP_EOL;
                system("cat ".$openVpnServerPath.'user.txt');
                $GLOBALS['sender']->send(ResponseHelper::createTaskResponse(ResponseState::VpnConnected, ["email" => $vpnLicence[0], "password" => $vpnLicence[1], "localisation" => $vpnLocalisation, "number" => $vpnNumber]));
                $GLOBALS['vpn'] = ["state" => ResponseState::VpnConnected, "email" => $vpnLicence[0], "password" => $vpnLicence[1], "localisation" => $vpnLocalisation, "number" => $vpnNumber];
                print PHP_EOL;
            }
        }
    }

    public static function setVpnLicence($email, $password)
    {
        system("printf \"" . $email . "\\n" . $password . "\" > /etc/openvpn/ovpn_tcp/user.txt");
    }

    public static function disconnectVpn()
    {
        $vpnPids = explode('\n', system("ps -ef | grep openvpn | grep -v grep | awk '{print $2}'"));

        foreach ($vpnPids as $vpnPid) {

            if (isset($vpnPid) && $vpnPid != "") {

                system("kill " . $vpnPid);
                print "Vpn disconnected" . PHP_EOL;
            }
        }
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
        //var_dump(print_r($postBody, 1));

        $gridRangeObject = new \Google_Service_Sheets_GridRange();
        $sortSpecsObject = new \Google_Service_Sheets_SortSpec();
        $sortRangeRequest = new \Google_Service_Sheets_SortRangeRequest();
        $batchUpdate = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest();
        $gridRangeObject->setSheetId($spreadsheetId);
        $gridRangeObject->setStartColumnIndex(0);
        $gridRangeObject->setStartRowIndex(1);
       // $gridRangeObject->setEndColumnIndex(4);
       // $gridRangeObject->setEndRowIndex();

        $sortSpecsObject->setDimensionIndex(0);
        $sortSpecsObject->setSortOrder("ASCENDING");
        $sortRangeRequest->setSortSpecs([$sortSpecsObject]);
        $sortRangeRequest->setRange($gridRangeObject);
        $batchUpdate->setRequests(
            [
                "sortRange" => [
                    "sortSpecs" => [
                        [
                            "dimensionIndex" => 0,
                            "sortOrder"=> "ASCENDING"
                        ]
                    ],
                    "range" => [
                        "startRowIndex" => 1
                    ]
                ]
            ]);

        $response = $service->spreadsheets_values->append($spreadsheetId, "A:D", $postBody, ["valueInputOption" => "RAW"]);
        if ($response->getUpdates()->getUpdatedRows() != null) {

            $GLOBALS['sender']->send(ResponseHelper::createTaskResponse(ResponseState::UserFileUpdated, null));
            var_dump(print_r($response, 1));
        }
        else
            $GLOBALS['sender']->send(ResponseHelper::createTaskResponse(ResponseState::UserFileNotUpdated, null));

        $response = $service->spreadsheets->batchUpdate($spreadsheetId, $batchUpdate);
        //var_dump(print_r($batchUpdate, 1));
        //var_dump(print_r($response, 1));

    }
}
<?php
require('./configs/config.php');
require_once('./api/PwAPI.php');

$api = new API();

$argv[1]($argv[2]);

function insertMeridian($line = null)
{
    global $config;
    global $api;

    $conn = new mysqli($config['mysql']['host'], $config['mysql']['user'], $config['mysql']['password'], $config['mysql']['db']);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if (strpos($line, "DeliverByAwardData: success = 1") !== false) {
        preg_match('/roleid=(\d+):taskid=(\d+):/', $line, $matches);
        $roleID = $matches[1];
        $missionID = $matches[2];

        // ID da tasks que será monitorada
        $missionToMonitor = 33469;

        if ($missionID == $missionToMonitor) {
            $idAcc = $api->getRoleBase($roleID);
            $userId = $idAcc['userid'];
            $roleName = $idAcc['name'];

            $stmt = $conn->prepare("SELECT zoneid FROM point WHERE uid = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->bind_result($zoneid);
            $stmt->fetch();
            $stmt->close();

            if ($zoneid == 1) {
                //$api->chatWhisper($roleID, $roleName, "A sua conta precisa estar deslogada para aplicar o meridiano, disconecte imediatamente e aguarde 5 minutos ", 14);
                $api->chatInGame("$roleName deslogue sua conta!");

                while ($zoneid == 1) {
                    sleep(60);
                    
                    $stmt = $conn->prepare("SELECT zoneid FROM point WHERE uid = ?");
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                    $stmt->bind_result($zoneid);
                    $stmt->fetch();
                    $stmt->close();
                }
            }

            $stmt = $conn->prepare("SELECT COUNT(*) FROM meridiano WHERE roleID = ? AND active = 1");
            $stmt->bind_param("i", $roleID);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if ($count > 0) {
                $api->chatWhisper($roleID, $roleName, "Meridiano já está ativo para este personagem $roleName.", 14);
                return;
            }

            $roleData = $api->getRole($roleID);

            if ($roleData['status']['meridian_data'] != '000000500000000000000000000000050000006400003f920000000100000000000000000000000000000000000000000000000000000000') {

                $roleData['status']['meridian_data'] = '000000500000000000000000000000050000006400003f920000000100000000000000000000000000000000000000000000000000000000';

                if ($api->putRole($roleID, $roleData)) {
                    $stmt = $conn->prepare("INSERT INTO meridiano (roleID) VALUES (?)");
                    $stmt->bind_param("i", $roleID);
                    $stmt->execute();
                    $stmt->close();

                    echo 'success';
                    $api->chatInGame("$roleName ativou o Full Meridiano com sucesso e agora está mais poderoso do que nunca!");
                } else {
                    echo 'system error';
                }
            } else {
                $api->chatWhisper($roleID, $roleName, "Meridiano já está ativo para este personagem $roleName.", 14);
                return;
            }
        }
    }

    $conn->close();
}

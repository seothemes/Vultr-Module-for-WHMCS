<?php 
function vultr_getDisableSsl()
{
    $result = select_query("mod_vultr_settings", "", array( "setting" => "disable_ssl" ));
    $data = mysql_fetch_array($result);
    return $data["value"];
}

function vultr_getApiKey()
{
    $result = select_query("tbladdonmodules", "", array( "module" => "vultr", "setting" => "vultr_apikey" ));
    $data = mysql_fetch_array($result);
    return $data["value"];
}

function vultr_getLicence()
{
    $result = select_query("tbladdonmodules", "", array( "module" => "vultr", "setting" => "vultr_licence" ));
    $data = mysql_fetch_array($result);
    return $data["value"];
}

function vultr_getLocalKey()
{
    $result = select_query("mod_vultr_settings", "", array( "setting" => "localkey" ));
    $data = mysql_fetch_array($result);
    return $data["value"];
}

function vultr_getNs()
{
    $result = select_query("mod_vultr_settings", "", array( "setting" => "ns1" ));
    $data = mysql_fetch_array($result);
    $ns["ns1"] = $data["value"];
    $result = select_query("mod_vultr_settings", "", array( "setting" => "ns2" ));
    $data = mysql_fetch_array($result);
    $ns["ns2"] = $data["value"];
    return $ns;
}

function vultr_getServerId($pid, $serviceid)
{
    $result = select_query("tblcustomfields", "", array( "relid" => $pid, "fieldname" => "server_id" ));
    $data = mysql_fetch_array($result);
    $var1 = $data["id"];
    $result2 = select_query("tblcustomfieldsvalues", "", array( "relid" => $serviceid, "fieldid" => $var1 ));
    $data2 = mysql_fetch_array($result2);
    return $data2["value"];
}

function vultr_sendAdminEmail($subject, $message)
{
    $values["customsubject"] = $subject;
    $values["custommessage"] = $message;
    $values["type"] = "system";
    localAPI("sendadminemail", $values, 1);
}

function vultr_getServer($params, $server_id)
{
    $disable_ssl = vultr_getdisablessl();
    $apikey = vultr_getapikey();
    sleep(1);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.vultr.com/v1/server/list?api_key=" . $apikey);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if( $disable_ssl == "yes" ) 
    {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }

    $data = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $response = json_decode($data, true);
    if( $http_status == "200" ) 
    {
        $server_details = $response[$server_id];
        if( $params["password"] == "See client area" ) 
        {
            $command = "encryptpassword";
            $adminuser = 1;
            $values["password2"] = $server_details["default_password"];
            $password = localAPI($command, $values, $adminuser);
            update_query("tblhosting", array( "dedicatedip" => $server_details["main_ip"], "password" => $password["password"] ), array( "id" => $params["serviceid"] ));
            vultr_rename($params, $params["domain"]);
        }

    }

    logModuleCall("vultr", "getserver (" . $server_id . ")", NULL, $response, NULL, NULL);
    return $response[$server_id];
}

function vultr_getServerIp($server_id)
{
    $disable_ssl = vultr_getdisablessl();
    $apikey = vultr_getapikey();
    sleep(1);
    if( $disable_ssl == "yes" ) 
    {
        $ip_options = array( "CURLOPT_SSL_VERIFYPEER" => false );
    }

    $ip_data = curlCall("https://api.vultr.com/v1/server/list_ipv4?api_key=" . $apikey . "&SUBID=" . $server_id, NULL, $ip_options);
    $response = json_decode($ip_data, true);
    logModuleCall("vultr", "getserverip (" . $server_id . ")", NULL, $response, NULL, NULL);
    return $response;
}

function vultr_getServerIp6($server_id)
{
    $disable_ssl = vultr_getdisablessl();
    $apikey = vultr_getapikey();
    sleep(1);
    if( $disable_ssl == "yes" ) 
    {
        $ip6_options = array( "CURLOPT_SSL_VERIFYPEER" => false );
    }

    $ip6_data = curlCall("https://api.vultr.com/v1/server/list_ipv6?api_key=" . $apikey . "&SUBID=" . $server_id, NULL, $ip6_options);
    $response = json_decode($ip6_data, true);
    logModuleCall("vultr", "getserverip6 (" . $server_id . ")", NULL, $response, NULL, NULL);
    return $response;
}

function vultr_getServerIp6Reverse($server_id)
{
    $disable_ssl = vultr_getdisablessl();
    $apikey = vultr_getapikey();
    sleep(1);
    if( $disable_ssl == "yes" ) 
    {
        $ip6_options = array( "CURLOPT_SSL_VERIFYPEER" => false );
    }

    $ip6_data = curlCall("https://api.vultr.com/v1/server/reverse_list_ipv6?api_key=" . $apikey . "&SUBID=" . $server_id, NULL, $ip6_options);
    $response = json_decode($ip6_data, true);
    logModuleCall("vultr", "getserverip6reverse (" . $server_id . ")", NULL, $response, NULL, NULL);
    return $response;
}

function vultr_ConfigOptions()
{
    $disable_ssl = vultr_getdisablessl();
    $apikey = vultr_getapikey();
    sleep(1);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.vultr.com/v1/plans/list");
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if( $disable_ssl == "yes" ) 
    {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }

    $data = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $response = json_decode($data, true);
    $sizedesc = "<h3><a href=\"addonmodules.php?module=vultr&tab=availability\" target=\"_blank\">Click here</a> for current availability</h3>";
    $sizelist = "";
    $sizelist .= $id . ",";
    sleep(1);
    $s_url = "https://api.vultr.com/v1/startupscript/list?api_key=" . $apikey;
    $s_postfields = array(  );
    if( $disable_ssl == "yes" ) 
    {
        $s_options = array( "CURLOPT_SSL_VERIFYPEER" => false );
    }

    $s_data = curlCall($s_url, $s_postfields, $s_options);
    $s_response = json_decode($s_data, true);
    $scriptdesc = "<h3><a href=\"addonmodules.php?module=vultr&tab=scripts\" target=\"_blank\">Click here</a> to add a script</h3>";
    $scriptlist = "";
    $scriptlist .= $script["SCRIPTID"] . ",";
    $configarray = array( "plan_id" => array( "Type" => "dropdown", "Options" => $sizelist, "Description" => $sizedesc ), "script_id" => array( "Type" => "dropdown", "Options" => $scriptlist, "Description" => $scriptdesc ) );
    return $configarray;
}

function vultr_CreateAccount($params)
{
    $serviceid = $params["serviceid"];
    $pid = $params["pid"];
    $domain = $params["domain"];
    $planid = $params["configoption1"];
    $scriptid = $params["configoption2"];
    $region = $params["configoptions"]["Datacenter"];
    $image = $params["configoptions"]["Operating System"];
    $application = $params["configoptions"]["Application"];
    $region = explode("|", $region);
    $region = $region[0];
    $image = explode("|", $image);
    $image = $image[0];
    $application = explode("|", $application);
    $application = $application[0];
    $disable_ssl = vultr_getdisablessl();
    $apikey = vultr_getapikey();
    $licence = vultr_getlicence();
    $localkey = vultr_getlocalkey();
    $result = select_query("mod_do2_settings", "", array( "setting" => "host_domain" ));
    $data = mysql_fetch_array($result);
    $host_domain = $data["value"];
    $check_license = vultr_check_license($licence, $localkey);
    if( $check_license["status"] == "Active" ) 
    {
        update_query("mod_vultr_settings", array( "value" => $check_license["localkey"] ), array( "setting" => "localkey" ));
        if( empty($domain) ) 
        {
            $domain = "srv" . $serviceid . "." . $host_domain;
            update_query("tblhosting", array( "domain" => $domain ), array( "id" => $serviceid ));
        }

        sleep(1);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.vultr.com/v1/server/create?api_key=" . $apikey);
        curl_setopt($ch, CURLOPT_POST, 1);
        if( !empty($scriptid) ) 
        {
            $script = "&SCRIPTID=" . $scriptid;
        }
        else
        {
            $script = "";
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, "DCID=" . $region . "&VPSPLANID=" . $planid . "&OSID=" . $image . "&APPID=" . $application . "&label=" . $domain . "&enable_ipv6=yes&enable_private_network=yes" . $script);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if( $disable_ssl == "yes" ) 
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        $data = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $response = json_decode($data, true);
        if( $http_status == "200" ) 
        {
            $successful = true;
            $server_id = $response["SUBID"];
        }
        else
        {
            $errorinfo = "API error. " . $http_status . " " . $data;
        }

    }
    else
    {
        $errorinfo = "Licence " . $check_license["status"] . ".";
    }

    if( $successful ) 
    {
        $errorinfo = "";
        $result = select_query("tblcustomfields", "", array( "relid" => $pid, "fieldname" => "server_id" ));
        $data = mysql_fetch_array($result);
        $var1 = $data["id"];
        if( !empty($var1) ) 
        {
            if( 0 < mysql_num_rows(select_query("tblcustomfieldsvalues", "*", array( "fieldid" => $var1, "relid" => $serviceid ))) ) 
            {
                update_query("tblcustomfieldsvalues", array( "value" => $server_id ), array( "fieldid" => $var1, "relid" => $serviceid ));
                logModuleCall("vultr", "updateCF", "pid=" . $pid . ", fieldid=" . $var1 . ", relid=" . $serviceid . ", server_id=" . $server_id, NULL, NULL, NULL);
            }
            else
            {
                $newid = insert_query("tblcustomfieldsvalues", array( "fieldid" => $var1, "value" => $server_id, "relid" => $serviceid ));
                logModuleCall("vultr", "insertCF", "pid=" . $pid . ", fieldid=" . $var1 . ", relid=" . $serviceid . ", server_id=" . $server_id, $newid, NULL, NULL);
            }

        }
        else
        {
            $errorinfo = "The server_id custom field doesn't exist. Check the install instructions.";
            logModuleCall("vultr", "create", "The server_id custom field doesn't exist. Check the install instructions.", NULL, NULL, NULL);
        }

        sleep(1);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.vultr.com/v1/server/label_set?api_key=" . $apikey);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "SUBID=" . $server_id . "&label=" . $domain);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if( $disable_ssl == "yes" ) 
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        curl_exec($ch);
        curl_close($ch);
        sleep(15);
        $server_details = vultr_getserver($params, $server_id);
        logModuleCall("vultr", "create", "pid=" . $pid . ", fieldid=" . $var1 . ", relid=" . $params["serviceid"] . ", server_id=" . $server_id, $server_details, NULL, NULL);
        if( empty($server_ip) ) 
        {
            sleep(15);
            $server_details = vultr_getserver($params, $server_id);
        }

        if( empty($server_details["main_ip"]) ) 
        {
            $result = "success";
            $server_ip = "See client area";
            $server_password = "See client area";
        }
        else
        {
            $result = "success";
            $server_ip = $server_details["main_ip"];
            $server_password = $server_details["default_password"];
            vultr_rename($params, $domain);
        }

        if( !empty($errorinfo) ) 
        {
            $result = $errorinfo;
        }

        $command = "encryptpassword";
        $adminuser = 1;
        $values["password2"] = $server_password;
        $password = localAPI($command, $values, $adminuser);
        update_query("tblhosting", array( "dedicatedip" => $server_ip, "username" => "root", "password" => $password["password"] ), array( "id" => $params["serviceid"] ));
    }
    else
    {
        $result = "Something has gone wrong. Please manually check and/or create the server. Info: " . $errorinfo;
        vultr_sendadminemail("WHMCS: Automatic Creation Failed", "<p>Automatic creation of a Vultr server has failed.</p> <p><strong>Service:</strong> " . $serviceid . "</p> <p><strong>Error:</strong> <br>" . $result . "</p>");
    }

    return $result;
}

function vultr_TerminateAccount($params)
{
    $serviceid = $params["serviceid"];
    $pid = $params["pid"];
    $disable_ssl = vultr_getdisablessl();
    $apikey = vultr_getapikey();
    $server_id = vultr_getserverid($pid, $serviceid);
    sleep(1);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.vultr.com/v1/server/destroy?api_key=" . $apikey);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "SUBID=" . $server_id);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if( $disable_ssl == "yes" ) 
    {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }

    $data = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $response = json_decode($data, true);
    if( $http_status == "200" ) 
    {
        $successful = true;
        $result = select_query("mod_vultr_domains", "", array( "service_id" => $serviceid ));
        while( $data = mysql_fetch_array($result) ) 
        {
            vultr_deletedomain($params, $data["domain"]);
        }
    }

    if( $successful ) 
    {
        update_query("tblhosting", array( "dedicatedip" => "" ), array( "id" => $params["serviceid"] ));
        $result = "success";
    }
    else
    {
        $result = "ERROR! Could not destroy the server  (" . $http_status . " " . $data . ").";
    }

    return $result;
}

function vultr_SuspendAccount($params)
{
    $serviceid = $params["serviceid"];
    $pid = $params["pid"];
    $disable_ssl = vultr_getdisablessl();
    $apikey = vultr_getapikey();
    $server_id = vultr_getserverid($pid, $serviceid);
    sleep(1);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.vultr.com/v1/server/halt?api_key=" . $apikey);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "SUBID=" . $server_id);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if( $disable_ssl == "yes" ) 
    {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }

    $data = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $response = json_decode($data, true);
    if( $http_status == "200" ) 
    {
        $successful = true;
    }

    if( $successful ) 
    {
        $result = "success";
    }
    else
    {
        $result = "Could not suspend server (" . $http_status . " " . $data . ").";
    }

    return $result;
}

function vultr_UnsuspendAccount($params)
{
    $serviceid = $params["serviceid"];
    $pid = $params["pid"];
    $disable_ssl = vultr_getdisablessl();
    $apikey = vultr_getapikey();
    $server_id = vultr_getserverid($pid, $serviceid);
    sleep(1);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.vultr.com/v1/server/start?api_key=" . $apikey);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "SUBID=" . $server_id);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if( $disable_ssl == "yes" ) 
    {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }

    $data = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $response = json_decode($data, true);
    if( $http_status == "200" ) 
    {
        $successful = true;
    }

    if( $successful ) 
    {
        $result = "success";
    }
    else
    {
        $result = "Could not unsuspend server  (" . $http_status . " " . $data . ").";
    }

    return $result;
}

function vultr_ClientArea($params)
{
    $serviceid = $params["serviceid"];
    $pid = $params["pid"];
    $disable_ssl = vultr_getdisablessl();
    $apikey = vultr_getapikey();
    $licence = vultr_getlicence();
    $localkey = vultr_getlocalkey();
    $ns = vultr_getns();
    $alert_error = "";
    $alert_success = "";
    $check_license = vultr_check_license($licence, $localkey);
    if( $check_license["status"] == "Active" ) 
    {
        update_query("mod_vultr_settings", array( "value" => $check_license["localkey"] ), array( "setting" => "localkey" ));
        $server_id = vultr_getserverid($pid, $serviceid);
        if( $_POST["function"] == "reboot" ) 
        {
            $function = vultr_reboot($params);
            if( $function == "success" ) 
            {
                header("Location: clientarea.php?action=productdetails&id=" . $serviceid . "&response=success");
            }
            else
            {
                $alert_error .= $function;
            }

        }
        else
        {
            if( $_POST["function"] == "poweron" ) 
            {
                $function = vultr_start($params);
                if( $function == "success" ) 
                {
                    header("Location: clientarea.php?action=productdetails&id=" . $serviceid . "&response=success");
                }
                else
                {
                    $alert_error .= $function;
                }

            }
            else
            {
                if( $_POST["function"] == "poweroff" ) 
                {
                    $function = vultr_halt($params);
                    if( $function == "success" ) 
                    {
                        header("Location: clientarea.php?action=productdetails&id=" . $serviceid . "&response=success");
                    }
                    else
                    {
                        $alert_error .= $function;
                    }

                }
                else
                {
                    if( $_POST["function"] == "destroy" ) 
                    {
                        $command = "moduleterminate";
                        $adminuser = 1;
                        $values["accountid"] = $serviceid;
                        $function = localAPI($command, $values, $adminuser);
                        if( $function["result"] == "success" ) 
                        {
                            header("Location: clientarea.php?action=productdetails&id=" . $serviceid);
                        }
                        else
                        {
                            $alert_error .= $function["message"];
                        }

                    }
                    else
                    {
                        if( $_POST["function"] == "rebuild" ) 
                        {
                            sleep(1);
                            $url = "https://api.vultr.com/v1/server/os_change?api_key=" . $apikey;
                            $postfields = array( "SUBID" => $server_id, "OSID" => $_POST["image"] );
                            if( $disable_ssl == "yes" ) 
                            {
                                $options = array( "CURLOPT_SSL_VERIFYPEER" => false );
                            }

                            $data = curlCall($url, $postfields, $options);
                            $response = json_decode($data, true);
                            header("Location: clientarea.php?action=productdetails&id=" . $serviceid . "&response=success");
                        }
                        else
                        {
                            if( $_POST["function"] == "adddomain" ) 
                            {
                                $function = vultr_adddomain($params, $_POST["domain"], $_POST["ip_address"]);
                                if( $function == "success" ) 
                                {
                                    header("Location: clientarea.php?action=productdetails&id=" . $serviceid . "&response=success");
                                }
                                else
                                {
                                    $alert_error .= $function;
                                }

                            }
                            else
                            {
                                if( $_POST["function"] == "deletedomain" ) 
                                {
                                    $function = vultr_deletedomain($params, $_POST["domain"]);
                                    if( $function == "success" ) 
                                    {
                                        header("Location: clientarea.php?action=productdetails&id=" . $serviceid . "&response=success");
                                    }
                                    else
                                    {
                                        $alert_error .= $function;
                                    }

                                }
                                else
                                {
                                    if( $_POST["function"] == "adddomainrecord" ) 
                                    {
                                        $function = vultr_adddomainrecord($params, $_POST["domain"], $_POST["type"], $_POST["name"], $_POST["data"], $_POST["priority"]);
                                        if( $function == "success" ) 
                                        {
                                            header("Location: clientarea.php?action=productdetails&id=" . $serviceid . "&response=success");
                                        }
                                        else
                                        {
                                            $alert_error .= $function;
                                        }

                                    }
                                    else
                                    {
                                        if( $_POST["function"] == "deletedomainrecord" ) 
                                        {
                                            $function = vultr_deletedomainrecord($params, $_POST["domain"], $_POST["record_id"]);
                                            if( $function == "success" ) 
                                            {
                                                header("Location: clientarea.php?action=productdetails&id=" . $serviceid . "&response=success");
                                            }
                                            else
                                            {
                                                $alert_error .= $function;
                                            }

                                        }
                                        else
                                        {
                                            if( $_POST["function"] == "rename" ) 
                                            {
                                                $function = vultr_rename($params, $_POST["hostname"]);
                                                if( $function == "success" ) 
                                                {
                                                    header("Location: clientarea.php?action=productdetails&id=" . $serviceid . "&response=success");
                                                }
                                                else
                                                {
                                                    $alert_error .= $function;
                                                }

                                            }

                                        }

                                    }

                                }

                            }

                        }

                    }

                }

            }

        }

        $server_details = vultr_getserver($params, $server_id);
        $ip_details = vultr_getserverip($server_id);
        $ip6_details = vultr_getserverip6($server_id);
        $ip6_reverse_details = vultr_getserverip6reverse($server_id);
        $result = select_query("mod_vultr_domains", "", array( "service_id" => $serviceid ));
        $domains = array(  );
        for( $dom_i = 0; $data = mysql_fetch_array($result); $dom_i++ ) 
        {
            $domains[$dom_i]["name"] = $data["domain"];
            sleep(1);
            $url = "https://api.vultr.com/v1/dns/records?api_key=" . $apikey . "&domain=" . $data["domain"];
            if( $disable_ssl == "yes" ) 
            {
                $verifypeer = false;
            }
            else
            {
                $verifypeer = true;
            }

            $options = array( "CURLOPT_SSL_VERIFYPEER" => $verifypeer );
            $response = curlCall($url, NULL, $options);
            $response = json_decode($response, true);
            logModuleCall("vultr", "getdomainrecords (" . $data["domain"] . ")", NULL, $response, NULL, NULL);
            if( !empty($response) ) 
            {
                $domains[$dom_i]["records"] = $response;
            }

        }
        $result3 = "SELECT tblproductconfigoptionssub.optionname FROM `tblproductconfiglinks` LEFT JOIN tblproductconfigoptions ON tblproductconfiglinks.gid=tblproductconfigoptions.gid LEFT JOIN tblproductconfigoptionssub ON tblproductconfigoptions.id=tblproductconfigoptionssub.configid WHERE pid=" . $pid . " AND tblproductconfigoptions.optionname='Operating System' AND tblproductconfigoptionssub.hidden=0";
        $query = mysql_query($result3);
        while( $row = mysql_fetch_assoc($query) ) 
        {
            $images[] = $row["optionname"];
        }
        $imgselect = "<select name=\"image\">";
        $img = explode("|", $image);
        if( $img[1] != "Custom" && $img[1] != "Backup" && $img[1] != "Snapshot" && $img[1] != "Application" ) 
        {
            $imgselect .= "<option value=\"" . $img[0] . "\">" . $img[1] . "</option>";
        }

        $imgselect .= "</select>";
        $used_bandwidth_percent = $server_details["current_bandwidth_gb"] / $server_details["allowed_bandwidth_gb"] * 100;
        $templateFile = "templates/overview.tpl";
        return array( "tabOverviewReplacementTemplate" => $templateFile, "templateVariables" => array( "server_details" => $server_details, "ip_details" => $ip_details, "ip6_details" => $ip6_details, "ip6_reverse_details" => $ip6_reverse_details, "imgselect" => $imgselect, "SUBID" => $server_details["SUBID"], "used_bandwidth_percent" => $used_bandwidth_percent, "ns" => $ns, "domains" => $domains, "alert_error" => $alert_error ) );
    }

    $templateFile = "templates/error.tpl";
    return array( "tabOverviewReplacementTemplate" => $templateFile, "templateVariables" => array( "error" => "Licence Suspended." ) );
}

function vultr_AdminServicesTabFields($params)
{
    $serviceid = $params["serviceid"];
    $pid = $params["pid"];
    $server_id = vultr_getserverid($pid, $serviceid);
    $server_details = vultr_getserver($params, $server_id);
    $ip_details = vultr_getserverip($server_id);
    $ip6_details = vultr_getserverip6($server_id);
    $fieldsarray = array( "Specification" => "\r\n\t\t\t<table>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<th>Hostname: </th>\r\n\t\t\t\t\t<td>" . $server_details["label"] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<th>Memory: </th>\r\n\t\t\t\t\t<td>" . $server_details["ram"] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<th>vCPU: </th>\r\n\t\t\t\t\t<td>" . $server_details["vcpu_count"] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<th>Disk: </th>\r\n\t\t\t\t\t<td>" . $server_details["disk"] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t", "Information" => "\r\n\t\t\t<table>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<th>Power Status: </th>\r\n\t\t\t\t\t<td>" . $server_details["power_status"] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<th>Location: </th>\r\n\t\t\t\t\t<td>" . $server_details["location"] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<th>Operating System: </th>\r\n\t\t\t\t\t<td>" . $server_details["os"] . "</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<th>Default Password: </th>\r\n\t\t\t\t\t<td>" . $server_details["default_password"] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<th>Bandwidth: </th>\r\n\t\t\t\t\t<td>" . $server_details["current_bandwidth_gb"] . "Gb/" . $server_details["allowed_bandwidth_gb"] . "Gb</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t" );
    return $fieldsarray;
}

function vultr_reboot($params)
{
    $serviceid = $params["serviceid"];
    $pid = $params["pid"];
    $disable_ssl = vultr_getdisablessl();
    $apikey = vultr_getapikey();
    $result = select_query("tblcustomfields", "", array( "relid" => $pid, "fieldname" => "server_id" ));
    $data = mysql_fetch_array($result);
    $var1 = $data["id"];
    $result2 = select_query("tblcustomfieldsvalues", "", array( "relid" => $params["serviceid"], "fieldid" => $var1 ));
    $data2 = mysql_fetch_array($result2);
    $var2 = $data2["value"];
    sleep(1);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.vultr.com/v1/server/reboot?api_key=" . $apikey);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "SUBID=" . $var2);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if( $disable_ssl == "yes" ) 
    {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }

    $data = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $response = json_decode($data, true);
    if( $http_status == "200" ) 
    {
        $successful = true;
    }

    if( $successful ) 
    {
        $result = "success";
    }
    else
    {
        $result = "Could not reboot server (" . $http_status . " " . $data . ").";
    }

    return $result;
}

function vultr_start($params)
{
    $serviceid = $params["serviceid"];
    $pid = $params["pid"];
    $disable_ssl = vultr_getdisablessl();
    $apikey = vultr_getapikey();
    $result = select_query("tblcustomfields", "", array( "relid" => $pid, "fieldname" => "server_id" ));
    $data = mysql_fetch_array($result);
    $var1 = $data["id"];
    $result2 = select_query("tblcustomfieldsvalues", "", array( "relid" => $params["serviceid"], "fieldid" => $var1 ));
    $data2 = mysql_fetch_array($result2);
    $var2 = $data2["value"];
    sleep(1);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.vultr.com/v1/server/start?api_key=" . $apikey);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "SUBID=" . $var2);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if( $disable_ssl == "yes" ) 
    {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }

    $data = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $response = json_decode($data, true);
    if( $http_status == "200" ) 
    {
        $successful = true;
    }

    if( $successful ) 
    {
        $result = "success";
    }
    else
    {
        $result = "Could not power on server  (" . $http_status . " " . $data . ").";
    }

    return $result;
}

function vultr_halt($params)
{
    $serviceid = $params["serviceid"];
    $pid = $params["pid"];
    $disable_ssl = vultr_getdisablessl();
    $apikey = vultr_getapikey();
    $result = select_query("tblcustomfields", "", array( "relid" => $pid, "fieldname" => "server_id" ));
    $data = mysql_fetch_array($result);
    $var1 = $data["id"];
    $result2 = select_query("tblcustomfieldsvalues", "", array( "relid" => $params["serviceid"], "fieldid" => $var1 ));
    $data2 = mysql_fetch_array($result2);
    $var2 = $data2["value"];
    sleep(1);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.vultr.com/v1/server/halt?api_key=" . $apikey);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "SUBID=" . $var2);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if( $disable_ssl == "yes" ) 
    {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }

    $data = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $response = json_decode($data, true);
    if( $http_status == "200" ) 
    {
        $successful = true;
    }

    if( $successful ) 
    {
        $result = "success";
    }
    else
    {
        $result = "Could not power off server (" . $http_status . " " . $data . ").";
    }

    return $result;
}

function vultr_reinstall($params)
{
    $serviceid = $params["serviceid"];
    $pid = $params["pid"];
    $disable_ssl = vultr_getdisablessl();
    $apikey = vultr_getapikey();
    $result = select_query("tblcustomfields", "", array( "relid" => $pid, "fieldname" => "server_id" ));
    $data = mysql_fetch_array($result);
    $var1 = $data["id"];
    $result2 = select_query("tblcustomfieldsvalues", "", array( "relid" => $params["serviceid"], "fieldid" => $var1 ));
    $data2 = mysql_fetch_array($result2);
    $var2 = $data2["value"];
    sleep(1);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.vultr.com/v1/server/reinstall?api_key=" . $apikey);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "SUBID=" . $var2);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if( $disable_ssl == "yes" ) 
    {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }

    $data = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $response = json_decode($data, true);
    if( $http_status == "200" ) 
    {
        $successful = true;
    }

    if( $successful ) 
    {
        $command = "encryptpassword";
        $adminuser = 1;
        $values["password2"] = "See client area";
        $password = localAPI($command, $values, $adminuser);
        update_query("tblhosting", array( "username" => "root", "password" => $password["password"] ), array( "id" => $serviceid ));
        $result = "success";
    }
    else
    {
        $result = "Could not reinstall server (" . $http_status . " " . $data . ").";
    }

    return $result;
}

function vultr_adddomain($params, $domain, $ip_address)
{
    $serviceid = $params["serviceid"];
    $pid = $params["pid"];
    $disable_ssl = vultr_getdisablessl();
    $apikey = vultr_getapikey();
    sleep(1);
    if( $disable_ssl == "yes" ) 
    {
        $verifypeer = false;
    }
    else
    {
        $verifypeer = true;
    }

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "https://api.vultr.com/v1/dns/create_domain?api_key=" . $apikey);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, "domain=" . $domain . "&serverip=" . $ip_address);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $verifypeer);
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    logModuleCall("vultr", "adddomain (" . $serviceid . ")", array( $domain, $ip_address ), array( $httpCode, $response ), NULL, NULL);
    if( $httpCode == "200" ) 
    {
        insert_query("mod_vultr_domains", array( "service_id" => $serviceid, "domain" => $domain ));
        $result = "success";
    }
    else
    {
        $result = "Could not add domain Error: " . $httpCode . " " . $response;
    }

    return $result;
}

function vultr_deletedomain($params, $domain)
{
    $serviceid = $params["serviceid"];
    $pid = $params["pid"];
    $disable_ssl = vultr_getdisablessl();
    $apikey = vultr_getapikey();
    sleep(1);
    $url = "https://api.vultr.com/v1/dns/delete_domain?api_key=" . $apikey;
    if( $disable_ssl == "yes" ) 
    {
        $verifypeer = false;
    }
    else
    {
        $verifypeer = true;
    }

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, "domain=" . $domain);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $verifypeer);
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    logModuleCall("vultr", "deletedomain (" . $serviceid . ")", array( $domain ), array( $httpCode, $response ), NULL, NULL);
    if( $httpCode == "200" ) 
    {
        $query = "DELETE FROM `mod_vultr_domains` WHERE `service_id` = '" . $serviceid . "' AND `domain` = '" . mysql_real_escape_string($domain) . "'";
        full_query($query);
        $result = "success";
    }
    else
    {
        $result = "Could not delete domain Error: " . $httpCode . " " . $response;
    }

    return $result;
}

function vultr_adddomainrecord($params, $domain, $type, $name, $data, $priority)
{
    $serviceid = $params["serviceid"];
    $pid = $params["pid"];
    $disable_ssl = vultr_getdisablessl();
    $apikey = vultr_getapikey();
    if( $type == "MX" ) 
    {
        $postfields = "domain=" . $domain . "&name=" . $name . "&type=" . $type . "&data=" . $data . "&priority=" . $priority;
    }
    else
    {
        $postfields = "domain=" . $domain . "&name=" . $name . "&type=" . $type . "&data=" . $data;
    }

    sleep(1);
    if( $disable_ssl == "yes" ) 
    {
        $verifypeer = false;
    }
    else
    {
        $verifypeer = true;
    }

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "https://api.vultr.com/v1/dns/create_record?api_key=" . $apikey);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postfields);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $verifypeer);
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    logModuleCall("vultr", "adddomainrecord (" . $domain . ")", $postfields, array( $httpCode, $response ), NULL, NULL);
    if( $httpCode == "200" ) 
    {
        $result = "success";
    }
    else
    {
        $result = "Could not add domain record Error: " . $httpCode . " " . $response;
    }

    return $result;
}

function vultr_deletedomainrecord($params, $domain, $record_id)
{
    $serviceid = $params["serviceid"];
    $pid = $params["pid"];
    $disable_ssl = vultr_getdisablessl();
    $apikey = vultr_getapikey();
    sleep(1);
    $url = "https://api.vultr.com/v1/dns/delete_record?api_key=" . $apikey;
    if( $disable_ssl == "yes" ) 
    {
        $verifypeer = false;
    }
    else
    {
        $verifypeer = true;
    }

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, "domain=" . $domain . "&RECORDID=" . $record_id);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $verifypeer);
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    logModuleCall("vultr", "deletedomainrecord (" . $record_id . ")", array( $domain, $record_id ), array( $httpCode, $response ), NULL, NULL);
    if( $httpCode == "200" ) 
    {
        $result = "success";
    }
    else
    {
        $result = "Could not delete domain record Error: " . $httpCode . " " . $response;
    }

    return $result;
}

function vultr_rename($params, $hostname)
{
    $serviceid = $params["serviceid"];
    $pid = $params["pid"];
    $disable_ssl = vultr_getdisablessl();
    $apikey = vultr_getapikey();
    $server_id = vultr_getserverid($pid, $serviceid);
    $server_details = vultr_getserver($params, $server_id);
    $ip_details = vultr_getserverip($server_id);
    $ip6_details = vultr_getserverip6($server_id);
    $ip6_reverse_details = vultr_getserverip6reverse($server_id);
    $SUBID = $server_details["SUBID"];
    $error = "";
    if( $ip["type"] != "private" ) 
    {
        if( $disable_ssl == "yes" ) 
        {
            $verifypeer = false;
        }
        else
        {
            $verifypeer = true;
        }

        sleep(1);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://api.vultr.com/v1/server/reverse_set_ipv4?api_key=" . $apikey);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, "SUBID=" . $server_id . "&ip=" . $ip["ip"] . "&entry=" . $hostname);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $verifypeer);
        curl_exec($curl);
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        logModuleCall("vultr", "updaterdns (" . $server_id . ")", array( "SUBID" => $server_id, "ip" => $ip["ip"], "entry" => $hostname ), array( $httpCode, $response ), NULL, NULL);
        if( $httpCode != "200" ) 
        {
            $error .= "Could not update rDNS for " . $ip["ip"] . "  Error: " . $httpCode . " " . $response;
        }

    }

    sleep(1);
    if( $disable_ssl == "yes" ) 
    {
        $verifypeer = false;
    }
    else
    {
        $verifypeer = true;
    }

    sleep(1);
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "https://api.vultr.com/v1/server/reverse_set_ipv6?api_key=" . $apikey);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, "SUBID=" . $server_id . "&ip=" . $ip["ip"] . "&entry=" . $hostname);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $verifypeer);
    curl_exec($curl);
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    logModuleCall("vultr", "updaterdns (" . $server_id . ")", array( "SUBID" => $server_id, "ip" => $ip["ip"], "entry" => $hostname ), array( $httpCode, $response ), NULL, NULL);
    if( $httpCode != "200" ) 
    {
        $error .= "Could not update rDNS for " . $ip["ip"] . "  Error: " . $httpCode . " " . $response;
    }

    sleep(1);
    if( $disable_ssl == "yes" ) 
    {
        $verifypeer = false;
    }
    else
    {
        $verifypeer = true;
    }

    sleep(1);
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "https://api.vultr.com/v1/server/reverse_set_ipv6?api_key=" . $apikey);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, "SUBID=" . $server_id . "&ip=" . $ip["ip"] . "&entry=" . $hostname);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $verifypeer);
    curl_exec($curl);
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    logModuleCall("vultr", "updaterdns (" . $server_id . ")", array( "SUBID" => $server_id, "ip" => $ip["ip"], "entry" => $hostname ), array( $httpCode, $response ), NULL, NULL);
    if( $httpCode != "200" ) 
    {
        $error .= "Could not update rDNS for " . $ip["ip"] . "  Error: " . $httpCode . " " . $response;
    }

    if( $disable_ssl == "yes" ) 
    {
        $verifypeer = false;
    }
    else
    {
        $verifypeer = true;
    }

    sleep(1);
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "https://api.vultr.com/v1/server/label_set?api_key=" . $apikey);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, "SUBID=" . $server_id . "&label=" . $hostname);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $verifypeer);
    curl_exec($curl);
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    logModuleCall("vultr", "updatelabel (" . $server_id . ")", array( "SUBID" => $server_id, "ip" => $ip["ip"], "entry" => $hostname ), array( $httpCode, $response ), NULL, NULL);
    if( $httpCode != "200" ) 
    {
        $error .= "Could not update label  Error: " . $httpCode . " " . $response;
    }

    if( empty($error) ) 
    {
        update_query("tblhosting", array( "domain" => $hostname ), array( "id" => $serviceid ));
        return "success";
    }

    return $error;
}

function vultr_AdminCustomButtonArray()
{
    $buttonarray = array( "Reboot" => "reboot", "Start" => "start", "Halt" => "halt" );
    return $buttonarray;
}

function vultr_check_license($licensekey, $localkey = "")
{
    $disable_ssl = vultr_getdisablessl();
    $result = select_query("tbladdonmodules", "", array( "module" => "vultr", "setting" => "version" ));
    $data = mysql_fetch_array($result);
    $current_version = $data["value"];
    $apikey = vultr_getapikey();
    $whmcsurl = "https://my.cnuk.co/";
    $licensing_secret_key = "vu1tr";
    $check_token = time() . md5(mt_rand(1000000000, 9999999999) . $licensekey);
    $checkdate = date("Ymd");
    $usersip = (isset($_SERVER["SERVER_ADDR"]) ? $_SERVER["SERVER_ADDR"] : $_SERVER["LOCAL_ADDR"]);
    $localkeydays = 15;
    $allowcheckfaildays = 5;
    $localkeyvalid = false;
    if( $localkey ) 
    {
        $localkey = str_replace("\n", "", $localkey);
        $localdata = substr($localkey, 0, strlen($localkey) - 32);
        $md5hash = substr($localkey, strlen($localkey) - 32);
        if( $md5hash == md5($localdata . $licensing_secret_key) ) 
        {
            $localdata = strrev($localdata);
            $md5hash = substr($localdata, 0, 32);
            $localdata = substr($localdata, 32);
            $localdata = base64_decode($localdata);
            $localkeyresults = unserialize($localdata);
            $originalcheckdate = $localkeyresults["checkdate"];
            if( $md5hash == md5($originalcheckdate . $licensing_secret_key) ) 
            {
                $localexpiry = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - $localkeydays, date("Y")));
                if( $localexpiry < $originalcheckdate ) 
                {
                    $localkeyvalid = true;
                    $results = $localkeyresults;
                    $validdomains = explode(",", $results["validdomain"]);
                    if( !in_array($_SERVER["SERVER_NAME"], $validdomains) ) 
                    {
                        $localkeyvalid = false;
                        $localkeyresults["status"] = "Invalid";
                        $results = array(  );
                    }

                    $validips = explode(",", $results["validip"]);
                    if( !in_array($usersip, $validips) ) 
                    {
                        $localkeyvalid = false;
                        $localkeyresults["status"] = "Invalid";
                        $results = array(  );
                    }

                    if( $results["validdirectory"] != dirname(__FILE__) ) 
                    {
                        $localkeyvalid = false;
                        $localkeyresults["status"] = "Invalid";
                        $results = array(  );
                    }

                }

            }

        }

    }

    if( !$localkeyvalid ) 
    {
        $postfields["licensekey"] = $licensekey;
        $postfields["domain"] = $_SERVER["SERVER_NAME"];
        $postfields["ip"] = $usersip;
        $postfields["dir"] = dirname(__FILE__);
        if( $check_token ) 
        {
            $postfields["check_token"] = $check_token;
        }

        if( function_exists("curl_exec") ) 
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $whmcsurl . "modules/servers/licensing/verify.php");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            if( $disable_ssl == "yes" ) 
            {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            }

            $data = curl_exec($ch);
            if( curl_errno($ch) ) 
            {
                $curl_error = "<span style=\"color:red; font-weight:bold;\">" . curl_error($ch) . "</span>";
            }
            else
            {
                $curl_error = "None";
            }

            curl_close($ch);
        }
        else
        {
            $fp = fsockopen($whmcsurl, 80, $errno, $errstr, 5);
            if( $fp ) 
            {
                $querystring = "";
                $querystring .= (string) $k . "=" . urlencode($v) . "&";
                $header = "POST " . $whmcsurl . "modules/servers/licensing/verify.php HTTP/1.0\r\n";
                $header .= "Host: " . $whmcsurl . "\r\n";
                $header .= "Content-type: application/x-www-form-urlencoded\r\n";
                $header .= "Content-length: " . @strlen($querystring) . "\r\n";
                $header .= "Connection: close\r\n\r\n";
                $header .= $querystring;
                $data = "";
                @stream_set_timeout($fp, 20);
                @fputs($fp, $header);
                $status = @socket_get_status($fp);
                while( !@feof($fp) && $status ) 
                {
                    $data .= @fgets($fp, 1024);
                    $status = @socket_get_status($fp);
                }
                @fclose($fp);
            }

        }

        if( !$data ) 
        {
            $localexpiry = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - ($localkeydays + $allowcheckfaildays), date("Y")));
            if( $localexpiry < $originalcheckdate ) 
            {
                $results = $localkeyresults;
            }
            else
            {
                $results["status"] = "Invalid";
                $results["description"] = "Remote Check Failed";
                $results["curl_error"] = $curl_error;
                return $results;
            }

        }
        else
        {
            preg_match_all("/<(.*?)>([^<]+)<\\/\\1>/i", $data, $matches);
            $results = array(  );
            $results[$v] = $matches[2][$k];
        }

        if( $results["md5hash"] && $results["md5hash"] != md5($licensing_secret_key . $check_token) ) 
        {
            $results["status"] = "Invalid";
            $results["description"] = "MD5 Checksum Verification Failed";
            $results["curl_error"] = $curl_error;
            return $results;
        }

        if( $results["status"] == "Active" ) 
        {
            $results["checkdate"] = $checkdate;
            $data_encoded = serialize($results);
            $data_encoded = base64_encode($data_encoded);
            $data_encoded = md5($checkdate . $licensing_secret_key) . $data_encoded;
            $data_encoded = strrev($data_encoded);
            $data_encoded = $data_encoded . md5($data_encoded . $licensing_secret_key);
            $data_encoded = wordwrap($data_encoded, 80, "\n", true);
            $results["localkey"] = $data_encoded;
        }

        $results["remotecheck"] = true;
    }

    if( $results["status"] == "Active" ) 
    {
        sleep(1);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.vultr.com/v1/server/list?api_key=" . $apikey);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if( $disable_ssl == "yes" ) 
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        $data = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($data, true);
        $i = 0;
        $i++;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://my.cnuk.co/versions/vultr.php?key=" . $licensekey . "&version=" . $current_version . "&servers=" . $i);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if( $disable_ssl == "yes" ) 
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        $data = curl_exec($ch);
        curl_close($ch);
    }

    unset($postfields);
    unset($data);
    unset($matches);
    unset($whmcsurl);
    unset($licensing_secret_key);
    unset($checkdate);
    unset($usersip);
    unset($localkeydays);
    unset($allowcheckfaildays);
    unset($md5hash);
    $results["curl_error"] = $curl_error;
    return $results;
}



<?php 
if( !defined("WHMCS") ) 
{
    exit( "This file cannot be accessed directly" );
}

function vultr_config()
{
    $configarray = array( "name" => "Vultr", "description" => "Module for provisioning Vultr servers", "version" => "1.0.3", "author" => "<a href=\"http://www.hypnotic-monkey.com\" target=\"_blank\">Hypnotic Monkey</a>", "language" => "english", "fields" => array( "vultr_apikey" => array( "FriendlyName" => "API Key", "Type" => "text", "Size" => "25", "Description" => "Vultr API Key" ), "vultr_licence" => array( "FriendlyName" => "Licence Key", "Type" => "text", "Size" => "25", "Description" => "Licence key provided by CNUK" ) ) );
    return $configarray;
}

function vultr_activate()
{
    $query = "CREATE TABLE IF NOT EXISTS `mod_vultr_settings` (\r\n\t\t\t  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n\t\t\t  `setting` varchar(255) NOT NULL,\r\n\t\t\t  `value` varchar(255) NOT NULL,\r\n\t\t\t  PRIMARY KEY (`id`)\r\n\t\t\t) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2\r\n\t\t\t";
    $result = full_query($query);
    $query = "INSERT INTO `mod_vultr_settings` (`id`, `setting`, `value`) VALUES (1, 'localkey', '')";
    $result = full_query($query);
    $query = "INSERT INTO `mod_vultr_settings` (`id`, `setting`, `value`) VALUES (2, 'disable_ssl', 'no')";
    $result = full_query($query);
    $query = "INSERT INTO `mod_vultr_settings` (`id`, `setting`, `value`) VALUES (3, 'host_domain', '')";
    $result = full_query($query);
    $query = "INSERT INTO `mod_vultr_settings` (`id`, `setting`, `value`) VALUES (4, 'ns1', 'ns1.vultr.com')";
    $result = full_query($query);
    $query = "INSERT INTO `mod_vultr_settings` (`id`, `setting`, `value`) VALUES (5, 'ns2', 'ns2.vultr.com')";
    $result = full_query($query);
    $query = "CREATE TABLE IF NOT EXISTS `mod_vultr_domains` (\r\n\t\t\t  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n\t\t\t  `service_id` int(11) NOT NULL,\r\n\t\t\t  `domain` varchar(255) NOT NULL,\r\n\t\t\t  PRIMARY KEY (`id`)\r\n\t\t\t) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2\r\n\t\t\t";
    $result = full_query($query);
    return array( "status" => "success", "description" => "Vultr has been setup properly. " );
}

function vultr_deactivate()
{
    $query = "DROP TABLE `mod_vultr_settings`;";
    $result = full_query($query);
    $query = "DROP TABLE `mod_vultr_domains`;";
    $result = full_query($query);
    return array( "status" => "success", "description" => "Vultr has been removed properly. " );
}

function vultr_upgrade($vars)
{
    $version = $vars["version"];
    if( version_compare($version, "1.0.0", "<") ) 
    {
        $query = "INSERT INTO `mod_vultr_settings` (`id`, `setting`, `value`) VALUES (3, 'host_domain', '')";
        $result = full_query($query);
        $query = "INSERT INTO `mod_vultr_settings` (`id`, `setting`, `value`) VALUES (4, 'ns1', 'ns1.vultr.com')";
        $result = full_query($query);
        $query = "INSERT INTO `mod_vultr_settings` (`id`, `setting`, `value`) VALUES (5, 'ns2', 'ns2.vultr.com')";
        $result = full_query($query);
        $query = "CREATE TABLE IF NOT EXISTS `mod_vultr_domains` (\r\n\t\t\t  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n\t\t\t  `service_id` int(11) NOT NULL,\r\n\t\t\t  `domain` varchar(255) NOT NULL,\r\n\t\t\t  PRIMARY KEY (`id`)\r\n\t\t\t) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2\r\n\t\t\t";
        $result = full_query($query);
    }

}

function vultr_output($vars)
{
    $modulelink = $vars["modulelink"];
    $version = $vars["version"];
    $apikey = $vars["vultr_apikey"];
    $licence = $vars["vultr_licence"];
    $currentversion = file_get_contents("https://my.cnuk.co/versions/vultr.txt");
    $requiredcurl = file_get_contents("https://my.cnuk.co/versions/vultr_curl.txt");
    $result = select_query("mod_vultr_settings", "", array( "setting" => "disable_ssl" ));
    $data = mysql_fetch_array($result);
    $disable_ssl = $data["value"];
    $usersip = (isset($_SERVER["SERVER_ADDR"]) ? $_SERVER["SERVER_ADDR"] : $_SERVER["LOCAL_ADDR"]);
    $domain = $_SERVER["SERVER_NAME"];
    $dir = dirname(__FILE__);
    if( function_exists("curl_exec") ) 
    {
        $curl_check = "<span>Yes</span>";
    }
    else
    {
        $curl_check = "<span style=\"color:red; font-weight:bold;\">No</span>";
    }

    $curl_version = curl_version();
    if( $requiredcurl <= $curl_version["version"] ) 
    {
        $curl_colour = "<span style=\"color:green;\">" . $curl_version["version"] . "</span>";
    }
    else
    {
        $curl_colour = "<span style=\"color:red; font-weight:bold;\">" . $curl_version["version"] . "</span> (requires " . $requiredcurl . ")";
    }

    $time = ini_get("max_execution_time");
    if( 120 <= $time ) 
    {
        $time_colour = "<span style=\"color:green;\">" . $time . "</span>";
    }
    else
    {
        $time_colour = "<span style=\"color:red; font-weight:bold;\">" . $time . "</span> (please set max_execution_time to a minimum of 120 seconds in php.ini)";
    }

    if( $currentversion == $version ) 
    {
        $version_colour = "<span style=\"color:green;\">" . $version . "</span>";
    }
    else
    {
        $version_colour = "<span style=\"color:red; font-weight:bold;\">" . $version . "</span>";
    }

    $result = select_query("mod_vultr_settings", "", array( "setting" => "localkey" ));
    $data = mysql_fetch_array($result);
    $localkey = $data["value"];
    $check_license = vultr_check_license($licence, $localkey);
    if( $check_license["status"] == "Active" ) 
    {
        update_query("mod_vultr_settings", array( "value" => $check_license["localkey"] ), array( "setting" => "localkey" ));
        $licence_status = "<span style=\"color:green;\">Active</span>";
    }
    else
    {
        $licence_status = "<span style=\"color:red;\">" . $check_license["status"] . " " . $check_license["description"] . "</span>";
    }

    echo "<ul class=\"nav nav-tabs client-tabs\" role=\"tablist\">\r\n\t\t\t\t<li class=\"tab " . (($_GET["tab"] == "" ? "active" : "")) . "\"><a href=\"" . $modulelink . "\">Module Info</a></li>\r\n\t\t\t\t<li class=\"tab " . (($_GET["tab"] == "plans" ? "active" : "")) . "\"><a href=\"" . $modulelink . "&amp;tab=plans\">Plans</a></li>\r\n\t\t\t\t<li class=\"tab " . (($_GET["tab"] == "regions" ? "active" : "")) . "\"><a href=\"" . $modulelink . "&amp;tab=regions\">Regions</a></li>\r\n\t\t\t\t<li class=\"tab " . (($_GET["tab"] == "images" ? "active" : "")) . "\"><a href=\"" . $modulelink . "&amp;tab=images\">Images</a></li>\r\n\t\t\t\t<li class=\"tab " . (($_GET["tab"] == "availability" ? "active" : "")) . "\"><a href=\"" . $modulelink . "&amp;tab=availability\">Availability</a></li>\r\n\t\t\t\t<li class=\"tab " . (($_GET["tab"] == "servers" ? "active" : "")) . "\"><a href=\"" . $modulelink . "&amp;tab=servers\">Servers</a></li>\r\n\t\t\t\t<li class=\"tab " . (($_GET["tab"] == "scripts" ? "active" : "")) . "\"><a href=\"" . $modulelink . "&amp;tab=scripts\">Startup Scripts</a></li>\r\n\t\t\t</ul>";
    switch( $_GET["tab"] ) 
    {
        case "plans":
            if( $check_license["status"] == "Active" ) 
            {
                echo "<div style=\"text-align: left;background-color: whiteSmoke;margin: 0px;padding: 10px;\">\r\n\t\t\t\t\t\t<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">\r\n\t\t\t\t\t\t\t<tbody>";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://api.vultr.com/v1/plans/list");
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                if( $disable_ssl == "yes" ) 
                {
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                }

                $data = curl_exec($ch);
                curl_close($ch);
                $response = json_decode($data, true);
                echo "<tr><td class=\"fieldlabel\" width=\"30%\"><strong>" . $plan["name"] . " (\$" . $plan["price_per_month"] . "/mo)</strong></td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"fieldarea\">" . $id . "</td></tr>";
                echo "\r\n\t\t\t\t\t\t\t</tbody>\r\n\t\t\t\t\t\t</table>\r\n\t\t\t\t\t</div>";
            }
            else
            {
                echo "Please activate your licence!";
            }

            break;
        case "regions":
            if( $check_license["status"] == "Active" ) 
            {
                echo "<p>You can copy and paste these into your product configuration.</p>\r\n\t\t\t\t\t<div class=\"contentbox\">\r\n\t\t\t\t\t\t<table width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">\r\n\t\t\t\t\t\t\t<tbody>";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://api.vultr.com/v1/regions/list");
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                if( $disable_ssl == "yes" ) 
                {
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                }

                $data = curl_exec($ch);
                curl_close($ch);
                $response = json_decode($data, true);
                echo "<tr><td>" . $id . "|" . $region["name"] . " (" . $region["country"] . ", " . $region["continent"] . ")</td></tr>";
                echo "\r\n\t\t\t\t\t\t\t</tbody>\r\n\t\t\t\t\t\t</table>\r\n\t\t\t\t\t</div>";
            }
            else
            {
                echo "Please activate your licence!";
            }

            break;
        case "images":
            if( $check_license["status"] == "Active" ) 
            {
                echo "<p>You can copy and paste these into your product configuration.</p>\r\n\t\t\t\t\t<div class=\"contentbox\">\r\n\t\t\t\t\t\t<table width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">\r\n\t\t\t\t\t\t\t<tbody>";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://api.vultr.com/v1/os/list");
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                if( $disable_ssl == "yes" ) 
                {
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                }

                $data = curl_exec($ch);
                curl_close($ch);
                $response = json_decode($data, true);
                echo "<tr><td>" . $id . "|" . $image["name"] . "</td></tr>";
                echo "\r\n\t\t\t\t\t\t\t</tbody>\r\n\t\t\t\t\t\t</table>\r\n\t\t\t\t\t</div>";
            }
            else
            {
                echo "Please activate your licence!";
            }

            break;
        case "availability":
            if( $check_license["status"] == "Active" ) 
            {
                echo "<div style=\"text-align: left;background-color: whiteSmoke;margin: 0px;padding: 10px;\">\r\n\t\t\t\t\t\t<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">\r\n\t\t\t\t\t\t\t<tbody>";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://api.vultr.com/v1/regions/list");
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                if( $disable_ssl == "yes" ) 
                {
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                }

                $data = curl_exec($ch);
                curl_close($ch);
                $regions = json_decode($data, true);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://api.vultr.com/v1/plans/list");
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                if( $disable_ssl == "yes" ) 
                {
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                }

                $data = curl_exec($ch);
                curl_close($ch);
                $plans = json_decode($data, true);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://api.vultr.com/v1/regions/availability?DCID=" . $region_id);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                if( $disable_ssl == "yes" ) 
                {
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                }

                $data = curl_exec($ch);
                curl_close($ch);
                $available_plans = json_decode($data, true);
                echo "<tr>\r\n\t\t\t\t\t\t\t<td class=\"fieldlabel\" width=\"30%\"><strong>" . $region_id . "|" . $region["name"] . "</strong></td>\r\n\t\t\t\t\t\t\t<td class=\"fieldarea\"></td>\r\n\t\t\t\t\t\t\t</tr>";
                if( $plans[$available_plan]["windows"] == true ) 
                {
                    $windows = " - supports windows server";
                }
                else
                {
                    $windows = "";
                }

                echo "<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldlabel\" width=\"30%\"><strong></strong></td>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldarea\">" . $available_plan . " | " . $plans[$available_plan]["name"] . " (\$" . $plans[$available_plan]["price_per_month"] . "/mo)" . $windows . "</td>\r\n\t\t\t\t\t\t\t\t</tr>";
                echo "\r\n\t\t\t\t\t\t\t</tbody>\r\n\t\t\t\t\t\t</table>\r\n\t\t\t\t\t</div>";
            }
            else
            {
                echo "Please activate your licence!";
            }

            break;
        case "servers":
            if( $check_license["status"] == "Active" ) 
            {
                echo "<div style=\"text-align: left;background-color: whiteSmoke;margin: 0px;padding: 10px;\">\r\n\t\t\t\t\t\t<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">\r\n\t\t\t\t\t\t\t<tbody>";
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
                if( !empty($response) ) 
                {
                    echo "<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldlabel\" width=\"30%\"><strong>" . $id . "</strong></td>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldarea\">" . $server["main_ip"] . "</td>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldarea\">" . $server["location"] . "</td>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldarea\">\$" . $server["cost_per_month"] . "/mo</td>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldarea\">" . $server["status"] . "</td>\r\n\t\t\t\t\t\t\t\t</tr>";
                }
                else
                {
                    echo "<tr><td align=\"center\">No servers found..</td></tr>";
                }

                echo "\r\n\t\t\t\t\t\t\t</tbody>\r\n\t\t\t\t\t\t</table>\r\n\t\t\t\t\t</div>";
            }
            else
            {
                echo "Please activate your licence!";
            }

            break;
        case "scripts":
            if( $check_license["status"] == "Active" ) 
            {
                if( $_POST["action"] == "deletescript" ) 
                {
                    $url = "https://api.vultr.com/v1/startupscript/destroy?api_key=" . $apikey;
                    $postfields = array( "SCRIPTID" => $_POST["SCRIPTID"] );
                    if( $disable_ssl == "yes" ) 
                    {
                        $options = array( "CURLOPT_SSL_VERIFYPEER" => false );
                    }

                    $data = curlCall($url, $postfields, $options);
                    $response = json_decode($data, true);
                    echo "<div align=\"center\"><p style=\"color:green;\">Script " . $_POST["SCRIPTID"] . " deleted.</p></div>";
                }

                echo "\r\n\t\t\t\t<form action=\"https://my.vultr.com/startup/manage.php?SCRIPTID=new\" method=\"get\" target=\"_blank\">\r\n\t\t\t\t\t<p align=\"center\"><input type=\"submit\" value=\"Create new startup script\" class=\"button\"></p>\r\n\t\t\t\t</form>\r\n\t\t\t\t<div style=\"text-align: left;background-color: whiteSmoke;margin: 0px;padding: 10px;\">\r\n\t\t\t\t\t\t<h4>Current Scripts</h4>\r\n\t\t\t\t\t\t<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">\r\n\t\t\t\t\t\t\t<tbody>";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://api.vultr.com/v1/startupscript/list?api_key=" . $apikey);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                if( $disable_ssl == "yes" ) 
                {
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                }

                $data = curl_exec($ch);
                curl_close($ch);
                $response = json_decode($data, true);
                if( !empty($response) ) 
                {
                    echo "<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldlabel\" width=\"30%\"><strong>" . $script["SCRIPTID"] . "</strong></td>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldarea\">" . $script["name"] . "</td>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldarea\"><textarea disabled=\"disabled\" rows=\"5\" cols=\"100\">" . $script["script"] . "</textarea></td>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldarea\"><small>\r\n\t\t\t\t\t\t\t\t<form action=\"\" method=\"post\">\r\n\t\t\t\t\t\t\t\t\t<input type=\"hidden\" name=\"action\" value=\"deletescript\">\r\n\t\t\t\t\t\t\t\t\t<input type=\"hidden\" name=\"SCRIPTID\" value=\"" . $script["SCRIPTID"] . "\">\r\n\t\t\t\t\t\t\t\t\t<input type=\"submit\" value=\"Delete\" class=\"button\">\r\n\t\t\t\t\t\t\t\t</form>\r\n\t\t\t\t\t\t\t\t</small></td>\r\n\t\t\t\t\t\t\t\t</tr>";
                }
                else
                {
                    echo "<tr><td align=\"center\">No scripts found..</td></tr>";
                }

                echo "\r\n\t\t\t\t\t\t\t</tbody>\r\n\t\t\t\t\t\t</table>\r\n\t\t\t\t\t</div>";
            }
            else
            {
                echo "Please activate your licence!";
            }

            break;
        default:
            echo "<div align=\"center\"><p>\r\n\t\t\t\t<h3>Create configurable options group(s):</h3>\r\n\t\t\t\t<form action=\"\" method=\"post\">\r\n\t\t\t\t\t<select name=\"grouptype\">\r\n\t\t\t\t\t\t<option value=\"nonwindows\">Non-Windows</option>\r\n\t\t\t\t\t\t<option value=\"windows\">Windows</option>\r\n\t\t\t\t\t</select>\r\n\t\t\t\t\t<input type=\"hidden\" name=\"action\" value=\"creategroup\">\r\n\t\t\t\t\t<input type=\"submit\" value=\"Create new option group\" class=\"button\">\r\n\t\t\t\t</form>\r\n\t\t\t</p></div>";
            if( $_POST["action"] == "creategroup" ) 
            {
                if( $_POST["grouptype"] == "windows" ) 
                {
                    $groupvalues = array( "name" => "Vultr (" . date("Y-m-d H:i:s") . ")", "description" => "Group created by Vultr module (Windows)" );
                }
                else
                {
                    $groupvalues = array( "name" => "Vultr (" . date("Y-m-d H:i:s") . ")", "description" => "Group created by Vultr module (Non-Windows)" );
                }

                $newgroupid = insert_query("tblproductconfiggroups", $groupvalues);
                $dcvalues = array( "gid" => $newgroupid, "optionname" => "Datacenter", "optiontype" => "1", "qtyminimum" => "0", "qtymaximum" => "0", "order" => "0", "hidden" => "0" );
                $dcid = insert_query("tblproductconfigoptions", $dcvalues);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://api.vultr.com/v1/regions/list");
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                if( $disable_ssl == "yes" ) 
                {
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                }

                $data = curl_exec($ch);
                curl_close($ch);
                $response = json_decode($data, true);
                $rgn_i = 0;
                $rgn_insert = insert_query("tblproductconfigoptionssub", array( "configid" => $dcid, "optionname" => $id . "|" . $region["name"] . " (" . $region["country"] . ", " . $region["continent"] . ")", "sortorder" => $rgn_i, "hidden" => "0" ));
                $rgn_i++;
                $osvalues = array( "gid" => $newgroupid, "optionname" => "Operating System", "optiontype" => "1", "qtyminimum" => "0", "qtymaximum" => "0", "order" => "1", "hidden" => "0" );
                $osid = insert_query("tblproductconfigoptions", $osvalues);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://api.vultr.com/v1/os/list");
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                if( $disable_ssl == "yes" ) 
                {
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                }

                $data = curl_exec($ch);
                curl_close($ch);
                $response = json_decode($data, true);
                $img_i = 0;
                if( $_POST["grouptype"] == "windows" ) 
                {
                    if( $image["windows"] == true ) 
                    {
                        $img_insert = insert_query("tblproductconfigoptionssub", array( "configid" => $osid, "optionname" => $id . "|" . $image["name"], "sortorder" => $img_i, "hidden" => "0" ));
                    }

                }
                else
                {
                    if( $image["windows"] == false ) 
                    {
                        $img_insert = insert_query("tblproductconfigoptionssub", array( "configid" => $osid, "optionname" => $id . "|" . $image["name"], "sortorder" => $img_i, "hidden" => "0" ));
                    }

                }

                $img_i++;
                if( $_POST["grouptype"] != "windows" ) 
                {
                    $appvalues = array( "gid" => $newgroupid, "optionname" => "Application", "optiontype" => "1", "qtyminimum" => "0", "qtymaximum" => "0", "order" => "2", "hidden" => "0" );
                    $appid = insert_query("tblproductconfigoptions", $appvalues);
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "https://api.vultr.com/v1/app/list");
                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    if( $disable_ssl == "yes" ) 
                    {
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    }

                    $data = curl_exec($ch);
                    curl_close($ch);
                    $response = json_decode($data, true);
                    $app_i = 1;
                    insert_query("tblproductconfigoptionssub", array( "configid" => $appid, "optionname" => 0 . "| --select application under operating system first--", "sortorder" => "0", "hidden" => "0" ));
                    insert_query("tblproductconfigoptionssub", array( "configid" => $appid, "optionname" => $application["APPID"] . "|" . $application["deploy_name"], "sortorder" => $app_i, "hidden" => "0" ));
                    $app_i++;
                }

                echo "<div align=\"center\"><p style=\"color:green;\">Option group '" . $groupvalues["name"] . "' created.</p></div>";
            }

            echo "<h4>Module Info</h4>\r\n\t\t   \t\t<div style=\"text-align: left;background-color: whiteSmoke;margin: 0px;padding: 10px;\">\r\n\t\t\t\t\t<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">\r\n\t\t\t\t\t\t<tbody>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldlabel\" width=\"30%\"><strong>API Key</strong></td>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldarea\">" . $apikey . "</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldlabel\" width=\"30%\"><strong>Licence Key</strong></td>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldarea\">" . $licence . "</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldlabel\" width=\"30%\"><strong>Licence Status</strong></td>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldarea\">" . $licence_status . "</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t</tbody>\r\n\t\t\t\t\t</table>\r\n\t\t\t\t</div>";
            echo "<h4>Version Check</h4>\r\n\t\t\t\t<div style=\"text-align: left;background-color: whiteSmoke;margin: 0px;padding: 10px;\">\r\n\t\t\t\t\t<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">\r\n\t\t\t\t\t\t<tbody>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldlabel\" width=\"30%\"><strong>Your Version</strong></td>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldarea\">" . $version_colour . "</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldlabel\" width=\"30%\"><strong>Current Version</strong></td>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldarea\">" . $currentversion . "</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t</tbody>\r\n\t\t\t\t\t</table>\r\n\t\t\t\t</div>";
            echo "<h4>Server Check</h4>\r\n\t\t\t\t<div style=\"text-align: left;background-color: whiteSmoke;margin: 0px;padding: 10px;\">\r\n\t\t\t\t\t<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">\r\n\t\t\t\t\t\t<tbody>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldlabel\" width=\"30%\"><strong>cURL Enabled</strong></td>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldarea\">" . $curl_check . "</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldlabel\" width=\"30%\"><strong>cURL Version</strong></td>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldarea\">" . $curl_colour . "</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldlabel\" width=\"30%\"><strong>Max Execution Time</strong></td>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldarea\">" . $time_colour . "</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldlabel\" width=\"30%\"><strong>IP</strong></td>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldarea\">" . $usersip . "</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldlabel\" width=\"30%\"><strong>Domain</strong></td>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldarea\">" . $domain . "</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldlabel\" width=\"30%\"><strong>Dir</strong></td>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldarea\">" . $dir . "</td>\r\n\t\t\t\t\t\t\t</tr>";
            if( isset($check_license["curl_error"]) && $check_license["curl_error"] != "None" ) 
            {
                echo "<tr>\r\n\t\t\t\t\t\t\t\t\t\t<td class=\"fieldlabel\" width=\"30%\"><strong>cURL Error</strong></td>\r\n\t\t\t\t\t\t\t\t\t\t<td class=\"fieldarea\">" . $check_license["curl_error"] . "</td>\r\n\t\t\t\t\t\t\t\t\t</tr>";
            }

            echo "</tbody>\r\n\t\t\t\t\t</table>\r\n\t\t\t\t</div>";
            if( $_POST["action"] == "save" ) 
            {
                update_query("mod_vultr_settings", array( "value" => $_POST["disable_ssl"] ), array( "setting" => "disable_ssl" ));
                update_query("mod_vultr_settings", array( "value" => $_POST["host_domain"] ), array( "setting" => "host_domain" ));
                update_query("mod_vultr_settings", array( "value" => $_POST["ns1"] ), array( "setting" => "ns1" ));
                update_query("mod_vultr_settings", array( "value" => $_POST["ns2"] ), array( "setting" => "ns2" ));
                header("Location: addonmodules.php?module=vultr");
            }

            $result = select_query("mod_vultr_settings", "", array( "setting" => "disable_ssl" ));
            $data = mysql_fetch_array($result);
            $disable_ssl = $data["value"];
            $result = select_query("mod_vultr_settings", "", array( "setting" => "host_domain" ));
            $data = mysql_fetch_array($result);
            $host_domain = $data["value"];
            $result = select_query("mod_vultr_settings", "", array( "setting" => "ns1" ));
            $data = mysql_fetch_array($result);
            $ns1 = $data["value"];
            $result = select_query("mod_vultr_settings", "", array( "setting" => "ns2" ));
            $data = mysql_fetch_array($result);
            $ns2 = $data["value"];
            echo "<h4>Settings</h4>\r\n\t\t\t\t<form action=\"\" method=\"post\">\r\n\t\t\t\t<input type=\"hidden\" name=\"action\" value=\"save\">\r\n\t\t\t\t<div style=\"text-align: left;background-color: whiteSmoke;margin: 0px;padding: 10px;\">\r\n\t\t\t\t\t<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">\r\n\t\t\t\t\t\t<tbody>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldlabel\" width=\"30%\"><strong>Disable SSL</strong></td>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldarea\"><label><input type=\"checkbox\" name=\"disable_ssl\" value=\"yes\"";
            if( $disable_ssl == "yes" ) 
            {
                echo "checked=\"checked\"";
            }

            echo "> If you receive cURL SSL error tick this box. Before enabling make sure ca-certificates are installed e.g. yum install ca-certificates</label></td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldlabel\" width=\"30%\"><strong>Host Domain</strong></td>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldarea\"><label><input type=\"text\" name=\"host_domain\" value=\"" . $host_domain . "\"> If no hostname is set, an automatic name of srvID.host_domain will be set. e.g. srv102451.yourdomain.com</label></td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldlabel\" width=\"30%\"><strong>Namserver 1</strong></td>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldarea\"><label><input type=\"text\" name=\"ns1\" value=\"" . $ns1 . "\"> The nameserver that will be provided to your client, please create an A record e.g. ns1.yourdomain.com and point to " . gethostbyname("ns1.vultr.com") . " (ns1.vultr.com)</label></td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldlabel\" width=\"30%\"><strong>Namserver 1</strong></td>\r\n\t\t\t\t\t\t\t\t<td class=\"fieldarea\"><label><input type=\"text\" name=\"ns2\" value=\"" . $ns2 . "\"> The nameserver that will be provided to your client, please create an A record e.g. ns2.yourdomain.com and point to " . gethostbyname("ns2.vultr.com") . " (ns2.vultr.com)</label></td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t</tbody>\r\n\t\t\t\t\t</table>\r\n\t\t\t\t</div>\r\n\t\t\t\t<div align=\"center\"><input type=\"submit\" value=\"Save Changes\" class=\"button\"></div>\r\n\t\t\t\t</form>";
    }
    echo "<div class=\"clear\"></div>";
}

function vultr_sidebar($vars)
{
    $modulelink = $vars["modulelink"];
    $version = $vars["version"];
    $clientid = $vars["vultr_clientid"];
    $apikey = $vars["vultr_apikey"];
    $sidebar = "<span class=\"header\"><img src=\"images/icons/addonmodules.png\" class=\"absmiddle\" width=\"16\" height=\"16\" /> Vultr</span>\r\n<ul class=\"menu\">\r\n        <li>Version: " . $version . "</li>\r\n    </ul>";
    return $sidebar;
}

function vultr_check_license($licensekey, $localkey = "")
{
    $result = select_query("mod_vultr_settings", "", array( "setting" => "disable_ssl" ));
    $data = mysql_fetch_array($result);
    $disable_ssl = $data["value"];
    $result = select_query("tbladdonmodules", "", array( "module" => "vultr", "setting" => "version" ));
    $data = mysql_fetch_array($result);
    $current_version = $data["value"];
    $result = select_query("tbladdonmodules", "", array( "module" => "vultr", "setting" => "vultr_apikey" ));
    $data = mysql_fetch_array($result);
    $apikey = $data["value"];
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



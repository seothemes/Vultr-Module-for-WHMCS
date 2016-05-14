<?php 
add_hook("ShoppingCartValidateProductUpdate", 1, "hook_vultr_check_availability");
function hook_vultr_check_availability($params)
{
    $errors = array(  );
    $pid = $_SESSION["cart"]["products"][$params["i"]]["pid"];
    $result = select_query("mod_vultr_settings", "", array( "setting" => "disable_ssl" ));
    $data = mysql_fetch_array($result);
    $disable_ssl = $data["value"];
    $result = select_query("tblproducts", "", array( "id" => $pid ));
    $data = mysql_fetch_array($result);
    $options["planid"] = $data["configoption1"];
    $servertype = $data["servertype"];
    if( $servertype == "vultr" ) 
    {
        $result = select_query("tblproductconfiglinks", "", array( "pid" => $pid ));
        $data = mysql_fetch_array($result);
        $groupid = $data["gid"];
        $result = select_query("tblproductconfigoptions", "", array( "gid" => $groupid, "optionname" => "Datacenter" ));
        $data = mysql_fetch_array($result);
        $optionid = $data["id"];
        $result = select_query("tblproductconfigoptionssub", "", array( "id" => $params["configoption"][$optionid] ));
        $data = mysql_fetch_array($result);
        $options["region"] = $data["optionname"];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.vultr.com/v1/regions/availability?DCID=" . $options["region"]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if( $disable_ssl == "yes" ) 
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        $data = curl_exec($ch);
        curl_close($ch);
        $available_plans = json_decode($data, true);
        if( !in_array($options["planid"], $available_plans) ) 
        {
            $errors[] = "The plan you selected is not available in this region.";
        }

    }

    return $errors;
}



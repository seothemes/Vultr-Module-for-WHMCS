 {if $smarty.get.response eq 'success'}
    <div class="alert alert-success" role="alert">Action completed successfully</div>
{/if}
{if $alert_error}
    <div class="alert alert-danger" role="alert">{$alert_error}</div>
{/if}

<div id="information">
    {if $systemStatus == 'Active'}

        <div class="tiles clearfix">
            <div class="row">
                <div class="col-sm-6 col-xs-12 tile" style="cursor: default;">
                        <!--<div class="icon"><i class="fa fa-cog"></i></div>-->
                        <div class="stat" style="color: #058;">{$server_details.power_status}</div>
                        <div class="title">Status</div>
                        <div class="highlight bg-color-green"></div>
                </div>
                <div class="col-sm-6 col-xs-12 tile" style="cursor: default;">
                        <!--<div class="icon"><i class="fa fa-globe"></i></div>-->
                        <div class="stat" style="color: #058;">{$server_details.main_ip}</div>
                        <div class="title">Primary IP</div>
                        <div class="highlight bg-color-blue"></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6 col-xs-12 tile">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Specification</h3>
                    </div>
                    <div class="panel-body">
                        <table class="table">
                            <tr>
                                <th>Hostname</th>
                                <td>{$server_details.label}</td>
                            </tr>
                            <tr>
                                <th>Memory</th>
                                <td>{$server_details.ram}</td>
                            </tr>
                            <tr>
                                <th>vCPU</th>
                                <td>{$server_details.vcpu_count}</td>
                            </tr>
                            <tr>
                                <th>Disk</th>
                                <td>{$server_details.disk}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xs-12 tile">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Information</h3>
                    </div>
                    <div class="panel-body">
                        <table class="table">
                            <tr>
                                <th>Status</th>
                                <td><span class="label {if $server_details.power_status eq 'running'}label-success{else}label-danger{/if}">{$server_details.power_status}</span></td>
                            </tr>
                            <tr>
                                <th>Location</th>
                                <td>{$server_details.location}</td>
                            </tr>
                            <tr>
                                <th>Operating System</th>
                                <td>{$server_details.os}</td>
                            </tr>

                            <tr>
                                <th>Default Password</th>
                                <td>{$server_details.default_password}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Bandwidth</h3>
        </div>
        <div class="panel-body">
            <div class="progress">
                <div class="progress-bar" role="progressbar" aria-valuenow="{$used_bandwidth_percent}" aria-valuemin="0" aria-valuemax="100" style="width: {$used_bandwidth_percent}%; min-width: 5em;">
                    {$server_details.current_bandwidth_gb} GB
                </div>
            </div>
        </div>
    </div>

        <div class="row">
            <div class="col-sm-3 col-xs-12">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">Power On</h3>
                    </div>
                    <div class="panel-body text-center">
                        <form method="post" action="clientarea.php?action=productdetails&id={$serviceid}">
                            <input type="hidden" name="function" value="poweron" />
                            <button class="btn btn-default btn-lg" type="submit" onclick="return confirm('This action will boot your server. Do you want to proceed?')" {if $server_details.power_status neq 'stopped'}disabled{/if}><i class="fa fa-2x fa-play"></i></button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-sm-3 col-xs-12">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">Power Off</h3>
                    </div>
                    <div class="panel-body text-center">
                        <form method="post" action="clientarea.php?action=productdetails&id={$serviceid}">
                            <input type="hidden" name="function" value="poweroff" />
                            <button class="btn btn-default btn-lg" onclick="return confirm('This will power off your server. We recommend shutting down your server through the command line. This action is the same as cutting power to the server, and may cause data corruption. Do you want to proceed?')" {if $server_details.power_status eq 'stopped'}disabled{/if}><i class="fa fa-2x fa-power-off"></i></button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-sm-3 col-xs-12">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">Power Cycle</h3>
                    </div>
                    <div class="panel-body text-center">
                        <form method="post" action="clientarea.php?action=productdetails&id={$serviceid}">
                            <input type="hidden" name="function" value="reboot" />
                            <button class="btn btn-default btn-lg" onclick="return confirm('This action is equivalent to hard resetting the server, which can cause data corruption. You should do this only if you are unable to reboot your server from the command line. Do you want to proceed?')" {if $server_details.power_status eq 'stopped'}disabled{/if}><i class="fa fa-2x fa-refresh"></i></button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-sm-3 col-xs-12">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">Console</h3>
                    </div>
                    <div class="panel-body text-center">
                        <button class="btn btn-default btn-lg" id="launchConsole" {if $server_details.power_status eq 'stopped'}disabled{/if}><i class="fa fa-2x fa-terminal"></i></button>
                    </div>
                </div>
            </div>
        </div>


        <!-- consoleModal -->
        <div class="modal fade" id="consoleModal" tabindex="-1" role="dialog" aria-labelledby="consoleModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">Console</h4>
                    </div>
                    <div class="modal-body">
                        <iframe src="" frameborder="0" height="250" width="100%"></iframe>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Network</h3>
            </div>
            <div class="panel-body">
                <table class="table table-condensed" style="font-size: 12px;">
                    <thead>
                        <tr>
                            <th>Version</th>
                            <th>Type</th>
                            <th>IP Address</th>
                            <th>Netmask</th>
                            <th>Gateway</th>
                            <th>Reverse</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach key=key item=ip from=$ip_details.$SUBID}
                            <tr>
                                <td>v4</td>
                                <td>{$ip.type}</td>
                                <td>{$ip.ip}</td>
                                <td>{$ip.netmask}</td>
                                <td>{$ip.gateway}</td>
                                <td>{$ip.reverse}</td>
                            </tr>
                        {/foreach}
                        {foreach key=key item=ip from=$ip6_details.$SUBID}
                            <tr>
                                <td>v6</td>
                                <td>{$ip.type}</td>
                                <td>{$ip.ip}</td>
                                <td>/{$ip.network_size}</td>
                                <td>{$ip.network}</td>
                                <td></td>
                            </tr>
                        {/foreach}
                        {foreach key=key item=ip from=$ip6_reverse_details.$SUBID}
                            <tr>
                                <td>v6</td>
                                <td></td>
                                <td>{$ip.ip}</td>
                                <td></td>
                                <td></td>
                                <td>{$ip.reverse}</td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6 col-xs-12">

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Nameservers</h3>
                    </div>
                    <div class="panel-body">
                        <p>You will need to set your nameservers to the following:</p>
                        <table class="table table-condensed">
                            <tbody>
                            <tr>
                                <td>NS1</td>
                                <td>{$ns.ns1}</td>
                            </tr>
                            <tr>
                                <td>NS2</td>
                                <td>{$ns.ns2}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xs-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Modify DNS</h3>
                    </div>
                    <div class="panel-body">
                        <p class="text-center">
                            <button class="btn btn-default" type="button" data-toggle="modal" data-target="#adddomain">Add Domain</button>
                            <button class="btn btn-default" type="button" data-toggle="modal" data-target="#adddomainrecord" {if !$domains}disabled{/if}>Add Domain Record</button>
                        </p>
                            <hr>
                        <p class="text-center">
                            <button class="btn btn-danger" type="button" data-toggle="modal" data-target="#deletedomain" {if !$domains}disabled{/if}>Delete Domain</button>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Domain Records</h3>
            </div>
            <div class="panel-body">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Domain</th>
                        <th>Type</th>
                        <th>Name</th>
                        <th>Data</th>
                        <th>Priority</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach key=key item=dom from=$domains}
                        {foreach key=key item=record from=$dom.records}
                            {if $record.type neq 'NS'}
                                <tr><td>{$dom.name}</td><td>{$record.type}</td><td>{$record.name}</td><td>{$record.data}</td><td>{$record.priority}</td><td><form method="post" action="clientarea.php?action=productdetails&id={$serviceid}"><input type="hidden" name="domain" value="{$dom.name}" /><input type="hidden" name="record_id" value="{$record.RECORDID}" /><input type="hidden" name="function" value="deletedomainrecord" /><button class="btn btn-danger btn-xs" type="submit" onclick="return confirm('Are you sure? This will remove the domain record!')"><i class="fa fa-trash"></i></button></form></td></tr>
                            {/if}
                        {/foreach}
                    {/foreach}
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Domain Modals -->
        <div class="modal fade" id="adddomain" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="post" action="clientarea.php?action=productdetails&id={$serviceid}">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">Add Domain</h4>
                        </div>
                        <div class="modal-body">
                            <input name="domain" placeholder="e.g. domain.com">
                            <input name="ip_address" placeholder="e.g. 127.0.0.1">
                            <input type="hidden" name="function" value="adddomain" />
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save domain</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal fade" id="adddomainrecord" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document" style="width: 800px;">
                <div class="modal-content">
                    <form method="post" action="clientarea.php?action=productdetails&id={$serviceid}">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">Add Domain Record</h4>
                        </div>
                        <div class="modal-body">
                            <p align="left"><br>
                                Add a record for
                                <select name="domain">
                                    {foreach key=key item=dom from=$domains}
                                        <option value="{$dom.name}">{$dom.name}</option>
                                    {/foreach}
                                </select>
                            </p>
                            <table class="table table-condensed">
                                <thead>
                                <tr>
                                    <th>Type</th>
                                    <th id="r1text">Hostname</th>
                                    <th id="r2text">IP</th>
                                    <th id="r3text">Priority</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>
                                        <select name="type" id="domainrecordtype">
                                            <option value="A">A</option>
                                            <option value="AAAA">AAAA</option>
                                            <option value="CNAME">CNAME</option>
                                            <option value="MX">MX</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input name="name" id="domainrecordname" placeholder="e.g. www">
                                    </td>
                                    <td>
                                        <input name="data" id="domainrecorddata" placeholder="e.g. 127.0.0.1">
                                    </td>
                                    <td>
                                        <input name="priority" id="domainrecordpriority" placeholder="" disabled>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <input type="hidden" name="function" value="adddomainrecord" />
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save record</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal fade" id="deletedomain" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="post" action="clientarea.php?action=productdetails&id={$serviceid}">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">Delete Domain</h4>
                        </div>
                        <div class="modal-body">
                            <p><br>
                                Delete domain
                                <select name="domain">
                                    {foreach key=key item=dom from=$domains}
                                        <option value="{$dom.name}">{$dom.name}</option>
                                    {/foreach}
                                </select>
                            </p>
                            <input type="hidden" name="function" value="deletedomain" />
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure? This will remove all domain records!')">Delete domain</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- END Domain Modals -->

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Rename</h3>
            </div>
            <div class="panel-body">
                <p>This will update the displayed domain of your server and the PTR record. You will need to change the hostname in the server manually.</p>
                <span style="text-align: center;">
                    <form method="post" action="clientarea.php?action=productdetails&id={$serviceid}">
                        <input type="hidden" name="function" value="rename" />
                        <div class="input-append">
                            <input name="hostname" value="{$domain}" type="text">
                            <button class="btn" type="submit" onclick="return confirm('Are you sure you want to change the rDNS? It can take 24 hours to update.')">Rename</button>
                        </div>
                    </form>
                </span>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Rebuild Server</h3>
            </div>
            <div class="panel-body">
                <div align="center">
                    <table border="0" cellpadding="0" cellspacing="0">
                        <tbody>
                        <tr>
                            <td style="width: 100%;text-align:center;vertical-align:top;">
                                <form method="post" action="clientarea.php?action=productdetails&id={$serviceid}">
                                    {$imgselect}
                                    <input type="hidden" name="function" value="rebuild" />
                                    <input class="btn" style="margin-top:-10px;" type="submit" value="Rebuild" alt="Rebuild" onclick="return confirm('Are you sure? This will wipe all data!')" />
                                </form>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    {else}

        <div class="alert alert-warning text-center" role="alert">
            {if $suspendreason}
                <strong>{$suspendreason}</strong><br />
            {/if}
            {$LANG.cPanel.packageNotActive} {$status}.<br />
            {if $systemStatus eq "Pending"}
                {$LANG.cPanel.statusPendingNotice}
            {elseif $systemStatus eq "Suspended"}
                {$LANG.cPanel.statusSuspendedNotice}
            {/if}
        </div>

    {/if}

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{$LANG.cPanel.billingOverview}</h3>
        </div>
        <div class="panel-body">

            <div class="row">
                <div class="col-md-6">
                    <div class="row">
                        {if $firstpaymentamount neq $recurringamount}
                        <div class="col-xs-6 text-right">
                            {$LANG.firstpaymentamount}
                        </div>
                        <div class="col-xs-6">
                            {$firstpaymentamount}
                        </div>
                    </div>
                    <div class="row">
                        {/if}
                        {if $billingcycle != $LANG.orderpaymenttermonetime && $billingcycle != $LANG.orderfree}
                        <div class="col-xs-6 text-right">
                            {$LANG.recurringamount}
                        </div>
                        <div class="col-xs-6">
                            {$recurringamount}
                        </div>
                    </div>
                    <div class="row">
                        {/if}
                        <div class="col-xs-6 text-right">
                            {$LANG.orderbillingcycle}
                        </div>
                        <div class="col-xs-6">
                            {$billingcycle}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6 text-right">
                            {$LANG.orderpaymentmethod}
                        </div>
                        <div class="col-xs-6">
                            {$paymentmethod}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-xs-6 col-md-5 text-right">
                            {$LANG.clientareahostingregdate}
                        </div>
                        <div class="col-xs-6 col-md-7">
                            {$regdate}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6 col-md-5 text-right">
                            {$LANG.clientareahostingnextduedate}
                        </div>
                        <div class="col-xs-6 col-md-7">
                            {$nextduedate}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6 col-md-5 text-right">
                            {$LANG.clientareastatus}
                        </div>
                        <div class="col-xs-6 col-md-7">
                            {$status}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <hr>

    <div class="row">
        {if $packagesupgrade}
            <div class="col-sm-6">
                <a href="upgrade.php?type=package&amp;id={$id}" class="btn btn-success btn-block">
                    Resize Server
                </a>
            </div>
        {/if}

        <div class="col-sm-6">
            <form method="post" action="clientarea.php?action=productdetails&id={$serviceid}">
                <input type="hidden" name="function" value="destroy" />
                <button class="btn btn-danger btn-block{if $pendingcancellation}disabled{/if}" type="submit" onclick="return confirm('This is irreversible. We will destroy your server and all associated backups.')">
                    {if $pendingcancellation}
                        {$LANG.cancellationrequested}
                    {else}
                        Destroy Server
                    {/if}
                </button>
            </form>
        </div>
    </div>

</div>
{literal}
    <script>
        $('#launchConsole').click(function(){
            window.open("{/literal}{$server_details.kvm_url}{literal}",'liveMatches','directories=no,titlebar=no,toolbar=no,location=no,status=no,menubar=no,scrollbars=no,resizable=no,width=750,height=450');
        });

        jQuery('#domainrecordtype').on('change', function() {
            var recordtype = jQuery(this).val()

            if(recordtype == "A") {
                jQuery('#r1text').text('Hostname');
                jQuery('#r2text').text('IP');
                jQuery('#r3text').text('Priority');
                jQuery('#domainrecordname').attr("placeholder", "e.g. subdomain");
                jQuery('#domainrecorddata').attr("placeholder", "e.g. 127.0.0.1");
                jQuery('#domainrecordpriority').attr("placeholder", "");
                jQuery('#domainrecordpriority').attr("disabled", true);
            } else if(recordtype == "AAAA") {
                jQuery('#r1text').text('Hostname');
                jQuery('#r2text').text('IP');
                jQuery('#r3text').text('Priority');
                jQuery('#domainrecordname').attr("placeholder", "e.g. www");
                jQuery('#domainrecorddata').attr("placeholder", "e.g. ::1");
                jQuery('#domainrecordpriority').attr("placeholder", "");
                jQuery('#domainrecordpriority').attr("disabled", true);
            } else if(recordtype == "CNAME") {
                jQuery('#r1text').text('Hostname');
                jQuery('#r2text').text('Domain');
                jQuery('#r3text').text('Priority');
                jQuery('#domainrecordname').attr("placeholder", "e.g. cpanel");
                jQuery('#domainrecorddata').attr("placeholder", "e.g. cp.domain.com.");
                jQuery('#domainrecordpriority').attr("placeholder", "");
                jQuery('#domainrecordpriority').attr("disabled", true);
            } else if(recordtype == "MX") {
                jQuery('#r1text').text('Priority');
                jQuery('#r2text').text('Domain');
                jQuery('#r3text').text('Priority');
                jQuery('#domainrecordname').attr("placeholder", "e.g. mail");
                jQuery('#domainrecorddata').attr("placeholder", "e.g. mx.domain.com");
                jQuery('#domainrecordpriority').attr("placeholder", "e.g. 10");
                jQuery('#domainrecordpriority').attr("disabled", false);
            }
        });
    </script>
{/literal}
{if $license}
    <div class="licensecheapv5-widget licensecheapv5-widget-changeip">
        <div class="row" style="margin-bottom: 8px;">
            <div class="col-sm-4"><strong>Current IP</strong></div>
            <div class="col-sm-8"><code>{$license.license_ip|escape|default:'-'}</code></div>
        </div>
        <div class="row" style="margin-bottom: 12px;">
            <div class="col-sm-4"><strong>IP Changes Used</strong></div>
            <div class="col-sm-8">
                {$license.change_ip_count|default:0}
                /
                {if $license.max_ip_changes == 0}
                    Unlimited
                {else}
                    {$license.max_ip_changes|default:0}
                {/if}
            </div>
        </div>

        {if !$license.can_change_ip}
            <div class="alert alert-info" style="margin-bottom: 0;">This product does not support remote IP changes.</div>
        {elseif $change_ip_limit_reached}
            <div class="alert alert-warning" style="margin-bottom: 0;">The maximum number of IP changes allowed by HostBill has already been used.</div>
        {else}
            <form method="post" action="{$widget_url}">
                <input type="hidden" name="make" value="submit" />
                <input type="hidden" name="old_ip" value="{$license.license_ip|escape}" />
                <div class="form-group">
                    <label for="lcv5-new-ip-{$service.id}">New IP Address</label>
                    <input type="text" class="form-control" id="lcv5-new-ip-{$service.id}" name="new_ip" value="{$submitted_new_ip|escape}" placeholder="e.g. 203.0.113.10" />
                </div>
                {securitytoken}
                <button type="submit" class="btn btn-primary btn-sm">Change IP</button>
            </form>
        {/if}
    </div>
{else}
    <div class="alert alert-warning">Unable to load license information for IP change.</div>
{/if}

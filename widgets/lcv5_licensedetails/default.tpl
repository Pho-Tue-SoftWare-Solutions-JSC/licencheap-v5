{if $license}
    <div class="licensecheapv5-widget licensecheapv5-widget-details">
        <div class="row" style="margin-bottom: 8px;">
            <div class="col-sm-4"><strong>Reference Key</strong></div>
            <div class="col-sm-8"><code>{$license.reference_key|escape|default:'-'}</code></div>
        </div>
        <div class="row" style="margin-bottom: 8px;">
            <div class="col-sm-4"><strong>Product</strong></div>
            <div class="col-sm-8">{$license.product_name|escape|default:'-'}</div>
        </div>
        <div class="row" style="margin-bottom: 8px;">
            <div class="col-sm-4"><strong>Licensed IP</strong></div>
            <div class="col-sm-8"><code>{$license.license_ip|escape|default:'-'}</code></div>
        </div>
        <div class="row" style="margin-bottom: 8px;">
            <div class="col-sm-4"><strong>Status</strong></div>
            <div class="col-sm-8">
                <span class="label label-{$license.status_class|default:'default'}">{$license.status|escape|default:'Pending'}</span>
            </div>
        </div>
        <div class="row" style="margin-bottom: 8px;">
            <div class="col-sm-4"><strong>Billing Cycle</strong></div>
            <div class="col-sm-8">{$license.billing_cycle_months|default:'1'} month(s)</div>
        </div>
        <div class="row" style="margin-bottom: 8px;">
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
        <div class="row" style="margin-bottom: 8px;">
            <div class="col-sm-4"><strong>Last Action</strong></div>
            <div class="col-sm-8">{$license.last_action|escape|default:'-'}</div>
        </div>
        <div class="row" style="margin-bottom: 8px;">
            <div class="col-sm-4"><strong>Last Remote Action</strong></div>
            <div class="col-sm-8">{$license.last_remote_action|escape|default:'-'}</div>
        </div>
        <div class="row">
            <div class="col-sm-4"><strong>Last API Message</strong></div>
            <div class="col-sm-8">{$license.last_message|escape|default:'-'}</div>
        </div>

        {if $can_renew_license}
            <form method="post" action="{$widget_url}" style="margin-top: 15px;">
                <input type="hidden" name="make" value="renew" />
                {securitytoken}
                <button type="submit" class="btn btn-success btn-sm">Renew Now</button>
            </form>
        {/if}
    </div>
{else}
    <div class="alert alert-warning">Unable to load license details.</div>
{/if}

{if $license}
    <div class="licensecheapv5-widget licensecheapv5-widget-docs">
        <div class="row" style="margin-bottom: 12px;">
            <div class="col-sm-4"><strong>Command Source</strong></div>
            <div class="col-sm-8">{$license.command_source|escape|default:'-'}</div>
        </div>

        {if $can_refresh_docs}
            <form method="post" action="{$widget_url}" style="margin-bottom: 15px;">
                <input type="hidden" name="make" value="refreshdocs" />
                {securitytoken}
                <button type="submit" class="btn btn-default btn-sm">Refresh Remote Help</button>
            </form>
        {/if}

        {if $license.commands}
            <h4 style="margin-top: 0;">Install Commands</h4>
            {foreach from=$license.commands item=command}
                <div class="well well-sm" style="margin-bottom: 10px;">
                    <div style="font-weight: 600; margin-bottom: 6px;">{$command.title|escape}</div>
                    <pre style="white-space: pre-wrap; word-break: break-word; margin: 0;"><code>{$command.command|escape}</code></pre>
                </div>
            {/foreach}
        {/if}

        {if $license.notes}
            <h4>Notes</h4>
            <ul style="margin-bottom: 15px;">
                {foreach from=$license.notes item=note}
                    <li>{$note|escape}</li>
                {/foreach}
            </ul>
        {/if}

        {if $license.remote_help_available}
            <h4>Remote ShowHelp Output</h4>
            <pre style="white-space: pre-wrap; word-break: break-word;"><code>{$license.remote_help|escape}</code></pre>
        {elseif !$license.commands}
            <div class="alert alert-info" style="margin-bottom: 0;">No install documentation is available for this service yet.</div>
        {/if}
    </div>
{else}
    <div class="alert alert-warning">Unable to load install documentation for this license.</div>
{/if}

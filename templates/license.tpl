{if $details.status == 'Active' || $details.status == 'Suspended' || $details.status == 'Pending' || $details.status == 'Terminated'}
    <ul class="accor">
        <li>
            <a href="#">LicenseCheap v5</a>
            <div class="sor" id="licensecheapv5-data" style="padding: 15px 0;">
                <div style="text-align: center">
                    <img src="{$template_dir}img/ajax-loading.gif"/>
                </div>
            </div>
        </li>
    </ul>
    <script type="text/javascript" src="{$module_tpldir}license.js"></script>
{/if}

<table id='tableWagi'>
    <thead>
        <tr>
            <th>Kierowca</th>
            <th>Rolnik</th>
            <th>Ubytek</th>
            <th>Różnica</th>
        </tr>
    </thead>
    {foreach from=$transporty item=transport key=key name=name}
        {if $transport.name != ''}
            {foreach from=$transport.trans item=tr key=key name=name}
                {if isset($farmer)}
                    {if $tr.place == $farmer}
                        <tr> 
                            <td> {$transport.driver} </td>
                            <td> {$tr.place} </td>
                            <td> {$tr.perPig}  </td>
                            <td> {$tr.diff} </td>
                        </tr>
                    {/if}
                {elseif  isset($ubojnia)}
                    {if $tr.place == $ubojnia}
                        <tr> 
                            <td> {$transport.driver} </td>
                            <td> {$tr.place} </td>
                            <td> {$tr.perPig}  </td>
                            <td> {$tr.diff} </td>
                        </tr>
                    {/if}
                {else}
                    <tr>   
                        <td> {$transport.driver} </td>
                        <td> {$tr.place} </td>
                        <td> {$tr.perPig}  </td>
                        <td> {$tr.diff} </td>
                    </tr>
                {/if}
            {/foreach}
        {/if}
    {/foreach}
</table>

{foreach from=$transporty item=transport key=key name=name}
    {if $transport.name != ''}
        <div class='trBox'>
            {assign var=colorIndex value=0}
            <div class='weightBox'>
                <div class='box'>
                    <div class='leftBox'>
                        <div class='row'>
                            <div class='bars' style='display:inline-block;'>{$transport.driver}</div>
                            <div class='bars' style='display:inline-block;'>{$transport.truck}</div>
                            <div class='bars' style='display:inline-block;'>&nbsp;</div>
                        </div>
                    </div>
                    <div class='rightBox'>
                        <div class='row'>
                            <div  class='data'>
                                {$transport.name}
                            </div>
                        </div>
                    </div>
                </div>
            <div style='clear:both;'></div>
            {foreach from=$transport.trans item=tr key=key name=name}
                {if $tr.type == "Załadunek"}
                <div class='box'>
                    <div class='leftBox'>
                        <div class='row'>
                            <div class='bars' style='display:inline-block;background-color:{$colors[$colorIndex]};'>
                                {$tr.weightEmpty}
                            </div>
                            {assign var=colorIndex value=$colorIndex+1}
                            <div class='bars' style='display:inline-block;background-color:{$colors[$colorIndex]};'>
                                {$tr.weightFull}
                            </div>
                            <div class='bars' style='display:inline-block;'>
                                &nbsp;
                            </div>
                        </div>
                    </div>
                    <div class='rightBox'>
                        <div class='row'>
                            <div class='data'>
                               Ubytek: {$tr.perPig} kg
                            </div>
                            <div class='data'>
                                {$tr.amount} szt.
                            </div>
                            <div class='data'>
                                {$tr.place}
                            </div>
                        </div>
                    </div>
                    <div style='clear:both;'></div>
                </div>
                <div class='box'>
                    <div class='leftBox'>
                        <div class='row'>
                            <div class='bars' style='display:inline-block;'>
                                &nbsp;
                            </div>
                            <div class='bars' style='display:inline-block;'>
                                &nbsp;
                            </div>
                            <div class='bars' style='display:inline-block;border:1px solid {$colors[$colorIndex]};'>
                                {$tr.diff}
                            </div>
                        </div>
                    </div>
                    <div class='rightBox'>
                        <div style='display:inline-block;'>
                            &nbsp;
                        </div>
                    </div>
                    <div style='clear:both;'></div>
                </div>
                {else}
                    <div class='box'>
                        <div class='leftBox'>
                            <div class='row'>
                                <div class='bars' style='display:inline-block;background-color:{$colors[$colorIndex]};'>
                                    {$tr.weightFull}
                                </div>
                                <div class='bars' style='display:inline-block;background-color:{$colors[0]};'>
                                    {$tr.weightEmpty}
                                </div>
                                <div class='bars' style='display:inline-block;'>
                                    &nbsp;
                                </div>
                            </div>
                        </div>  
                        <div class='rightBox'>
                            <div class='row'>
                                <div class='data' >
                                </div>
                                <div class='data'>
                                </div>
                                <div class='data' >
                                    {$tr.place}
                                </div>
                            </div>
                        </div>
                        <div style='clear:both;'></div>
                    </div>
                    <div class='box'>
                        <div class='leftBox'>
                            <div class='row'>
                                <div class='bars' style='display:inline-block;'>
                                    &nbsp;
                                </div>
                                <div class='bars' style='display:inline-block;'>
                                    &nbsp;
                                </div>
                                <div class='bars' style='display:inline-block;border:1px solid {$colors[0]};'>
                                    {$tr.diff}
                                </div>
                            </div>
                        </div>
                        <div class='rightBox'>
                            <div style='display:inline-block;'>
                            </div>
                        </div>
                        <div style='clear:both;'></div>
                    </div>
                {/if}
            {/foreach}
            </div>
        </div> 
        {/if}
{/foreach}
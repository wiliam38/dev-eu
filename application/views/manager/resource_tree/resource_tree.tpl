{$this_file="{$smarty.current_dir}/{$smarty.template}"}

{if $action eq 'tree_items'}
	{include file=$this_file action="item" parent_id=$parent_id}
{/if}

{if $action eq 'item'}
	{foreach item="data" from=$pages_tree}
		{if $data.parent_id eq $parent_id}
			<div class="resource">
				<div class="item">
					{$inactive=true}
					{foreach item="tmp" from=$data.lang|default:array()}{if $tmp.status_id >= 10}{$inactive=false}{/if}{/foreach}
					
					<div class="open_close {if $inactive}inactive{/if}">
						<span class="ui-icon {if $data.opened|default:'0' eq '1'}ui-icon-minus{else}ui-icon-plus{/if} action-resource-toggle">
							<input type="hidden" name="id" value="{$data.id}"/>
						</span>
					</div>
					<div class="link" onclick="window.location='{$base_url}manager/pages/load/{$data.id}'" title="ID: {$data.id}">
						<input type="hidden" name="id" value="{$data.id}"/>
						<span {if $inactive}class="inactive"{/if}>
							<input type="hidden" name="id" value="{$data.id}"/>
							{if $data.admin_title|strip_tags|trim eq ''}...{else}{$data.admin_title|strip_tags}{/if}
						</span>
					</div>
					<div class="clear"></div>
				</div>
				<div class="sub_resource">
					{include file=$this_file action="item" parent_id=$data.id}
				</div>
			</div>		
		{/if}
	{/foreach}
{/if}
{$this_file="{$smarty.current_dir}/{$smarty.template}"}

{if $action == 'list'}
	<input type="button" value="{__('orders.close')}" class="button-green close-button-green" onclick="$('#cart_button').click();"/>

	{if empty($order.pro_order)}
		{$show_info={setting name="order.information_popup"}}
		{if !empty($show_info)}
			<div class="checkout-list" id="checkout-list-information">
				<h2>{__('orders.your_cart')}</h2>
			
				<div class="checkout-information">
					{__('orders.information_popup')}
				</div>
			
				<div class="checkout-form-submit">
					<button type="button" class="button-green" onclick="order_list('{$order.id}', this)" style="width: 100%;">{lang name="orders.continue"}</button>
				</div>
			</div>
		{/if}
	{else}
		{$show_info={setting name="order.pro_information_popup"}}
		{if !empty($show_info)}
			<div class="checkout-list" id="checkout-list-information">
				<h2>{__('orders.your_cart')}</h2>
			
				<div class="checkout-information">
					{__('orders.pro_information_popup')}
				</div>
			
				<div class="checkout-form-submit">
					<button type="button" class="button-green" onclick="order_list('{$order.id}', this)" style="width: 100%;">{lang name="orders.continue"}</button>
				</div>
			</div>
		{/if}
	{/if}
	
	<div class="checkout-list" id="checkout-list" {if !empty($show_info)}style="display: none;"{/if}>
		<h2>{__('orders.your_cart')}</h2>
		
		{foreach item="data" from=$products}
			{include file=$this_file action="view_list_row"}
		{/foreach}
		
		<div class="checkout-total" id="checkout_total">
			{__('orders.your_total')}:
			<div class="value">{$total.price|number_format:2:'.':''} {$total.curr_symbol}</div>
		</div>

		{if !empty($order.non_stock)}
			<div id="configure_error" style="text-align: left;">				
				{__('order_checkout.error_order_balance_delivery')}
			</div>
		{/if}
		
		<div class="checkout-form-submit">
			<button type="button" class="button-green" onclick="order_configure('{$order.id}', this);" style="width: 100%;">{if !empty($order.non_stock)}{lang name="products.checkout_order"}{else}{lang name="products.checkout"}{/if}</button>
		</div>
	</div>
	
	<script type="text/javascript">
		$().ready(function() {
			$('#cart .close-button').hide();
		});
	</script>
{/if}
{if $action == 'view_list_row'}
	<div class="item">
		<input type="hidden" name="order_detail_id" value="{$data.id}"/>
		
		<a href="{$base_url}{$product_page.full_alias}/{$data.l_category_alias}-c{$data.category_id}/{$data.l_alias}-i{$data.product_id}" class="main-image">
			{if !empty($data.product_image_src)}<img src="{$base_url}{$data.product_image_src|thumb}" class="vAlign"/>{/if}
			{if $data.product_coffee_gift_amount > 0}
				{$amount=$data.product_coffee_gift_amount|number_format:0:'.':''}
				{if $data.curr_symbol|lower == 'eur'}{$amount=$amount|cat:'€'}{else}{$amount=$amount|cat:$data.curr_symbol}{/if}
				<div class="product-coffee-gift">{$amount}<div class="text">{__('products.coffee_gift')}</div></div>
			{/if}
			{if $data.discount_level == 1}
				<div class="discount-price {$data.discount_color}" {if $data.product_coffee_gift_amount > 0}style="top: 40px;"{/if}>
					-{$data.discount_percents|number_format:0:'.':''}%
				</div>
			{/if}
		</a>
		<div class="text">
			<h2>{$data.qty|number_format:0:'.':''} X <a href="{$base_url}{$product_page.full_alias}/{$data.l_category_alias}-c{$data.category_id}/{$data.l_alias}-i{$data.product_id}">{$data.l_title|escape}</a></h2>
			{$data.product_reference}
			
			<div class="price-text">
				{if $data.discount_level == 1}
					<div class="original-price"><span class="value">{$data.full_original_price|number_format:2:'.':''} {$data.curr_symbol|escape}</span></div>
					<div class="price"><span class="value">{$data.full_price|number_format:2:'.':''} {$data.curr_symbol|escape}</span></div>
				{elseif $data.discount_level == 2}
					<div class="price"><span class="value">{$data.full_original_price|number_format:2:'.':''} {$data.curr_symbol|escape}</span></div>
				{else}
					<div class="price"><span class="value">{$data.full_price|number_format:2:'.':''} {$data.curr_symbol|escape}</span></div>
				{/if}
			</div>
			
			<a href="#remove" class="btn-remove" onclick="orderRemove(this); return false;"><img src="{$base_url}assets/plugins/orders/img/btn-remove.png?v=1"/></a>
		</div>
		<div class="clear"></div>
	</div>
{/if}

{if $action == 'configure'}
	<h2>{__('orders.your_cart')} ({count($products)})</h2>
	<div class="configure-list" id="configure-list">
		{foreach item="data" from=$products}
			{include file=$this_file action="view_configure_row"}
		{/foreach}
	</div>
	
	<div id="configure_error">
		{if !empty($order.non_stock_coffee)}
			{__('order_checkout.error_order_balance_coffee')}<br/>
		{/if}
		{if !empty($order.non_stock)}
			{__('order_checkout.error_order_balance')}<br/>
			{__('order_checkout.error_order_balance_delivery')}
		{/if}
	</div>

	<div class="checkout-form-submit" style="padding-top: 10px;">
		<button type="button" class="button-back" onclick="order_back('{$order.id}', 'list', this);">{__('order_checkout.back')}</button>
		<button type="button" class="button-green" onclick="order_checkout('{$order.id}', this);">
			<div class="checkout-text">{if !empty($order.non_stock)}{lang name="products.checkout_order"}{else}{lang name="products.checkout"}{/if}</div>
			<div class="checkout-total" id="checkout_total">
				{$total.price|number_format:2:'.':''} {$total.curr_symbol}
			</div>	
			<div class="clear"></div>		
		</button>
	</div>
{/if}
{if $action == 'configure_data'}
	{foreach item="data" from=$products}
		{include file=$this_file action="view_configure_row"}
	{/foreach}
{/if}

{if $action == 'view_configure_row'}
	<div class="item">
		<input type="hidden" name="order_detail_id" value="{$data.id}"/>
		
		<a href="{$base_url}{$product_page.full_alias}/{$data.l_category_alias}-c{$data.category_id}/{$data.l_alias}-i{$data.product_id}" class="main-image">
			{if !empty($data.product_image_src)}<img src="{$base_url}{$data.product_image_src|thumb}" class="vAlign"/>{/if}
			{if $data.product_coffee_gift_amount > 0}
				{$amount=$data.product_coffee_gift_amount|number_format:0:'.':''}
				{if $data.curr_symbol|lower == 'eur'}{$amount=$amount|cat:'€'}{else}{$amount=$amount|cat:$data.curr_symbol}{/if}
				<div class="product-coffee-gift">{$amount}<div class="text">{__('products.coffee_gift')}</div></div>
			{/if}
			{if $data.discount_level == 1}
				<div class="discount-price {$data.discount_color}" {if $data.product_coffee_gift_amount > 0}style="top: 40px;"{/if}>
					-{$data.discount_percents|number_format:0:'.':''}%
				</div>
			{/if}
		</a>
		<h2><a href="{$base_url}{$product_page.full_alias}/{$data.l_category_alias}-c{$data.category_id}/{$data.l_alias}-i{$data.product_id}">{$data.product_reference|escape}</a></h2>
		
		<div class="price-data">
			<div class="qty" data-category_id="{$data.category_id}" data-pro_sub_category_id="{$data.pro_sub_category_id|default:''}">				
				<a href="#plus" class="btn-minus" onclick="orderDecrese(this); return false;"><img src="{$base_url}assets/plugins/orders/img/btn-minus.png"/></a>
				<div class="product_qty_wrapper">x <span class="product_qty">{$data.qty|number_format:0:'.':''}</span></div>
				<a href="#minus" class="btn-plus" onclick="orderIncrese(this); return false;"><img src="{$base_url}assets/plugins/orders/img/btn-plus.png"/></a>
				<a href="#remove" class="btn-remove" onclick="orderRemove(this); return false;"><img src="{$base_url}assets/plugins/orders/img/btn-remove.png?v=1"/></a>
				<div class="clear"></div>
			</div>
			<div class="price-text">
				{if $data.discount_level == 1}
					<div class="original-price"><span class="value">{$data.full_original_price|number_format:2:'.':''} {$data.curr_symbol|escape}</span></div>
					<div class="price"><span class="value">{$data.full_price|number_format:2:'.':''} {$data.curr_symbol|escape}</span></div>
				{elseif $data.discount_level == 2}
					<div class="price"><span class="value">{$data.full_original_price|number_format:2:'.':''} {$data.curr_symbol|escape}</span></div>
				{else}
					<div class="price"><span class="value">{$data.full_price|number_format:2:'.':''} {$data.curr_symbol|escape}</span></div>
				{/if}
				<div class="error">
					{if $data.qty > $data.product_reference_balance}{__('order_checkout.error_item_balance')}: {$data.product_reference_balance|number_format:0:'.':''}{/if}
				</div>
			</div>
		</div>
	</div>
{/if}

{if $action == 'checkout'}
	<form method="post" id="payment_form" class="current-order-form">
		<table class="checkout_table" cellpadding="0" cellspacing="0">
			<tr>
				<th colspan="2" style="width: 350px;" class="left first">{lang name="order_checkout.product_title"}</th>
				<th style="width: 90px;" class="right">{lang name="order_checkout.qty"}</th>
				<th style="width: 140px;" class="right">{lang name="order_checkout.price"}</th>
				<th style="width: 140px;" class="right last">{lang name="order_checkout.total"}</th>
			</tr>
			
			{$total_qty=0}
			{foreach item="data" from=$products name="products"}
				<tr class="{if $smarty.foreach.products.first}products-first{/if} {if $smarty.foreach.products.last}products-last{/if}">
					<td class="first bold" style="padding-right: 5px; width: 20px;">{$smarty.foreach.products.iteration}.</td>
					<td class="bold">
						{$data.product_reference}
						<div class="category">{$data.l_category_title}</div>
					</td>
					<td class="right bold">{$data.qty|number_format:0:'.':''}</td>
					<td class="right">
						<div class="price">
							{$data.full_price|number_format:2:'.':''} {$order.curr_symbol}
							{if (($data.full_original_price|number_format:2:'.':'')-($data.full_price|number_format:2:'.':'')) > 0} 
								<div class="discount-price">&nbsp;{$data.full_original_price|number_format:2:'.':''} {$order.curr_symbol}&nbsp;</div>
							{/if}
						</div>
					</td>
					<td class="right green last">{(($data.full_price|number_format:2:'.':'') * $data.qty|number_format:2:'.':'')|number_format:2:'.':''} {$order.curr_symbol}</td>
				</tr>
				{$total_qty=($total_qty|number_format:2:'.':'')+($data.qty|number_format:2:'.':'')}
			{/foreach}
			
			<tr>
				<th colspan="5" class="left first last">
					{lang name="order_checkout.shipping_title"}
				</th>
			</tr>
			
			{$shipping_costs=0}
			{$shipping_vat=0}
			{foreach item="data" from=$shippings name="shippings"}
				{if empty($order.pro_order) || (!empty($order.pro_order) && $data.id != 5)}
					<tr class="shipping-item {if $smarty.foreach.shippings.first}shipping-first{/if} {if $smarty.foreach.shippings.last}shipping-last{/if}">
						<td class="center first" style="padding-right: 5px; vertical-align: top;">
							<input type="radio" name="shipping_id" value="{$data.id}" {if $order.shipping_id == $data.id}checked="checked"{/if} style="margin-top: 3px;" onchange="recalculate_shipping('{$data.total|number_format:4:'.':''}', '{if $order.no_vat != 1}{$data.vat_type_value|number_format:2:'.':''}{else}0{/if}', this);"/>
						</td>
						<td colspan="3" class="shipping-item-title">
							{$data.l_description|escape}
							{if $data.id == 5}
								<div class="shipping-statoil" {if $order.shipping_id != 5}style="display: none;"{/if}>
									<select name="shipping_statoil_id" style="background-color: #f0f0f0; margin-bottom: 7px; margin-top: 3px; width: 100%; text-align: left;">
										<option value=""></option>
										{foreach item="statoil" from=$shippings_statoil}
											<option value="{$statoil.id}" {if $order.shipping_statoil_id == $statoil.id}selected="selected"{/if}>{$statoil.name} - {$statoil.address}</option>
										{/foreach}
									</select>
								</div>
							{/if}
							{if $data.id == 9}
								<div class="shipping-office" style="font-weight: normal; margin-top: 10px; {if $order.shipping_id != 9}display: none;{/if}">
									{__('order_checkout.pickup_time')}: <span class="must-fill">*</span>&nbsp;&nbsp;&nbsp;&nbsp;
									<input type="text" name="shipping_pickup_time" value="{$order.shipping_pickup_time}" class="input-text" style="width: 250px;"/>
								</div>
							{/if}
						</td>
						<td class="right last shipping-item-price {if $data.total == 0}free{/if}">
							{if $data.total == 0}
								{__('order_checkout.free_shipping')}
							{else}
								{if $order.shipping_id == $data.id}
									{$shipping_costs=$data.total}
									{if $order.no_vat != 1}{$shipping_vat=$data.vat_type_value}{/if}
								{/if}
								
								{if $order.no_vat != 1}{($data.total * (1 + $data.vat_type_value / 100))|number_format:2:'.':''}
								{else}{$data.total|number_format:2:'.':''}{/if}
								{$order.curr_symbol}
							{/if}
						</td>
					</tr>
				{/if}
			{/foreach}
			
			<tr class="total total-data">
				<td class="right" colspan="4">{lang name="order_checkout.sum"}:</td>
				<td class="right last">{$total_data.price|number_format:2:'.':''} {$order.curr_symbol}</td>
			</tr>
			
			<tr class="total-data">
				<td class="right" colspan="4">{lang name="order_checkout.pay_shipping"}:</td>
				<td class="right last"><span id="shipping_display">{($shipping_costs * (1 + $shipping_vat / 100))|number_format:2:'.':''}</span> {$order.curr_symbol}</td>
			</tr>
			
			{if $total_data.product_coffee_gift_amount > 0}
				<tr class="total-data">
					<td colspan="4" class="right bold">{lang name="order_checkout.sum_of_coffee_gift_amount"}:</td>
					<td class="right last bold">{$total_data.product_coffee_gift_amount|number_format:2:'.':''}</span> {$order.curr_symbol}</td>
				</tr>
			{/if}
			
			<tr class="total-data {if $order.no_vat != 1}last-total{/if}">
				<td class="right" colspan="4"><b>{lang name="order_checkout.pay_sum"} {if $order.no_vat != 1}{lang name="order_checkout.pay_sum_with_vat"}{else}{lang name="order_checkout.pay_sum_without_vat"}{/if}:</b></td>
				<td class="right green last">
					<input type="hidden" id="total_value" value="{$total_data.price|number_format:2:'.':''}"/>
					<b id="total_display">{($total_data.price|number_format:2:'.':'' + $shipping_costs|number_format:2:'.':'' + ($shipping_costs * $shipping_vat / 100))|number_format:2:'.':''}</b> <b>{$order.curr_symbol}</b>
				</td>
			</tr>

			<tr class="error-data">
				<td class="first"></td>
				<td id="error" style="color: #ff0000; padding-top: 10px;" class="last" colspan="4"></td>
			</tr>	
		</table>
		
		<input type="hidden" name="order_id" value="{$order.id}"/>
		
		{if !empty($order.non_stock)}
			<div id="configure_error" style="padding-bottom: 10px;">				
				{__('order_checkout.error_order_balance_delivery')}				
			</div>
		{/if}
		
		<div class="checkout-form-submit" style="padding-top: 0px;">
			<button type="button" class="button-back" onclick="order_back('{$order.id}', 'configure', this);">{__('order_checkout.back')}</button>
			<button type="submit" class="button-green">
				<div class="checkout-text">{if !empty($order.non_stock)}{lang name="products.checkout_order"}{else}{lang name="products.checkout"}{/if}</div>
				<div class="checkout-total" id="checkout_total">
					{($total_data.price|number_format:2:'.':''  + $shipping_costs|number_format:2:'.':'' + ($shipping_costs * $shipping_vat / 100))|number_format:2:'.':''} {$order.curr_symbol}
				</div>	
				<div class="clear"></div>		
			</button>
		</div>
	</form>
{/if}

{if $action == 'checkout2'}
	<h2>{lang name="order_checkout.additional_info"}:</h2>
	<form method="post" id="payment_form2" class="current-order-form">
		<input type="hidden" name="order_id" value="{$order.id}"/>
		<table class="order_info_table">
			{if $admin_role}
				<tr>
					<th>{lang name="order_checkout.user_name"}:</th>
					<td>
						<select name="tmp_owner_user_id" id="tmp_owner_user_id" class="input-select" style="width: 269px;">
							{if !empty($order.tmp_owner_user_id)}<option value="{$order.tmp_owner_user_id}" selected="selected">{$order.tmp_owner_user_value}</option>
							{else}<option value="" selected="selected">--- none ---</option>{/if}
						</select>
					</td>
				</tr>
			{/if}		
			<tr>
				<th>{lang name="order_checkout.contact_name"}: <span class="must-fill">*</span></th>
				<td><input type="text" class="input-text" name="contact_name" value="{$order.contact_name|escape}" style="width: 300px;"/></td>
			</tr>
			<tr>
				<th>{lang name="order_checkout.company"}:</th>
				<td><input type="text" class="input-text" name="company" value="{$order.company|escape}" style="width: 300px;"/></td>
			</tr>
			<tr>
				<th>{lang name="order_checkout.contact_reg_nr"}:</th>
				<td><input type="text" class="input-text" name="reg_nr" value="{$order.reg_nr|escape}" style="width: 300px;"/></td>
			</tr>
			<tr>
				<th>{lang name="order_checkout.company_vat_nr"}:</th>
				<td><input type="text" class="input-text" name="vat_nr" value="{$order.vat_nr|escape}" style="width: 300px;"/></td>
			</tr>
			
			<tr>
				<th>{lang name="order_checkout.contact_email"}: <span class="must-fill">*</span></th>
				<td><input type="text" class="input-text" name="email" value="{$order.email|escape}" style="width: 300px;"/></td>
			</tr>
			<tr>
				<th>{lang name="order_checkout.contact_phone"}: <span class="must-fill">*</span></th>
				<td><input type="text" class="input-text" name="phone" value="{$order.phone|escape}" style="width: 300px;"/></td>
			</tr>
			<tr {if in_array($order.shipping_id, array(5,9))}style="display: none;"{/if}>
				<th>{lang name="order_checkout.contact_address"}: <span class="must-fill">*</span></th>
				<td><input type="text" class="input-text" name="address" value="{$order.address|escape}" style="width: 300px;"/></td>
			</tr>
			<tr>
				<th style="vertical-align: top; padding-top: 20px;">{lang name="order_checkout.contact_pay_type"}:</th>
				<td style=" padding-top: 20px;  padding-bottom: 20px;">
					<table style="width: 100%; font-size: 14px;">						
						{foreach item="data" from=$pay_types}
							{if empty($order.pro_order) || (!empty($order.pro_order) && $data.value != 'card')}
								<tr>
									<td style="width: 20px; vertical-align: middle;"><input type="radio" name="pay_type_id" value="{$data.id}" {if $order.pay_type_id|default:1 == $data.id}checked{/if}/></td>
									<td style="vertical-align: middle;">{$data.l_name|escape}</td>
								</tr>
							{/if}
						{/foreach}						
					</table>
				</td>
			</tr>
			<tr class="splitter-bottom">
				<th style="vertical-align: top; padding-top: 10px;">{lang name="order_checkout.contact_notes"}:</th>
				<td>
					<textarea name="notes" class="input-textarea">{$order.notes}</textarea>
				</td>
			</tr>	
			
			<tr>
				<th></th>
				<td id="error" style="color: #ff0000; padding-top: 10px; height: 0px;"></td>
			</tr>		
		</table>
		
		{if !empty($order.non_stock)}
			<div id="configure_error" style="padding-bottom: 10px;">				
				{__('order_checkout.error_order_balance_delivery')}				
			</div>
		{/if}
		
		<div class="checkout-form-submit" style="padding-top: 0px;">
			<button type="button" class="button-back" onclick="order_back('{$order.id}', 'checkout', this);">{__('order_checkout.back')}</button>
			<button type="submit" class="button-green">
				<div class="checkout-text">{if !empty($order.non_stock)}{lang name="products.checkout_order"}{else}{lang name="products.checkout"}{/if}</div>
				<div class="checkout-total" id="checkout_total">
					{$total_data.total_vat|number_format:2:'.':''} {$order.curr_symbol}
				</div>	
				<div class="clear"></div>		
			</button>
		</div>
	</form>
{/if}

{if $action == 'no_order'}
	<div class="error-content">
		<h2>{__('orders.your_cart')}</h2>
		<div class="info">{__('current_order.empty')}</div>	
	</div>
{/if}

{if $action == 'login'}
	<div class="error-content">
		<h2>{__('orders.your_cart')}</h2>
		<div class="info">{__('orders.no_user')}</div>	
		<input type="button" class="button-green" value="{__('user_login.button_login')}" onclick="$('#login_button').click();" style="width: 237px; margin: 30px auto 10px auto; display: block;"/>
	</div>
{/if}

{if $action == 'paywithcard'}
	<form method="post" id="payment_form" class="current-order-form">
		<table class="checkout_table" cellpadding="0" cellspacing="0">
			<tr>
				<th colspan="2" style="width: 350px;" class="left first">{lang name="order_checkout.product_title"}</th>
				<th style="width: 90px;" class="right">{lang name="order_checkout.qty"}</th>
				<th style="width: 130px;" class="right">{lang name="order_checkout.price"}</th>
				<th style="width: 130px;" class="right last">{lang name="order_checkout.total"}</th>
			</tr>
			
			{foreach item="data" from=$products name="products"}
				{include file=$this_file action="view_paywithcard_row" item=$smarty.foreach.products.iteration}
			{/foreach}
						
			<tr class="total-data">
				<td class="right" colspan="4">{lang name="order_checkout.pay_shipping"}:</td>
				<td class="right last">{$order.shipping_total_vat|number_format:2:'.':''} {$order.curr_symbol}</td>
			</tr>
			
			<tr class="total-data">
				<td class="right" colspan="4"><b>{lang name="order_checkout.pay_sum"} {if $order.no_vat != 1}{lang name="order_checkout.pay_sum_with_vat"}{else}{lang name="order_checkout.pay_sum_without_vat"}{/if}:</b></td>
				<td class="right green last">
					<input type="hidden" id="total_value" value="{$total.total_vat|number_format:2:'.':''}"/>
					<b id="total_display">{$total.total_vat|number_format:2:'.':''}</b> <b>{$order.curr_symbol}</b></td>
			</tr>

			{if $order.no_vat == 1}
				<tr class="total-data">
					<td class="right last" colspan="5" style="text-transform: none; font-size: 14px; padding-top: 7px;">{__('order_checkout.eu_vat_directive')}</td>
				</tr>
			{/if}
		</table>
		
		<input type="hidden" name="order_id" value="{$order.id}"/>
		<table class="order_info_table">
			<tr>
				<th>{lang name="order_checkout.contact_name"}:</th>
				<td>{$order.contact_name}</td>
			</tr>
			<tr>
				<th>{lang name="order_checkout.company"}:</th>
				<td>{$order.company}</td>
			</tr>
			<tr>
				<th>{lang name="order_checkout.contact_reg_nr"}:</th>
				<td>{$order.reg_nr}</td>
			</tr>
			<tr>
				<th>{lang name="order_checkout.company_vat_nr"}:</th>
				<td>{$order.vat_nr}</td>
			</tr>
			
			<tr>
				<th>{lang name="order_checkout.contact_email"}:</th>
				<td>{$order.email}</td>
			</tr>
			<tr>
				<th>{lang name="order_checkout.contact_phone"}:</th>
				<td>{$order.phone}</td>
			</tr>
			<tr>
				<th>{lang name="order_checkout.contact_address"}:</th>
				<td>
					{$order.shipping_info|escape} {if $order.shipping_id == 5}({$order.shipping_statoil_address}){/if}
					{if trim($order.address) != ''} - {$order.address}{/if}
					{if trim($order.shipping_pickup_time) != ''} - {$order.shipping_pickup_time}{/if}
				</td>
			</tr>
			<tr>
				<th>{lang name="order_checkout.contact_pay_type"}:</th>
				<td>{$order.pay_type_l_name}</td>
			</tr>
			<tr class="splitter-bottom">
				<th>{lang name="order_checkout.contact_notes"}:</th>
				<td>{$order.notes}</td>
			</tr>	
			
			<tr>
				<th></th>
				<td id="error" style="color: #ff0000; padding-top: 10px; height: 0px;"></td>
			</tr>		
		</table>
		
		{if !empty($order.non_stock)}
			<div id="configure_error" style="padding-bottom: 10px;">				
				{__('order_checkout.error_order_balance_delivery')}				
			</div>
		{/if}
		
		<div class="checkout-form-submit" style="padding-top: 0px;">
			<button type="button" class="button-back" onclick="order_back('{$order.id}', 'checkout2', this);">{__('order_checkout.back')}</button>
			<a href="plugins/firstdata/payment/{$order.id}" class="button-green" target="_blank">
				<div class="checkout-text">{lang name="order_checkout.paywithcard_pay"}</div>
				<div class="checkout-total" id="checkout_total">
					{$total.total_vat|number_format:2:'.':''} {$order.curr_symbol}
				</div>	
				<div class="clear"></div>		
			</a>
		</div>
	</form>
{/if}

{if $action == 'confirm'}
	<form method="post" id="confirm_form" class="current-order-form">
		<input type="hidden" name="order_id" value="{$order.id}"/>
	
		<table class="checkout_table" cellpadding="0" cellspacing="0">
			<tr>
				<th colspan="2" style="width: 350px;" class="left first">{lang name="order_checkout.product_title"}</th>
				<th style="width: 90px;" class="right">{lang name="order_checkout.qty"}</th>
				<th style="width: 130px;" class="right">{lang name="order_checkout.price"}</th>
				<th style="width: 130px;" class="right last">{lang name="order_checkout.total"}</th>
			</tr>
			
			{foreach item="data" from=$products name="products"}
				{include file=$this_file action="view_paywithcard_row" item=$smarty.foreach.products.iteration}
			{/foreach}
						
			<tr class="total-data">
				<td class="right" colspan="4">{lang name="order_checkout.pay_shipping"}:</td>
				<td class="right last">{$order.shipping_total_vat|number_format:2:'.':''} {$order.curr_symbol}</td>
			</tr>
			
			<tr class="total-data">
				<td class="right" colspan="4"><b>{lang name="order_checkout.pay_sum"} {if $order.no_vat != 1}{lang name="order_checkout.pay_sum_with_vat"}{else}{lang name="order_checkout.pay_sum_without_vat"}{/if}:</b></td>
				<td class="right green last">
					<input type="hidden" id="total_value" value="{$total.total_vat|number_format:2:'.':''}"/>
					<b id="total_display">{$total.total_vat|number_format:2:'.':''}</b> <b>{$order.curr_symbol}</b></td>
			</tr>

			{if $order.no_vat == 1}
				<tr class="total-data">
					<td class="right last" colspan="5" style="text-transform: none; font-size: 14px; padding-top: 7px;">{__('order_checkout.eu_vat_directive')}</td>
				</tr>
			{/if}
		</table>
		
		<input type="hidden" name="order_id" value="{$order.id}"/>
		<table class="order_info_table">
			<tr>
				<th>{lang name="order_checkout.contact_name"}:</th>
				<td>{$order.contact_name}</td>
			</tr>
			{if !empty($order.company)}
				<tr>
					<th>{lang name="order_checkout.company"}:</th>
					<td>{$order.company}</td>
				</tr>
			{/if}
			<tr>
				<th>{lang name="order_checkout.contact_reg_nr"}:</th>
				<td>{$order.reg_nr}</td>
			</tr>
			{if !empty($order.vat_nr)}
				<tr>
					<th>{lang name="order_checkout.company_vat_nr"}:</th>
					<td>{$order.vat_nr}</td>
				</tr>
			{/if}
			
			<tr>
				<th>{lang name="order_checkout.contact_email"}:</th>
				<td>{$order.email}</td>
			</tr>
			<tr>
				<th>{lang name="order_checkout.contact_phone"}:</th>
				<td>{$order.phone}</td>
			</tr>
			<tr>
				<th>{lang name="order_checkout.contact_address"}:</th>
				<td>
					{$order.shipping_info|escape} {if $order.shipping_id == 5}({$order.shipping_statoil_address}){/if}
					{if trim($order.address) != ''} - {$order.address}{/if}
					{if trim($order.shipping_pickup_time) != ''} - {$order.shipping_pickup_time}{/if}
				</td>
			</tr>
			<tr>
				<th>{lang name="order_checkout.contact_pay_type"}:</th>
				<td>{$order.pay_type_l_name}</td>
			</tr>
			<tr class="splitter-bottom">
				<th>{lang name="order_checkout.contact_notes"}:</th>
				<td>{$order.notes}</td>
			</tr>	
			
			<tr>
				<th></th>
				<td id="error" style="color: #ff0000; padding-top: 10px; height: 0px;"></td>
			</tr>		
		</table>
		
		{if !empty($order.non_stock)}
			<div id="configure_error" style="padding-bottom: 10px;">				
				{__('order_checkout.error_order_balance_delivery')}				
			</div>
		{/if}
		
		<div class="checkout-form-submit" style="padding-top: 0px;">
			<button type="button" class="button-back" onclick="order_back('{$order.id}', 'checkout2', this);">{__('order_checkout.back')}</button>
			<button type="submit" class="button-green">
				<div class="checkout-text">{if !empty($order.non_stock)}{lang name="products.checkout_order"}{else}{lang name="products.checkout"}{/if}</div>
				<div class="checkout-total" id="checkout_total">
					{$total.total_vat|number_format:2:'.':''} {$order.curr_symbol}
				</div>	
				<div class="clear"></div>
			</button>
		</div>
	</form>
{/if}

{if $action == 'view_paywithcard_row'}
	<tr class="bold">
		<td style="padding-right: 5px; width: 20px;" class="first">{$item}.</td>
		<td>{$data.product_reference}</td>
		<td class="right">{$data.qty|number_format:0:'.':''}</td>
		<td class="right">{$data.full_price|number_format:2:'.':''} {$order.curr_symbol}</td>
		<td class="right green last">{($data.full_price * $data.qty)|number_format:2:'.':''} {$order.curr_symbol}</td>
	</tr>
{/if}

{if $action == 'order_placed'}
	<h2>{__('orders.your_cart')}</h2>
	<div class="info">{__('current_order.order_placed')}</div>

	{include file=$this_file action="ga_purchase"}
{/if}

{if $action == 'order_paid'}
	<h2>{__('orders.your_cart')}</h2>
	<div class="info">{__('current_order.order_paid')}</div>

	{include file=$this_file action="ga_purchase"}
{/if}

{if $action == 'ga_purchase'}
	{* $ga_enabled="{setting name="google.analystic_enabled"}"}
	{$ga_key="{setting name="google.analystic_key"}"}

	{if $ga_enabled == "1" && !empty($ga_key)}
		<script type="text/javascript">
			ga('create', '{$ga_key}', 'auto');
			ga('require', 'ec');
			ga('set', '&cu', '{$total.curr_symbol}');

			{foreach item="product" from=$products}
				ga('ec:addProduct', {               							// Provide product details in an productFieldObject.
					'id': '{$product.product_id|escape:'javascript'}',      	// Product ID (string).
					'name': '{$product.product_title|escape:'javascript'}', 	// Product name (string).
					'category': '{$product.l_category_title|escape:'javascript'}',
					'price': '{$product.price|number_format:2:'.':''}',         // Product price (currency).
					'quantity': {$product.qty|number_format:0:'.':''}           // Product quantity (number).
				});
			{/foreach}

			ga('ec:setAction', 'purchase', {          							// Transaction details are provided in an actionFieldObject.
				'id': '{$order.id|escape:'javascript'}',                    	// (Required) Transaction id (string).
				'affiliation': '19bar.eu', 										// Affiliation (string).
				'revenue': '{$total.price|number_format:2:'.':''}',        		// Revenue (currency).
				'tax': '{$total.vat|number_format:2:'.':''}',                   // Tax (currency).
				'shipping': '{$order.shipping_total|number_format:2:'.':''}',   // Shipping (currency).
			});

			ga('send', 'pageview');
		</script>
	{/if *}

	{$gtm_enabled="{setting name="google.tag_manager_enabled"}"}
	{$gtm_key="{setting name="google.tag_manager_key"}"}

	{if $gtm_enabled == "1" && !empty($gtm_key)}
		<script>
			dataLayer.push({
				'currencyCode': '{$total.curr_symbol}',
				'transactionId': '{$order.id|escape:'javascript'}',
				'transactionAffiliation': '19bar.eu',
				'transactionTotal': '{$total.price|number_format:2:'.':''}',
				'transactionTax': '{$total.vat|number_format:2:'.':''}',
				'transactionShipping': '{$order.shipping_total|number_format:2:'.':''}',
				'transactionProducts': [
					{foreach item="product" from=$products}
						{
							'id': '{$product.product_id|escape:'javascript'}',
							'sku': '{$product.product_reference_code|escape:'javascript'}',
							'name': '{$product.product_reference_reference|escape:'javascript'}',
							'category': '{$product.l_category_title|escape:'javascript'}',
							'price': '{$product.price|number_format:2:'.':''}',
							'quantity': '{$product.qty|number_format:0:'.':''}'
						},
					{/foreach}
				],
				'event': 'transactionPurchase'
			});
		</script>
	{/if}
{/if}
<?xml version='1.0' encoding='utf-8' ?>
<root>
{foreach item="data" from=$products}
	<item>
		<name>{$data.title|escape}</name>
		<link>{$base_url}{$product_page[0].full_alias}/{$data.category_full_alias}/{$data.alias}-i{$data.id}</link>
		<price>{$data.price_vat|number_format:2:'.':''}</price>
		<image>{$base_url}{$data.image_src}</image>
		<manufacturer>Nespresso</manufacturer>
		<category>{$data.category_name|escape}</category>
		<category_full>{$data.category_full_name|escape}</category_full>
		<category_link>{$base_url}{$product_page[0].full_alias}/{$data.category_full_alias}</category_link>
		<in_stock></in_stock>
		<delivery_cost_riga></delivery_cost_riga>
	</item>
{/foreach}
</root>
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
	{foreach item=data from=$links}
		<url>
	        <loc>{$data.link}</loc>
	        <lastmod>{$data.last_change|date_format:'Y-m-d'}</lastmod>
	        <changefreq>{$data.change_frequency|default:'monthly'}</changefreq>
	        <priority>{$data.priority|default:'0.5'}</priority>
	    </url>
	{/foreach}
</urlset>
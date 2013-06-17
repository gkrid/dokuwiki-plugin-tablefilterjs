tablefilterjs = {};
tablefilterjs.decodeHTML = function(text)
{
    return jQuery('<div/>').html(text).text();
};
tablefilterjs.find_th_eq = function( col, $table )
{
    var eq = -1;
    $table.find('th').each(function() {
	if( jQuery(this).text() == col )
	    eq = jQuery(this).index();
    });
    return eq;
}
jQuery(document).ready(function ()
{
    jQuery(".tablefilterjs").each(function() {
	$table = jQuery(this).find("table");

	var filters = jQuery.parseJSON( tablefilterjs.decodeHTML(jQuery(this).data("filters")) );

	for( col in filters )
	{
	    if( isNaN( col ) )
	    {
		var eq = tablefilterjs.find_th_eq( col, $table );
		if( eq == -1)
		    continue;
	    } else
	    {
		var eq = parseInt( col ) - 1;
	    }
            var regex = new RegExp( filters[col][0], filters[col][1] );
    	    $table.find("tr:has(td)").each(function() {
		if( ! jQuery(this).find("td").eq(eq).text().match( regex ) )
		    jQuery(this).hide();
	    });
	}

    });
});

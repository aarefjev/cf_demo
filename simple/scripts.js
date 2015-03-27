$(document).ready(function(){

	draw_files();
	
	



});

function ra(){
	// alert(this);
}

function draw_files(){

	var results = '';
	// alert(server+"/tmpdir/allfiles.html");
	$.get(server+"/tmpdir/allfiles.html", function(response) {
		 allfiles  = response;
		// alert(allfiles.length);
		$('#results').html('start');
		
		var json_text = '['+response+']';
		//var objJS = eval("(function(){return " + json_text + ";})()"); // oldschool ->  This represents a potential security problem. Can execute any javascipt code
		var objJS = $.parseJSON(json_text); // <-- JQUERY way! Possibly better (definitely more secure)

		// dump results into page
		for(var i=0;i<objJS.length;i++){
			results = results + '<div class="files" onclick="ra()">' + objJS[i]+'</div>'
			$('#results').html(results);
		}
		
		$( ".files" ).bind( "click", function() {
		  load_one_json($( this ).text());
		});
	});
	

}

function load_one_json(current_file){
	
	var full_path = server+current_file;
	// alert(full_path);
	var items = '';
	$('#data').html('loading...');
	$.getJSON( server+current_file, function( data ) {
		
		$.each( data, function( key, val ) {
			items = items + "<li>" + key + ':' + val + "</li>";
		});
		$('#data').html('<ul>'+items+'</ul>');
	});
}





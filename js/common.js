$(document).ready(function ($){
	function send_data(url,data){
		$.ajax({
			type: "POST",
			url: url,
			data: data
		});
	}
});

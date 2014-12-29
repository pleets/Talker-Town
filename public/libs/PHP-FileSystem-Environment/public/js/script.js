$(function(){
	$(".shell form").submit(function(event){
		
		event.preventDefault();

		var input = $(this).children("input");

		var shell = $(this).parent().parent();
		var data = $(this).serializeArray();

		$.ajax({
			url: "adapter.php",
			type: 'post',
			data: data,
			success: function(response) {
				shell.children(".body").append("<div>$: "+data[0]["value"]+"</div>");
				shell.children(".body").append("<div>"+response+"</div>");
				input.val("");
			},
		});

	});
});

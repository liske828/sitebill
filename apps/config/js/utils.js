    $(document).ready(function(){
    	
    	$('.nav-tabs a').click(function(){
    		$('body,html').animate({scrollTop: 0}, 1000);
    	});
    	
    	
    	$('.cnf_resort').live('click',function(){
    		var form=$(this).parents('form').eq(0);
    		var ids=[];
    		form.find('input.sort_order').each(function(){
    			ids.push($(this).val());
    		});
    		$.ajax({
    			url: estate_folder + '/apps/config/js/ajax.php',
    			data: 'action=resort&ids='+ids.join(','),
    			type: "POST",
    			success: function(json){
    		    	//$('#sql_log').html('Шаблон сохранен' + json);
        		},
    		});
    	});
        
    	
    });
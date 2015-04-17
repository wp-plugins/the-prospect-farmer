$j = jQuery.noConflict();

$j(document).ready(function(){
	var sccontent;
	
	$j('#tpf_list li').click(function(){
		$j('#tpf_name_edit').val($j(this).text());
		$j('#tpf_edit_form').submit();
	});
	
	$j('#tpf_list li').draggable({
		start: function(){ $j('#tpf_delete').addClass('tpf_trashHover'); },
		stop: function(){ $j('#tpf_delete').removeClass('tpf_trashHover'); }
	});
	$j('#tpf_delete').droppable({
		drop: function(e, ui){
			$j('#tpf_list li').unbind('click');
			$j('#tpf_name_edit').val(ui.draggable.text());
			$j('#tpf_form_action').val('delete');
			$j('#tpf_edit_form').submit();
		}
	});
	
	$j('#tpf_name').bind('keyup focus change',function(){
		tpf_triggerElements($j(this).val());
	});
	
	$j('.tpf_back').click(function(){
		location.reload();
	});
	
	tpf_triggerElements($j('#tpf_name').val());

});

function tpf_triggerElements(val){
	if(val != ''){
		var allowedchars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-";
		var tempval = "";
		for(xx = 0; xx < val.length; xx++)
		{
		     if(allowedchars.indexOf(val.substring(xx,xx+1)) >= 0)
		     		tempval += val.substring(xx,xx+1);
	  }
	  val = tempval;	
	  $j('#tpf_name').val(val);
		if(tpf_wsc(val)){
			code = '"' + val + '"';
		}else{
			code = val;
		}
		
		$j('#tpf_code').show();
		$j('#tpf_code').html('Your shortcode is <b contenteditable="true" disabled="disabled">[tpf:' + code + ']</b>');
		$j('#tpf_submit').removeClass('tpf_btdisabled').removeAttr('disabled');
	}else{
		$j('#tpf_code').hide();
		$j('#tpf_submit').addClass('tpf_btdisabled').attr('disabled', 'disabled');
	}
}

function tpf_wsc(s) {
  return s.indexOf(' ') >= 0;
}
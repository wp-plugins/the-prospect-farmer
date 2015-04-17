<?php
/**
 * Shortcoder include for inserting and editing shortcodes in post and pages
 * v1.2
 **/
 
if ( ! isset( $_GET['TB_iframe'] ) )
	define( 'IFRAME_REQUEST' , true );


// Load WordPress
require_once('../../../wp-load.php');

if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
    wp_die(__('You do not have permission to edit posts.'));

// Load all created shortodes
$tpf_options = get_option('theprospectfarmer_data');

if( empty($tpf_options) )
	die( "Sorry! No Forms to Insert<br/><a href='" . TPF_ADMIN . "' target='_blank'>Create Forms</a>" );
		
?>

<html>
<head>
<title>Forms Available</title>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
<style type="text/css">
body{
	font: 13px Arial, Helvetica, sans-serif;
	padding: 10px;
	background: #f2f2f2;
}
h2{
	font-size: 23px;
	font-weight: normal;
}
h4{
	margin: 0 0 20px 0;
}
hr{
	border-width: 0px;
	margin: 15px 0;
	border-bottom: 1px solid #DFDFDF;
}
.tpf_wrap{
	border: 1px solid #DFDFDF;
	box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}
.tpf_shortcode{
	border-bottom: 1px solid #CCC;
	padding: 0px;
	background: #FFF;
}
.tpf_shortcode_name{
	cursor: pointer;
	padding: 10px;
}
.tpf_shortcode_name:hover{
	background: #fbfbfb;
}
.tpf_params{
	border: 1px solid #DFDFDF;
	background: #F9F9F9;
	margin: 0 -1px -1px;
	padding: 20px;
	display: none;
}
.tpf_insert{
	background: linear-gradient(to bottom, #09C, #0087B4);
	color: #FFF;
	padding: 5px 15px;
	border: 1px solid #006A8D;
	font-weight: bold;
}

.tpf_insert:hover{
	opacity: 0.8;
}
input[type=text], textarea{
	padding: 5px;
	border: 1px solid #CCC;
	width: 120px;
	margin: 0px 25px 10px 0px;
}
.tpf_toggle{
	background: url(images/toggle-arrow.png) no-repeat;
	float: right;
	width: 16px;
	height: 16px;
	opacity: 0.4;
}

.tpf_share_iframe{
	background: #FFFFFF;
	border: 1px solid #dfdfdf;
	-webkit-border-radius: 5px;
	-moz-border-radius: 5px;
	border-radius: 5px;
	-moz-box-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
	-webkit-box-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
	box-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
}
.tpf_credits{
	background: url(images/aw.png) no-repeat;
	padding-left: 23px;
	color: #8B8B8B;
	margin-left: -5px;
	font-size: 13px;
	text-decoration: none;
}
</style>
<script type="text/javascript">
$(document).ready(function(){
	
	$('.tpf_shortcode_name').append('<span class="tpf_toggle"></span>');
	
	$('.tpf_insert').click(function(){
		var params = '';
		var scname = $(this).attr('data-name');
		var sc = '';
		
		$(this).parent().children().find('input[type="text"]').each(function(){
			if($(this).val() != ''){
				attr = $(this).attr('data-param');
				val = $(this).val();
				params += attr + '="' + val + '" ';
			}
		});
		
		if(wsc(scname)){
			name = '"' + scname + '"';
		}else{
			name = scname;
		}
		sc = '[tpf:' + name + ' ' + params + ']';
		
		if( typeof parent.send_to_editor !== undefined ){
			parent.send_to_editor(sc);
		}
		
	});
	
	$('.tpf_share_bar img').mouseenter(function(){
		$this = $(this);
		$('.tpf_share_iframe').remove();
		$('body').append('<iframe class="tpf_share_iframe"></iframe>');
		$('.tpf_share_iframe').css({
			position: 'absolute',
			top: $this.offset()['top'] - $this.attr('data-height') - 15,
			left: $this.offset()['left'] - $this.attr('data-width')/2 ,
			width: $this.attr('data-width'),
			height: $this.attr('data-height'),
		}).attr('src', $this.attr('data-url')).hide().fadeIn();
	
	});
	
	$('.tpf_shortcode_name').click(function(e){
		$('.tpf_params').slideUp();
		if($(this).next('.tpf_params').is(':visible')){
			$(this).next('.tpf_params').slideUp();
		}else{
			$(this).next('.tpf_params').slideDown();
		}
	})
	
});

var tpf_closeiframe = function(){
	$('.tpf_share_iframe').remove();
}

function wsc(s){
	if(s == null)
		return '';
	return s.indexOf(' ') >= 0;
}
</script>
</head>
<body>
<h2><img src="images/theprospectfarmer.png" align="absmiddle" alt="The Prospect Farmer" width="35px"/> Insert shortcode to editor</h2>

<div class="tpf_wrap">
<?php
foreach($tpf_options as $key=>$value){
	if($key != '_version_fix'){
		echo '<div class="tpf_shortcode"><div class="tpf_shortcode_name">' . $key;
		echo '</div>';
		preg_match_all('/%%[^%\s]+%%/', $value['content'], $matches);
		echo '<input type="button" class="tpf_insert cupid-blue" data-name="' . $key . '" value="Insert TPF Form" />';
		echo '</div>';
	}
}
?>
</div>

<p align="center"><a href="http://www.theprospectfarmer.com/" target="_blank">The Prospect Farmer</a></p>

</body>
</html>
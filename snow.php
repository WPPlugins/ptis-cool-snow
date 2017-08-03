<?php
/*
Plugin Name: Pti's cool snow
Plugin URI: http://www.ptipti.ru/snow/
Description: Snows on your Wordpress Blog (based on XSnow written by Nikolaus Klepp)
Version: 1.0
Author: Pti_the_Leader
Author URI: http://www.ptipti.ru/
*/

if (is_admin()) require dirname(__FILE__).'/admin.php';

add_action('wp_footer', 'pti_cool_snow');

function pti_cool_snow() {
if (get_option('detect_winter') && (floor(date('n') / 3) % 4)) return;
?>
<script type="text/javascript">
window.onerror = null;

var ns6 = (!document.all && document.getElementById);
var ie4 = (document.all);
var ns4 = (document.layers);
var BV  = (parseFloat(navigator.appVersion.indexOf("MSIE")>0 ?
			navigator.appVersion.split(";")[1].substr(6)	 :
			navigator.appVersion));
var op  = (navigator.userAgent.indexOf('Opera')!=-1 && BV>=4);

var pageWidth  = 0;
var pageHeight = 0;
var pageOffX   = 0;
var pageOffY   = 0;


var flakeImageDir   = '<?php echo home_url().'/'.PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)).'/images/'; ?>';

var infoLayer  = false;

var flakes = <?php echo get_option('flakes_quantity'); ?>;
var flake_speed_PperS =  <?php echo get_option('flakes_normal_speed'); ?>;
var storm_speed_PperS = <?php echo get_option('flakes_storm_speed'); ?>;

var flake_TX          = 1.0;
var flake_XperY		  = 2.0;

var storm_duration_S  = 5.0;
var storm_lag_S       = 40.0;
var storm_YperX       = 1/3.0;

var disappear_margin = 50;

var refresh_FperS = 25;
var refresh 	  = 1000/refresh_FperS;

var flake_speed 	= 0;
var storm_speed 	= 0;
var storm_YperX_current = storm_YperX;
var storm_v_sin     = 0;
var storm_v_cos     = 1;
var storm_direction = 0;
var storm_id    	= 0;

var storm_blowing	= 1;

var flake    = new Array(flakes);
var flakeX   = new Array(flakes);
var flakeY   = new Array(flakes);
var flakeSX  = new Array(flakes);
var flakeVX  = new Array(flakes);
var flakeVY	 = new Array(flakes);
var flakeVIS = new Array(flakes);
var flakeDX  = 0;
var flakeDY  = 0;


var timer_id    = 0;
var timer_sum   = refresh;
var timer_count = 1;

var flake_visible = op;
var flake_id	  = 0;

var kFlakeImages = 7;
var flake_images = new Array(kFlakeImages);
for(i=0;i<kFlakeImages;i++) {
    flake_images[i] = new Image();
	flake_images[i].src = flakeImageDir+'snow'+i+'.gif';
}

function rebuild_speed_and_timer() {
	var old = refresh_FperS;
	refresh = Math.floor(timer_sum/timer_count*2)+10;
	refresh_FperS = Math.floor(1000/refresh);

	flake_speed = flake_speed_PperS/refresh_FperS;
	storm_speed = storm_speed_PperS/refresh_FperS;

	if (timer_id) window.clearInterval(timer_id);
	timer_id = window.setInterval('move_snow_main()',refresh);

	if(infoLayer){
		if (old!=refresh_FperS) write_to_layer(infoLayer,refresh_FperS+'f/s');
	}

	if (old != refresh_FperS) {
		var ratio = old/refresh_FperS;
		for (i=0; i<flakes; i++) {
			flakeSX[i] *= ratio;
			flakeVX[i] *= ratio;
			flakeVY[i] *= ratio;
		}
	}

	timer_count /= 2;
	timer_sum   /= 2;
}

function make_flake_visible_proc() {
	window.clearInterval(flake_id);
	flake_visible = true;
}

function storm_proc() {
	window.clearInterval(storm_id);

	if (storm_blowing == 0) {
		storm_blowing = (Math.random()<0.5) ? -1 : 1 ;
		storm_YperX_current = Math.random()*storm_YperX;

		var storm_theta = Math.atan(storm_YperX_current);
		storm_v_cos = Math.cos(storm_theta);
		storm_v_sin = Math.sin(storm_theta);
		storm_id = window.setInterval('storm_proc()',storm_duration_S*1000.0);

	} else {
		storm_blowing *= 0.7;
		if ((Math.abs(storm_blowing)<0.05) || (!flake_visible)) {
			storm_blowing = 0;
			storm_id = window.setInterval('storm_proc()',storm_lag_S*1000.0);
		} else {
			storm_id = window.setInterval('storm_proc()',500.0);
		}
	}

	flakeDX = storm_v_cos*storm_speed*storm_blowing;
	flakeDY = Math.abs(storm_v_sin*storm_speed*storm_blowing);
}


function init_snow() {
	for (var i=0; i<flakes; i++) {
		flake[i]    = get_layer_by_name('flake'+i);
		flakeX[i]   = Math.random()*pageWidth;
		flakeY[i]   = Math.random()*pageHeight;
		flakeSX[i]  = 0;
		flakeVX[i]  = 0;
		flakeVIS[i] = flake_visible;
		flakeVY[i]  = 1;
	}
}

function get_layer_by_name(id) {
	if (op) 	{	return document.getElementById(id);	}
	if (ns6)	{	return document.getElementById(id);	}
	if (ie4)	{	return document.all[id];			}
	if (ns4)	{	return document.layers[id];			}
}

function move_snow_main() {
	var beginn = new Date().getMilliseconds();
	move_snow();
	var ende = new Date().getMilliseconds();
	var diff = (beginn>ende?1000+ende-beginn:ende-beginn);
	timer_sum   += diff;
	timer_count ++;
	if (timer_count>10) {
		rebuild_speed_and_timer();
	}
}

function move_snow() {
	for (var i=0; i<flakes; i++) {
		flakeX[i] += flakeVX[i] + flakeDX;
		flakeY[i] += flakeVY[i] + flakeDY;
		if (flakeY[i]>pageHeight-disappear_margin || flakeY[i]<getBodyScrollTop()-disappear_margin) {
			flakeX[i]  = Math.random()*pageWidth;
			flakeY[i]  = getBodyScrollTop();
			flakeVY[i] = flake_speed+Math.random()*flake_speed;
			if (Math.random()<0.1) flakeVY[i] *= 2.0;
			if (flake_visible) flakeVIS[i] = true;
		}

		flakeSX[i] --;
		if (flakeSX[i] <= 0) {
			flakeSX[i] = Math.random()*refresh_FperS*flake_TX;
			flakeVX[i] = (2.0*Math.random()-1.0)*flake_XperY*flake_speed;
		}

		if (flakeX[i]<-disappear_margin)
		    flakeX[i] += pageWidth;
		if (flakeX[i]>=(pageWidth-disappear_margin))
			flakeX[i] -= pageWidth;

		move_to(flake[i],flakeX[i],flakeY[i],flakeVIS[i]);
	}
}

function move_to(obj, x, y, visible) {
	if (visible) {
		if (op) {
			obj.style.left		= x+"px";
			obj.style.top 		= y+"px";
			obj.style.display 	= "block";
		} else if (ie4) {
			obj.style.pixelLeft = x;
			obj.style.pixelTop  = y;
			obj.style.visibility= "visible";
		} else if (ns4) {
			obj.left 			= x;
			obj.top				= y;
			obj.visibility 		= "show";
		} else if (ns6) {
			obj.style.left 		= x+"px";
			obj.style.top		= y+"px";
			obj.style.display 	= "block";
		}
	} else {
		if (ie4 || op) { obj.style.visibility = "hidden";}
		if (ns4) { obj.visibility 		= "hide";}
		if (ns6) { obj.style.display 	= "none";}
	}
}

function write_to_layer(layer,txt) {
	if (op) {
		document.getElementById(layer).innerHTML = txt;
	} else if (ie4) {
		document.all[layer].innerHTML = txt;
	} else if (ns4) {
		document[layer].document.write(txt);
		document[layer].document.close();
	} else if (ns6) {
		var over = document.getElementById(layer);
		var range = document.createRange();
		range.setStartBefore(over);
		var domfrag = range.createContextualFragment(txt);
		while (over.hasChildNodes()) {
			over.removeChild(over.lastChild);
		}
		over.appendChild(domfrag);
	}
}

function get_page_dimension() {
	if(op) {
		pageOffX   = self.pageXOffset;
		pageOffY   = self.pageYOffset;
		pageWidth  = innerWidth  + pageOffX;
		pageHeight = innerHeight + pageOffY;
	} else if(ns6) {
		pageOffX   = scrollX;
		pageOffY   = scrollY;
		pageWidth  = innerWidth  + pageOffX;
		pageHeight = innerHeight + pageOffY;
	} else if(ns4) {
		pageOffX   = window.pageXOffset;
		pageOffY   = window.pageYOffset;
		pageWidth  = innerWidth  + pageOffX;
		pageHeight = innerHeight + pageOffY;
	} else if(ie4) {
		pageOffX   = document.body.scrollLeft;
		pageOffY   = document.body.scrollTop;
		pageWidth  = document.body.clientWidth  + pageOffX;
		pageHeight = document.body.clientHeight + pageOffY;
	}
}

function getBodyScrollTop() {
  return self.pageYOffset || (document.documentElement && document.documentElement.scrollTop) || (document.body && document.body.scrollTop);
}

function start() {
	var a = '';
	
	for (var i=0; i<flakes; i++) {
		a += '<div id="flake'+i+'" '
		  +  'style="position: absolute; '
		  +  'left:-1px; top:-1px; z-index:1000;">'
		  +  '<img src="' +flake_images[i % kFlakeImages].src+ '"></div>\n';
	}

	document.write(a);
	window.setInterval('get_page_dimension()',1000);
	get_page_dimension();

	init_snow();

	rebuild_speed_and_timer(refresh);

	timer_id = window.setInterval('move_snow_main()',refresh);
	storm_id = window.setInterval('storm_proc()',1800);
	flake_id = window.setInterval('make_flake_visible_proc()',2000);
}

start();
</script>
<?php } ?>
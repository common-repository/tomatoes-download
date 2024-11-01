/**
 * @detail
 * Additional function to handle content
 * http://zourbuth.com/
 */

(function($) { spDialog = {

	init : function(){
		$('.totalControls').closest(".widget-inside").addClass("totalWidgetBg");		
		
		$('ul.nav-tabs li').live("click", function(){
			spDialog.tabs(this)
		});
		
		$("a.addFile").live("click", function(){
			spDialog.addFile(this); return false;
		});
		
		$("a.addImage").live("click", function(){
			spDialog.addImages(this); return false;
		});
		
		$("a.removeImage").live("click", function(){
			spDialog.removeImage(this); return false;
		});
	},
	
	tabs : function(tab){
		var t, i, c;
		
		t = $(tab);
		i = t.index();
		c = t.parent("ul").next().children("li").eq(i);
		t.addClass('active').siblings("li").removeClass('active');
		$(c).show().addClass('active').siblings().hide().removeClass('active');
		t.parent("ul").find("input").val(0);
		$('input', t).val(1);
	},
	
	addImages : function(el){
		var g, u, i, a;
		
		g = $(el).siblings('img');
		i = $(el).siblings('input');
		a = $(el).siblings('a');
		
		
		tb_show('Select Image/Icon Title', 'media-upload.php?post_id=0&type=image&TB_iframe=true');	
		
		window.send_to_editor = function(html) {
			
			u = $('img',html).attr('src');
			
			if ( u === undefined || typeof( u ) == "undefined" ) 
				u = $(html).attr('src');
			
			g.attr("src", u).slideDown();
			i.val(u);
			a.addClass("showRemove").removeClass("hideRemove");
			tb_remove();
		};
		return false;
	},
	
	addFile : function(el){
		var g, h, t, i, a;
		g = $(el).prev();
		i = $(el).siblings('input');
		a = $(el).siblings("a");
		def = wp.media.editor.send.attachment;
		
		wp.media.editor.send.attachment = function(props, attachment){
			h = attachment.url;
			t = attachment.url.split('/').pop();

			g.text(t);
			g.attr("href", h).fadeIn();
			
			i.val(h);
			
			a.removeClass("hidden");
			
			wp.media.editor.send.attachment = def;
		}

		wp.media.editor.open( $(this) );
		return false;
	},
	
	removeImage : function(el){
		var t = $(el);
		
		t.next().val('');
		t.siblings('img').slideUp();
		t.siblings('a.filelink').attr("href", "").text("");
		t.addClass('hidden');
		t.fadeOut();
		return false;
	}	
};

$(document).ready(function(){spDialog.init();});
})(jQuery);
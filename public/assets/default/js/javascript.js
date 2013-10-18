$(document).ready(function() {
	$('pre code').each(function(i, e) {hljs.highlightBlock(e)});

	$('.main-sidebar .box').scrollspy({
		min: $('.main-sidebar .box').offset().top - 20,
		max: $(document).height(),
		onEnter: function(element, position) {
			$(".main-sidebar .box").addClass('fixed');
		},
		onLeave: function(element, position) {
			$(".main-sidebar .box").removeClass('fixed');
		}
	});
});

// Redirect function for hash change
$(window).on('hashchange', function() {
	if (window.location.hash.substr(0,10) == '#redirect-')
	{
		new_url = window.location.hash.substr(10);
		window.location.hash = '#_';
		uri = window.location.pathname.split('/')[1];
		url = window.location.protocol + "//" + window.location.host + '/' + uri;
		window.location.href = url + new_url;
	}
});

/*
	To test scroll areas:
	$('body').append('<div style="position:absolute;top:' + start_position + 'px;left:350px;width:100%;height:' + (end_position - start_position) + 'px;background-color:' + "#"+((1<<24)*Math.random()|0).toString(16) + '">' + hash + '</div>');
*/

$(window).load(function() {
	set_anchors();
});

$(window).resize(function() {
	$(this).unbind('.scrollspy');
	set_anchors();
});

function set_anchors()
{
	var anchors = $('.content a[name]');
	anchors.each(function(i) {
		var sidebar = $('.main-sidebar .box');
		var start_position = $(this).position().top; // Current position
		var end_position = (i + 1 < anchors.length ? anchors.eq(i+1).position().top : $(document).height()); // Get position for next element
		var hash = $(this).attr('name');
		var link = $('.main-sidebar a[href="' + '#' + hash + '"]'); // Get sidebar link

		// Auto position of the sidebar
		var ul = link.parent().parent();
		ul.unbind(".anchorEvents");
		ul.bind("transitionstart.anchorEvents webkitTransitionStart.anchorEvents oTransitionStart.anchorEvents MSTransitionStart.anchorEvents", function(){
				sidebar.css({overflowY: "hidden"});
		});
		ul.bind("transitionend.anchorEvents webkitTransitionEnd.anchorEvents oTransitionEnd.anchorEvents MSTransitionEnd.anchorEvents", function(){
				var top = link.position().top;
				var bottom = top + link.height();
				var scroll_pos = sidebar.scrollTop();
				var view_bottom = scroll_pos + sidebar.height();
				if (top < scroll_pos || bottom > view_bottom)
				{
					sidebar.stop();
					sidebar.animate({ scrollTop: top});
				}
				sidebar.css({overflowY: "scroll"});
		});

		// If this is the first element, make a detection for the header
		if (i == 0)
			set_scroll_trigger($(this), link.parents('li:eq(-1)').children('a:first'), 0, start_position, '');

		set_scroll_trigger($(this), link, start_position, end_position, hash);
	});
}

function set_hash(hash)
{
	if(history.pushState) {
		history.pushState(null, null, '#' + hash);
	}
	else
	{
		window.location.hash = hash;
	}
}

function set_scroll_trigger(anchor, link, min_pos, max_pos, hash)
{
	var li = link.parent();
	var ul = li.parent();

	anchor.scrollspy({
		min: min_pos,
		max: max_pos,
		onEnter: function(element, position) {
			if (!link.position()) return;

			// Collapse other UL, expand parent ul
			$('.main-sidebar ul').removeClass('expanded')
			if (ul.hasClass("collapsed")) ul.addClass('expanded');

			// Expand children
			li.children('ul').addClass('expanded');

			li.addClass('active');

			set_hash(hash);
		},
		onLeave: function(element, position) {
			if (!link.position()) return;

			li.removeClass('active');
		}
	});
}

// Overwrite json highlight to allow {...} syntax
hljs.LANGUAGES.json = function (a) {
    var e = {
        literal: "true false null"
    };
    var d = [a.QSM, a.CNM];
    var c = {
        cN: "value",
        e: ",",
        eW: true,
        eE: true,
        c: d,
        k: e
    };
    var b = {
        b: "{",
        e: "}",
        c: [{
            cN: "attribute",
            b: '\\s*"',
            e: '"\\s*:\\s*',
            eB: true,
            eE: true,
            c: [a.BE],
            i: "\\n",
            starts: c
        },
        {
            cN: "attribute",
	      	b: '...'
        }],
        i: "\\S"
    };
    var f = {
        b: "\\[",
        e: "\\]",
        c: [a.inherit(c, {
            cN: null
        })],
        i: "\\S"
    };
    d.splice(d.length, 0, b, f);
    return {
        c: d,
        k: e,
        i: "\\S"
    }
}(hljs);
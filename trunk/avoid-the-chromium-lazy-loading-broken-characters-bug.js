let viewHeight = 0;
let nowTop = 0;
let elems = [];
let updated = [];
let fontweights = [];

jQuery(()=>{
	viewHeight = jQuery(window).innerHeight();
	elems = jQuery("b.gaclltb_dummy");
	elems = elems.sort((a,b)=>{
		return jQuery(a).offset().top - jQuery(b).offset().top
	});
	updated = Array(elems.length);
	updated.fill(false);
	for(let i = 0; i < elems.length; i++) {
		fontweights[i] = jQuery(elems[i]).css('font-weight');
	}
	let nowScrolling = false;
	jQuery(window).scroll(() => {
		if (nowScrolling) {
			return;
		}
		nowScrolling = true;
		nowTop = jQuery(window).scrollTop();
		if(nowTop < viewHeight) {
			nowScrolling = false;
			return;
		}
		let refreshed = false;
		for(let i = 0; i < elems.length; i++) {
			const elem = jQuery(elems[i]);
			const elemTop = elem.offset().top;
			const elemHtml = elem.html();
			if(nowTop + viewHeight < elemTop) {
				break;
			} else if(nowTop > elemTop + viewHeight * 0.5) {
				continue;
			} else if(false
				|| elemHtml.indexOf('<img') !== -1
				|| elemHtml.indexOf('<script') !== -1
				|| elemHtml.indexOf('<iframe') !== -1
				|| elemHtml.indexOf('<style') !== -1
			) {
				continue;
			} else if(!updated[i] && elem.css('font-weight') != 400) {
				refreshed = true;
				const fontweight = fontweights[i];
				setTimeout(()=>{
					elem.css('font-weight', '400');
					setTimeout(() => elem.css('font-weight', fontweight), 500);
				}, 1);
				updated[i] = true;
			}
		}
		if (refreshed) {
			setTimeout(()=>{
				nowScrolling = false;
				updated.fill(false)
			}, 1000);
		} else {
			updated.fill(false)
			nowScrolling = false;
		}
	});
});

jQuery(document).ready(function ($) {
	//Change position for drop down
	$(".menu li").on("mouseenter mouseleave", function (e) {
		if ($(".sub-menu", this).length) {
			var elm = $("ul:first", this);
			var off = elm.offset();
			var l = off.left;
			var w = elm.width();
			var docH = $(".container").height();
			var docW = $(".container").width();

			var isEntirelyVisible = l + w <= docW;

			if (!isEntirelyVisible) {
				$(this).addClass("edge");
			} else {
				$(this).removeClass("edge");
			}
		}
	});
	$(document.documentElement).removeClass("no-js");
	$(document.documentElement).addClass("js");
	$(document.documentElement).addClass("loaded");

	const $mainMenuItems = $(".main-nav ul li");

	/**
	 * Position submenus that flow offscreen to the left of their parent.
	 */

	$mainMenuItems.on("mouseover focus", function (e) {
		e.stopPropagation();
		const $menuItem = $(e.currentTarget);
		const $subMenu = $menuItem.children("ul").first();

		if ($subMenu.length) {
			const isOffScreen =
				$subMenu.offset().left + $subMenu.width() > $(window).width();

			if (isOffScreen) {
				$subMenu.css({
					left: "-100%",
				});
			}
		}
	});

	$(window).on("resize", function () {
		$mainMenuItems.removeAttr("style");
	});

	//Mobile menu

	$(".main-nav__toggle").on("click", function () {
		$("body").toggleClass("main-nav__open");
	});

	const anchorsWithChildren = document.querySelectorAll(
		".main-nav .menu-item-has-children > a, .main-nav .page_item_has_children > a"
	);
	const anchorsWithChildrenSubmenu = document.querySelectorAll(
		".main-nav .menu-item-has-children > ul, .main-nav .page_item_has_children > ul"
	);

	// Add dropdown buttons for mobile menu submenu
	function mobileMenuDropdownIcons() {
		for (const a of anchorsWithChildren) {
			let expandBtn = document.createElement("button");
			expandBtn.classList.add("expand");

			let expandBtnLabel = document.createElement("SPAN");
			expandBtnLabel.innerText = "Open Submenu";
			expandBtnLabel.classList.add("sr-only");
			expandBtn.appendChild(expandBtnLabel);
			a.after(expandBtn);
		}
	}

	mobileMenuDropdownIcons();

	$(".main-nav .expand").on("click", function () {
		$(this).parent().toggleClass("expanded");
		$(this).next(".sub-menu").toggle("medium");
	});
});

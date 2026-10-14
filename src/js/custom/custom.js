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

	// Only bind the click handler once, even if this file is enqueued twice.
	if (!$(".site-header__toggle").data("initialized")) {
		$(".site-header__toggle").data("initialized", true);

		$(".site-header__toggle").on("click", function () {
			console.log("Hamburger menu clicked");
			$("body").toggleClass("site-header__nav__open");
			console.log("Body class after toggle:", $("body").attr("class"));
			$(".site-header__nav").toggle();

			// Make the main nav visible when the hamburger menu is clicked
			if ($("body").hasClass("site-header__nav__open")) {
				$(".site-header__nav").css("display", "block");
			} else {
				$(".site-header__nav").css("display", "none");
			}
		});
	}

	const anchorsWithChildren = document.querySelectorAll(
		".site-header__nav .menu-item-has-children > a, .site-header__nav .page_item_has_children > a"
	);
	const anchorsWithChildrenSubmenu = document.querySelectorAll(
		".site-header__nav .menu-item-has-children > ul, .site-header__nav .page_item_has_children > ul"
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

	$(".site-header__nav").off("click", ".expand").on("click", ".expand", function (e) {
		e.preventDefault();
		e.stopPropagation();
		console.log("Expand button clicked");
		$(this).parent().toggleClass("expanded");
		console.log("Parent class after toggle:", $(this).parent().attr("class"));
		$(this).next(".sub-menu").toggle("medium", function () {
			console.log("Sub-menu visibility:", $(this).css("display"));
		});
	});

	// Meetups state filter: populate the state select from the server.
	$.ajax({
		url: ajax_url.ajax_url,
		method: "GET",
		data: { action: "get_all_meetup_state_terms" },
		success: function (response) {
			let stateSelect = $("#state-select");
			stateSelect.empty();
			stateSelect.append('<option value="">Select a state...</option>');
			stateSelect.append('<option value="all">Show All</option>');

			if (response.success) {
				$.each(response.data, function (index, state) {
					stateSelect.append('<option value="' + state + '">' + state + "</option>");
				});
			}
		},
		error: function (xhr, status, error) {
			console.log("AJAX Error:", error);
		},
	});

	$("#state-select").on("change", function () {
		var selectedState = $(this).val();

		if (selectedState === "all") {
			window.location.href = "/meetups/";
		} else if (selectedState) {
			window.location.href = "/meetups/?state=" + encodeURIComponent(selectedState);
		}
	});
});

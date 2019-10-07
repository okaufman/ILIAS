<?php


function show_tooltip_for_button() : string {
	global $DIC;
	$factory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();
	$tooltip = $factory->popover()->tooltip([$factory->legacy('Hello World!')]);
	$button = $factory->button()
		->standard('Hover Me!', '#')
		->withOnHover($tooltip->getShowSignal());
	$tooltip2 = $factory->popover()->tooltip([$factory->legacy('Foo <i>bar</i> with some <b>HTML</b>!')]);
	$button2 = $factory->button()
		->standard('Click Me!', '#')
		->withOnClick($tooltip2->getShowSignal());
	return $renderer->render([$tooltip, $button, new \ILIAS\UI\Implementation\Component\Legacy\Legacy('&nbsp;'), $tooltip2, $button2]);
}
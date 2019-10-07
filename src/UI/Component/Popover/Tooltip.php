<?php

namespace ILIAS\UI\Component\Popover;

/**
 * A listing popover renders multiple items as a list.
 *
 * @package ILIAS\UI\Component\Popover
 */
interface Tooltip extends Popover {

	/**
	 * Get the list items of this popover.
	 *
	 * @return string
	 */
	public function getContent();
}
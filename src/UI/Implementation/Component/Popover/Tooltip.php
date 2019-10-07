<?php

namespace ILIAS\UI\Implementation\Component\Popover;


use ILIAS\UI\Component;


class Tooltip extends Popover implements Component\Popover\Tooltip {

	protected $content;

	public function __construct($content, SignalGeneratorInterface $signal_generator) {
		parent::__construct($signal_generator);
		$content = $this->toArray($content);
		$types = array( Component\Component::class );
		$this->checkArgListElements('content', $content, $types);
		$this->content = $content;
	}
	/**
	 *
	 */
	public function getContent(){
		return  $this->content;
	}
}
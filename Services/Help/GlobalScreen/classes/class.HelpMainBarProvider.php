<?php

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\TypeInformationCollection;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ILIAS\UI\Component\Symbol\Icon\Standard;
use ILIAS\UI\Implementation\Component\Link\Bulky;

class HelpMainBarProvider extends AbstractStaticMainMenuProvider implements StaticMainMenuProvider
{
    use ilHelpDisplayed;

    /**
     * @inheritDoc
     */
    public function getStaticTopItems() : array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getStaticSubItems() : array
    {
        $title = $this->dic->language()->txt("help");
        $icon_path = \ilUtil::getImagePath("outlined/" . "icon_hlps" . ".svg");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom($icon_path, "help");

        if ($this->showHelpTool()) {
            return [
                $this->mainmenu->topLinkItem($this->if->identifier('mm_help'))
                               ->withPosition(100)
                               ->withTitle($title)
                               ->withSymbol($icon)
                               ->addComponentDecorator(static function (
                                   \ILIAS\UI\Component\Component $c
                               ) : \ILIAS\UI\Component\Component {
                                   if ($c instanceof Bulky) {
                                       return $c->withAdditionalOnLoadCode(static function (string $id) : string {
                                           return "$('#$id').on('click', function() {
                                            console.log('trigger help slate');
                                            $('body').trigger('il-help-toggle-slate');
                                     })";
                                       });
                                   }
                               })
            ];
        }
        return [];
    }
}

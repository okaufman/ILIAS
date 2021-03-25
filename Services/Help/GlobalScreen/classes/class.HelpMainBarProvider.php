<?php

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider;

class HelpMainBarProvider extends AbstractStaticMainMenuProvider implements StaticMainMenuProvider
{
    use ilHelpDisplayed;

    /**
     * @inheritDoc
     */
    public function getStaticTopItems() : array
    {
        $l = function (string $content) {
            return $this->dic->ui()->factory()->legacy($content);
        };
        $icon_path = \ilUtil::getImagePath("outlined/" . "icon_hlps" . ".svg");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom($icon_path, "help");
        if ($this->showHelpTool()) {
            $item = $this->mainmenu->complex($this->if->identifier('mm_help'))
                                   ->withSymbol($icon)
                                   ->withPosition(100)
                                   ->withTitle("Help")
                                   ->withSupportsAsynchronousLoading(false)
                                   ->withContentWrapper(function () use ($l) {
                                       return $l($this->getHelpContent());
                                   });
            return [$item];
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getStaticSubItems() : array
    {
        return [];
    }

    /**
     * help
     * @param int $ref_id
     * @return string
     */
    private function getHelpContent() : string
    {
        global $DIC;
        $ctrl = $DIC->ctrl();
        $main_tpl = $DIC->ui()->mainTemplate();
        /** @var ilHelpGUI $help_gui */
        $help_gui = $DIC["ilHelp"];
        $help_gui->initHelp($main_tpl, $ctrl->getLinkTargetByClass("ilhelpgui", "", "", true));
        $html = "";
        if ((defined("OH_REF_ID") && OH_REF_ID > 0) || DEVMODE == 1) {
            $html = "<div class='ilHighlighted small'>Screen ID: " . $help_gui->getScreenId() . "</div>";
        }
        $html .= "<div id='ilHelpPanel'>&nbsp;</div>";
        return $html;
    }
}

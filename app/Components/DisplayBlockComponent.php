<?php

declare(strict_types=1);

namespace App\Components;

class DisplayBlockComponent extends \Nette\Application\UI\Control {

    /** @var \Contributte\Translation\Translator */
    private $translator;

    public function __construct(\Contributte\Translation\Translator $translator) {
        $this->translator = $translator;
    }

    public function render($imagePath, $title, $text, $linkText = null, $linkTarget = null, $mentorText = null) {
        $this->template->imagePath = $imagePath;
        $this->template->title = $this->translator->translate($title);
        $this->template->text = $this->translator->translate($text);
        $this->template->linkText = $linkText;
        $this->template->linkTarget = $linkTarget;
        $this->template->mentorText = $mentorText;
	    $this->template->render(__DIR__ . '/DisplayBlockComponent.latte');
    }

}

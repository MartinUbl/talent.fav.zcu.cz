<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

class BasePresenter extends Nette\Application\UI\Presenter
{
    /** @var \Contributte\Translation\Translator @inject */
    public $translator;

    /** @var \Contributte\Translation\LocalesResolvers\Session @inject */
	public $translatorSessionResolver;

    public function startup() {
        parent::startup();

        $this->template->lang = $this->translator->getLocale();
    }

	public function handleChangeLocale(string $lang): void
	{
		$this->translatorSessionResolver->setLocale($lang);
		$this->redirect('this');
	}

    public function handleLogout() {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect("this");
        }

        $this->getUser()->logout();
        $this->redirect("this");
    }

    public function createPdfWithProject($project, $showInline = false) {
        $template = $this->createTemplate();
        $template->setFile(__DIR__ . "/templates/_pdf/proposal.latte");
        $template->data = json_decode($project->data, true);
        $template->tr_scopes = \App\Model\Enum\ProjectScope::getTranslatedEnum(function($tr) { return $this->translator->translate($tr); });
        $template->tr_lengths = \App\Model\Enum\ProjectLength::getTranslatedEnum(function($tr, $d) { return $this->translator->translate($tr, $d); });
        $template->tr_fincategories = \App\Model\Enum\FinanceCategory::getTranslatedEnum(function($tr) { return $this->translator->translate($tr); });
        $template->tr_outtypes = \App\Model\Enum\OutputType::getTranslatedEnum(function($tr) { return $this->translator->translate($tr); });
        $template->cur_date = (new \DateTime())->format('j. n. Y, H:i');
        $template->cur_author = $this->getUser()->getIdentity()->fullname;
        $template->res = [
            'gtl_logo' => file_get_contents(__DIR__ . "/templates/_pdf/_res_gtl_logo.base64"),
            'fav_logo' => file_get_contents(__DIR__ . "/templates/_pdf/_res_favlogo.svg")
        ];

        $pdf = new \Contributte\PdfResponse\PdfResponse($template);

        $mpdf = $pdf->getMPDF();
        $mpdf->AddFontDirectory(__DIR__."/templates/_pdf/");
        $mpdf->fontdata["Roboto Condensed"]["R"] = "roboto-condensed-regular.ttf";
        $mpdf->SetFont("Roboto Condensed");
        $pdf->setMPDF($mpdf);

        $pdf->setSaveMode($showInline ? \Contributte\PdfResponse\PdfResponse::INLINE : \Contributte\PdfResponse\PdfResponse::DOWNLOAD);
        $pdf->documentTitle = "grabthelab";

        $this->sendResponse($pdf);
    }
}

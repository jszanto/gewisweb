<?php

namespace Education\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\I18n\Translator\Translator;

class Upload extends Form
{

    public function __construct(Translator $translator)
    {
        parent::__construct();

        $this->add(array(
            'name' => 'upload',
            'type' => 'file',
            'option' => array(
                'label' => $translator->translate('Exam to upload')
            )
        ));
        $this->get('upload')->setLabel($translator->translate('Exam to upload'));

        $this->add(array(
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => array(
                'value' => $translator->translate('Submit')
            )
        ));

        $this->initFilters();
    }

    protected function initFilters()
    {
    }
}

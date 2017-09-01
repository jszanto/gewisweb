<?php

namespace Education\Form\Fieldset;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use zend\I18n\Translator\TranslatorInterface as Translator;
use Education\Model\Exam as ExamModel;

class Exam extends Fieldset
    implements InputFilterProviderInterface
{

    protected $config;

    public function __construct(Translator $translator)
    {
        parent::__construct('exam');

        $this->add([
            'name' => 'file',
            'type' => 'hidden'
        ]);

        $this->add([
            'name' => 'course',
            'type' => 'text',
            'options' => [
                'label' => $translator->translate('Course code')
            ]
        ]);

        $this->add([
            'name' => 'date',
            'type' => 'date',
            'options' => [
                'label' => $translator->translate('Exam date')
            ]
        ]);

        $this->add([
            'name' => 'examType',
            'type' => 'Zend\Form\Element\Select',
            'options' => [
                'label' => $translator->translate('Type'),
                'value_options' => [
                    ExamModel::EXAM_TYPE_FINAL => $translator->translate('Final examination'),
                    ExamModel::EXAM_TYPE_INTERMEDIATE_TEST => $translator->translate('Intermediate test'),
                    ExamModel::EXAM_TYPE_ANSWERS => $translator->translate('Exam answers'),
                    ExamModel::EXAM_TYPE_OTHER => $translator->translate('Other'),
                ],
            ]
        ]);

        $this->add([
            'type' => 'Zend\Form\Element\Select',
            'name' => 'language',
            'options' => [
                'label' => $translator->translate('Language'),
                'value_options' => [
                    ExamModel::EXAM_LANGUAGE_ENGLISH => $translator->translate('English'),
                    ExamModel::EXAM_LANGUAGE_DUTCH => $translator->translate('Dutch'),
                ],
            ],
        ]);
    }

    /**
     * Set the configuration.
     *
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = $config['education_temp'];
    }

    public function getInputFilterSpecification()
    {
        $dir = $this->config['upload_exam_dir'];
        return [
            'file' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => 'regex',
                        'options' => [
                            'pattern' => '/^[a-zA-Z0-9_ ,.-]+\.pdf$/'
                        ]
                    ],
                    [
                        'name' => 'callback',
                        'options' => [
                            'callback' => function ($value) use ($dir) {
                                $validator = new \Zend\Validator\File\Exists([
                                    'directory' => $dir
                                ]);
                                return $validator->isValid($value);
                            }
                        ]
                    ]
                ]
            ],

            'course' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => 'string_length',
                        'options' => [
                            'min' => 5,
                            'max' => 6
                        ]
                    ],
                    ['name' => 'alnum']
                ],
                'filters' => [
                    ['name' => 'string_to_upper']
                ]
            ],

            'date' => [
                'required' => true,
                'validators' => [
                    ['name' => 'date']
                ]
            ]
        ];
    }

}

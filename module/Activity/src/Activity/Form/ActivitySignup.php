<?php

namespace Activity\Form;

use Zend\Form\Form;
//input filter
use Zend\InputFilter\InputFilterInterface;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;
use Zend\InputFilter\InputFilterProviderInterface;

class ActivitySignup extends Form implements InputFilterProviderInterface
{
    
    public function __construct()
    {
        parent::__construct('activitysignup');
        $this->setAttribute('method', 'post');
        $this->setHydrator(new ClassMethodsHydrator(false))
            ->setObject(new \Activity\Model\ActivitySignup());
        
        $this->add([
            'name' => 'submit',
            'attributes' => [
                'type' => 'submit',
                'value' => 'Subscribe',
            ],
        ]);
    }

    /**
     * Add every field in $fields to the form.
     * 
     * @param ActivityField $fields
     */
    public function setFields($fields)
    {
        foreach($fields as $field){
            $this->add($this->createFieldElementArray($field));
        }
    }
    
    /**
     * Apparently, validators are automatically added, so this works. 
     * 
     * @return type array
     */
    public function getInputFilterSpecification()
    {
        return [];
    }
    
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception('Not used');
    }
    
    /**
     * Creates an array of the form element specification for the given $field,
     * to be used by the factory.
     * 
     * @param \Activity\Model\ActivityField $field
     * @return array 
     */
    protected function createFieldElementArray(\Activity\Model\ActivityField $field){
        
        $result = [
            'name' => $field->get('id'),
        ];
        switch($field->get('type')){
            case 0: //'Text'
                $result['type'] = 'Text';
                break;
            case 1: //'Yes/No'
                $result['type'] = 'Zend\Form\Element\Radio';
                $result['options'] = [
                    'value_options' => [
                        '1' => 'Yes',
                        '0' => 'No',
                    ]
                ];
                break;
            case 2: //'Number'
                $result['type'] = 'Zend\Form\Element\Number';
                $result['attributes'] = [
                    'min' => $field->get('minimumValue'),
                    'max' => $field->get('maximumValue'),
                    'step' => '1'
                ];
                break;
            case 3: //'Choice'
                $values = [];
                foreach($field->get('options') as $option){
                    $values[$option->get('id')] = $option->get('value');
                }
                $result['type'] = 'Zend\Form\Element\Select';
                $result['options'] = [
                    'empty_option' => 'Make a choice',
                    'value_options' => $values
                ];
                break;
        }
        return $result;        
    }
}

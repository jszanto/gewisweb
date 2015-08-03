<?php

namespace Decision\Service;

use Application\Service\AbstractAclService;

/**
 * Decision service.
 */
class Decision extends AbstractAclService
{

    /**
     * Get all meetings.
     *
     * @return array Of all meetings
     */
    public function getMeetings()
    {
        if (!$this->isAllowed('list_meetings')) {
            $translator = $this->getTranslator();
            throw new \User\Permissions\NotAllowedException(
                $translator->translate('You are not allowed to list meetings.')
            );
        }

        return $this->getMeetingMapper()->findAll();
    }

    /**
     * Get information about one meeting.
     *
     * @param string $type
     * @param int $number
     *
     * @return Decision\Model\Meeting
     */
    public function getMeeting($type, $number)
    {
        if (!$this->isAllowed('view_meeting')) {
            $translator = $this->getTranslator();
            throw new \User\Permissions\NotAllowedException(
                $translator->translate('You are not allowed to view meetings.')
            );
        }

        return $this->getMeetingMapper()->find($type, $number);
    }

    /**
     * Check if there are notes for a meeting and get the URL if so.
     *
     * @param Decision\Model\Meeting $meeting
     *
     * @return string|null
     */
    public function getMeetingNotes(\Decision\Model\Meeting $meeting)
    {
        $config = $this->getServiceManager()->get('config');
        $config = $config['meeting-notes'];

        $filename = $meeting->getType() . '/' . $meeting->getNumber() . '.pdf';
        $path = $config['upload_dir'] . '/' . $filename;

        if (file_exists($path)) {
            return $config['public_dir'] . '/' . $filename;
        }
        return null;
    }

    /**
     * Upload meeting notes.
     *
     * @param array|Traversable $post
     * @param array|Traversable $files
     *
     * @return boolean If uploading was a success
     */
    public function uploadNotes($post, $files)
    {
        $form = $this->getNotesForm();

        $data = array_merge_recursive($post->toArray(), $files->toArray());

        $form->setData($data);

        if (!$form->isValid()) {
            return false;
        }

        var_dump($form->getData());
        var_dump($form->get('meeting')->getValue());
        ini_set('xdebug.var_display_max_depth', 5);
        var_dump($form->get('meeting')->getValueOptions());
        echo '<br><br><br><br><br><br>';
    }

    /**
     * Search for decisions.
     *
     * @param array|Traversable $data Search data
     *
     * @return array Search results
     */
    public function search($data)
    {
        if (!$this->isAllowed('search')) {
            $translator = $this->getTranslator();
            throw new \User\Permissions\NotAllowedException(
                $translator->translate('You are not allowed to search decisions.')
            );
        }

        $form = $this->getSearchDecisionForm();

        $form->setData($data);

        if (!$form->isValid()) {
            return null;
        }

        $data = $form->getData();

        return $this->getDecisionMapper()->search($data['query']);
    }

    /**
     * Get the Notes form.
     *
     * @return Decision\Form\Notes
     */
    public function getNotesform()
    {
        return $this->sm->get('decision_form_notes');
    }

    /**
     * Get the SearchDecision form.
     *
     * @return Decision\Form\SearchDecision
     */
    public function getSearchDecisionForm()
    {
        return $this->sm->get('decision_form_searchdecision');
    }

    /**
     * Get the meeting mapper.
     *
     * @return Decision\Mapper\Meeting
     */
    public function getMeetingMapper()
    {
        return $this->sm->get('decision_mapper_meeting');
    }

    /**
     * Get the decision mapper.
     *
     * @return Decision\Mapper\Decision
     */
    public function getDecisionMapper()
    {
        return $this->sm->get('decision_mapper_decision');
    }

    /**
     * Get the default resource ID.
     *
     * @return string
     */
    protected function getDefaultResourceId()
    {
        return 'decision';
    }

    /**
     * Get the Acl.
     *
     * @return Zend\Permissions\Acl\Acl
     */
    public function getAcl()
    {
        return $this->sm->get('decision_acl');
    }
}

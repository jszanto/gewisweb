<?php

namespace Education\Oase;

class Service
{

    /**
     * Client.
     *
     * @var Client
     */
    protected $client;

    /**
     * Keywords of W&I studies.
     *
     * @var array
     */
    protected $keywords;

    /**
     * Negative keywords for W&I studies.
     *
     * @var array
     */
    protected $negativeKeywords;

    /**
     * Group ID's for W&I studies.
     *
     * @var array
     */
    protected $groupIds;

    /**
     * Education types for W&I studies.
     *
     * @var array
     */
    protected $educationTypes;


    /**
     * Constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Set the keywords
     *
     * @param array $keywords
     */
    public function setKeywords(array $keywords)
    {
        $this->keywords = $keywords;
    }

    /**
     * Set the negative keywords.
     *
     * @param array $keywords
     */
    public function setNegativeKeywords(array $keywords)
    {
        $this->negativeKeywords = $keywords;
    }

    /**
     * Set the group ID's
     *
     * @param array $groupIds
     */
    public function setGroupIds(array $groupIds)
    {
        $this->groupIds = $groupIds;
    }

    /**
     * Set the education types
     *
     * @param array $educationTypes
     */
    public function setEducationTypes(array $educationTypes)
    {
        $this->educationTypes = $educationTypes;
    }

    /**
     * Check if a given keyword occurs in a string.
     *
     * @param string $keyword
     * @param string $haystack
     *
     * @return boolean If the keyword occurs.
     */
    protected function isSubString($keyword, $haystack)
    {
        return stristr($haystack, $keyword) !== false;
    }

    /**
     * Filter if a given 'doelgroep' is a W&I study.
     *
     * @param SimpleXMLElement $doelgroep
     *
     * @return boolen If it is a W&I study
     */
    protected function filterDoelgroep(\SimpleXMLElement $doelgroep)
    {
        // first do simple checks
        if (!in_array($doelgroep->Opleidingstype, $this->educationTypes)
            || !in_array($doelgroep->GroepscategorieId, $this->groupIds)) {
            return false;
        }
        // do negative checks
        foreach ($this->negativeKeywords as $keyword) {
            if ($this->isSubString($keyword, $doelgroep->Omschrijving)) {
                return false;
            }
        }
        // do positive checks
        foreach ($this->keywords as $keyword) {
            if ($this->isSubString($keyword, $doelgroep->Omschrijving)) {
                return true;
            }
        }
        // if not matched, return false
        return false;
    }

    /**
     * Get ID's of all W&I's studies.
     *
     * @todo figure out good way to define year in 'GeefDoelgroepen'
     *
     * @return array
     */
    public function getStudies()
    {
        $data = $this->client->GeefDoelgroepen('2013', 'NL');

        // convert doelgroepen to array
        $doelgroepen = array();
        foreach ($data->Doelgroep as $doelgroep) {
            $doelgroepen[] = $doelgroep;
        }

        return array_filter($doelgroepen, array($this, 'filterDoelgroep'));
    }
}

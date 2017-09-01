<?php

namespace User\Authentication\Storage;

use Zend\Authentication\Storage,
    User\Model\Session as SessionModel,
    Zend\Http\Header\SetCookie;

class Session extends Storage\Session
{

    /**
     * @var The service manager
     */
    protected $sm;

    /**
     * @var boolean indicating whether we should remember the user
     */
    protected $rememberMe;

    /**
     * Set whether we should remember this session or not.
     *
     * @param int $rememberMe
     */
    public function setRememberMe($rememberMe = 0)
    {
        $this->rememberMe = $rememberMe;
        if ($rememberMe) {
            $this->saveSession($this->session->{$this->member});
        }
    }

    /**
     * Ensure that this session is no longer remembered.
     */
    public function forgetMe()
    {
        $this->rememberMe = false;
    }

    /**
     * Defined by Zend\Authentication\Storage\StorageInterface
     *
     * @return bool
     */
    public function isEmpty()
    {
        if (isset($this->session->{$this->member})) {
            return false;
        }

        return !$this->readDatabaseSession();

    }

    /**
     * Check if there is a session stored in the database and load it when possible.
     *
     * @return bool indicating whether a session was loaded.
     */
    protected function readDatabaseSession()
    {
        $mapper = $this->sm->get('user_mapper_session');
        $request = $this->sm->get('Request');
        $cookies = $request->getHeaders()->get('cookie');
        if (!isset($cookies->SESSID) || !isset($cookies->SECRET)) {
            return false;
        }
        $session = $mapper->find($cookies->SESSID, $cookies->SECRET);
        if ($session === null) {
            return false;
        }
        $this->session->{$this->member} = $session->getUser()->getLidnr();
        return true;
    }

    /**
     * Defined by Zend\Authentication\Storage\StorageInterface
     *
     * @return mixed
     */
    public function read()
    {
        return $this->session->{$this->member};
    }

    /**
     * Defined by Zend\Authentication\Storage\StorageInterface
     *
     * @param  mixed $contents
     * @return void
     */
    public function write($contents)
    {
        $this->session->{$this->member} = $contents;
        if ($this->rememberMe) {
            $this->saveSession($contents);
        }
    }

    /**
     * Store the current session.
     *
     * @param \User\Model\User $user the logged in user
     *
     * @return SessionModel
     */
    public function saveSession($user)
    {
        $mapper = $this->sm->get('user_mapper_session');

        $session = new SessionModel();
        $session->setIp($this->sm->get('user_remoteaddress'));

        $user = $this->sm->get('user_service_user')->detachUser($user);
        $session->setUser($user);
        $session->setSecret($this->generateSecret());
        $session->setCreatedAt(new \DateTime());
        $session->setLastActive(new \DateTime());

        $mapper->persist($session);
        $mapper->flush();

        $this->saveCookie($session);
    }

    /**
     * Defined by Zend\Authentication\Storage\StorageInterface
     *
     * @return void
     */
    public function clear()
    {
        unset($this->session->{$this->member});
        $mapper = $this->sm->get('user_mapper_session');
        $request = $this->sm->get('Request');
        $cookies = $request->getHeaders()->get('cookie');
        if (!isset($cookies->SESSID) || !isset($cookies->SECRET)) {
            return;
        }
        $session = $mapper->find($cookies->SESSID, $cookies->SECRET);
        if ($session !== null) {
            $mapper->remove($session);
            $mapper->flush();
        }
    }

    /**
     * Generates a cryptographically secure random secret
     *
     * @return string The secret
     */
    public function generateSecret()
    {
        return bin2hex(openssl_random_pseudo_bytes(16));
    }


    /**
     * Store the session data as cookies
     * @param SessionModel $session The session to store
     */
    protected function saveCookie($session)
    {
        $sessionId = new SetCookie('SESSID', $session->getId(), time() + 15552000);
        $sessionSecret = new SetCookie('SECRET', $session->getSecret(), time() + 15552000);

        // Use secure cookies in production
        if (APP_ENV === 'production') {
            $sessionId->setSecure(true)->setHttponly(true);
            $sessionSecret->setSecure(true)->setHttponly(true);
        }
        $config = $this->sm->get('config');
        $sessionId->setDomain($config['cookie_domain']);
        $sessionSecret->setDomain($config['cookie_domain']);

        $response = $this->sm->get('Response');
        $response->getHeaders()->addHeader($sessionId);
        $response->getHeaders()->addHeader($sessionSecret);
    }

    public function __construct($sm)
    {
        $this->sm = $sm;
        parent::__construct(null, null, null);
    }
}

<?php

namespace User\Authentication\Adapter;

use Zend\Authentication\Adapter\AdapterInterface,
    Zend\Authentication\Result,
    User\Mapper\User as UserMapper,
    User\Model\User as UserModel;
use Zend\Crypt\Password\Bcrypt;
use Application\Service\Legacy as LegacyService;
class Mapper implements AdapterInterface
{

    /**
     * Mapper.
     *
     * @var UserMapper
     */
    protected $mapper;

    /**
     * Email.
     *
     * @var string
     */
    protected $email;

    /**
     * Password.
     *
     * @var string
     */
    protected $password;

    /**
     * Bcrypt instance.
     *
     * @var Bcrypt
     */
    protected $bcrypt;

    /**
     * Legacy Service
     * (for checking logins against the old database)
     *
     * @var LegacyService
     */
     protected $legacyService;

    /**
     * Constructor.
     *
     * @param Bcrypt $bcrypt
     */
    public function __construct(Bcrypt $bcrypt, LegacyService $legacyService)
    {
        $this->bcrypt = $bcrypt;
        $this->legacyService = $legacyService;
    }

    /**
     * Try to authenticate.
     *
     * @return Result
     */
    public function authenticate()
    {
        $mapper = $this->getMapper();

        $user = $mapper->findByLogin($this->login);

        if (null === $user) {
            return new Result(
                Result::FAILURE_IDENTITY_NOT_FOUND,
                null,
                []
            );
        }
        $mapper->detach($user);

        if (!$this->verifyPassword($this->password, $user->getPassword(), $user)) {
            return new Result(
                Result::FAILURE_CREDENTIAL_INVALID,
                null,
                []
            );
        }

        return new Result(Result::SUCCESS, $user);
    }

    /**
     * Verify a password.
     *
     * @param string $password
     * @param string $hash
     * @param UserModel $user
     *
     * @return boolean
     */
    public function verifyPassword($password, $hash, $user = null)
    {
        if (strlen($hash) === 0) {
            return $this->legacyService->checkPassword($user, $password, $this->bcrypt);
        }

        if ($this->bcrypt->verify($password, $hash)) {
            return true;
        }

        return false;
    }

    /**
     * Set the credentials.
     *
     * @param array $data
     */
    public function setCredentials($data)
    {
        $this->login = $data['login'];
        $this->password = $data['password'];
    }

    /**
     * Get the mapper.
     *
     * @return UserMapper
     */
    public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * Set the mapper.
     *
     * @param UserMapper $mapper
     */
    public function setMapper(UserMapper $mapper)
    {
        $this->mapper = $mapper;
    }
}

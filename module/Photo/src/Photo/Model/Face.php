<?php

namespace Photo\Model;

use Doctrine\ORM\Mapping as ORM;
use Zend\Permissions\Acl\Resource\ResourceInterface;

/**
 * Object defining a face in a Photo. Optionally used by tags.
 *
 * @ORM\Entity
 *
 */
class Face implements ResourceInterface
{

    /**
     * Face ID.
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * The x-coordinate of the lower left corner of the detected face.
     *
     * @ORM\Column(type="integer")
     */
    protected $x;

    /**
     * The y-coordinate of the lower left corner of the detected face.
     *
     * @ORM\Column(type="integer")
     */
    protected $y;

    /**
     * The width the detected face.
     *
     * @ORM\Column(type="integer")
     */
    protected $width;

    /**
     * The height of the detected face.
     *
     * @ORM\Column(type="integer")
     */
    protected $height;

    /**
     * Album in which the photo is.
     *
     * @ORM\ManyToOne(targetEntity="Photo\Model\Photo", inversedBy="faces")
     * @ORM\JoinColumn(name="photo_id",referencedColumnName="id")
     */
    protected $photo;

    /**
     * @return integer id of the Face
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return integer
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * @param integer $x
     */
    public function setX($x)
    {
        $this->x = $x;
    }

    /**
     * @return integer
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * @param integer $y
     */
    public function setY($y)
    {
        $this->y = $y;
    }

    /**
     * @return integer
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param integer $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @return integer
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param integer $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @return \Photo\Model\Photo
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @param \Photo/Model\Photo $photo
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;
    }

    /**
     * Allows constructing a Face from an assoc array
     *
     * @param array $array like ['x'=>x, 'y'=>y, 'w'=>w, 'h' => h]
     * @return \Photo\Model\Photo
     */
    public static function fromArray($array) {
        $face = new Face();
        $face->x = $array['x'];
        $face->y = $array['y'];
        $face->width = $array['w'];
        $face->height = $array['h'];
    }
    /**
     * Returns the string identifier of the Resource
     *
     * @return string
     */
    public function getResourceId()
    {
        return 'face';
    }
}
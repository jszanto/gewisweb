<?php

namespace Photo\Service;

use Application\Service\AbstractService;

/**
 * Face detection service.
 */
class FaceDetection extends AbstractService
{

    /**
     * Populates the photo with face objects, by detecting faces.
     *
     * @param \Photo\Model\Photo $photo
     *
     * @return \Photo\Model\Photo with added detected faces.
     */
    public function populateFaces($photo) {

        $photoPath = $photo->getPath();
        $config = $this->getConfig();
        $faces = array();
        // Detect faces using openCV
        foreach($config['face_detect']['haarcascades'] as $haarCascade) {
            $faces = array_merge($faces, face_detect($photoPath, $config['face_detect']['haarcascades_dir'] . $haarCascade));
        }

        // Filter based on color

        foreach($faces as $face) {
            if(FaceColorCheck($photoPath, $face)) {
                $photo->addFace($face);
            }
        }


        return $photo;
    }

    public function faceColorCheck($photoPath, $detectedFace) {

    }
}
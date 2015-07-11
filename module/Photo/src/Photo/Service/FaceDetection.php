<?php

namespace Photo\Service;

use Application\Service\AbstractService;
use Imagick;
use Photo\Model\Face;
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
    public function populateFaces($photo, $path)
    {

        $config = $this->getConfig();
        $faces = array();
        // Detect faces using openCV
        foreach ($config['face_detect']['haarcascades'] as $haarCascade) {
            $faces = array_merge($faces,
                face_detect($path, getcwd() . '/' . $config['face_detect']['haarcascades_dir'] . $haarCascade));
        }

        // Filter based on color

        foreach ($faces as $detectedFace) {
            echo "FACE";
            $face = Face::fromArray($detectedFace);
            if ($this->faceColorCheck($path, $face)) {
                $photo->addFace($face);
            }
        }


        return $photo;
    }

    private function faceColorCheck($path, $detectedFace)
    {
        $config = $this->getConfig();

        $RGB = $this->getRGBAverage($path, $detectedFace);
        $HSV = $this->getHSV($RGB);

        return (
            $HSV['h'] < $config['face_detect']['hue_threshold']
            && $HSV['s'] < $config['face_detect']['saturation_threshold']
        );

    }

    private function getHSV($RGB)
    {
        $r = ($RGB['r'] / 255);
        $g = ($RGB['g'] / 255);
        $b = ($RGB['b'] / 255);

        $maxRGB = max($r, $g, $b);
        $minRGB = min($r, $g, $b);
        $chroma = $maxRGB - $minRGB;

        $computedV = 100 * $maxRGB;

        if ($chroma == 0) {
            return array(0, 0, $computedV);
        }

        $computedS = 100 * ($chroma / $maxRGB);

        if ($r == $minRGB) {
            $h = 3 - (($g - $b) / $chroma);
        } elseif ($b == $minRGB) {
            $h = 1 - (($r - $g) / $chroma);
        } else {

            // $G == $minRGB
            $h = 5 - (($b - $r) / $chroma);
        }
        $computedH = 60 * $h;

        return array(
            'h' => $computedH,
            's' => $computedS,
            'v' => $computedV,
        );

    }

    private function getRGBAverage($path, $detectedFace)
    {
        $image = new Imagick($path);
        $r = 0;
        $g = 0;
        $b = 0;
        $count = 0;

        $minX = $detectedFace->getX() + $detectedFace->getWidth() * 0.25;
        $maxX = $detectedFace->getX() + $detectedFace->getWidth() * 0.75;

        $minY = $detectedFace->getY() + $detectedFace->getHeight() * 0.25;
        $maxY = $detectedFace->getY() + $detectedFace->getHeight() * 0.75;

        for ($x = $minX; $x < $maxX; $x++) {
            for ($y = $minY; $y < $maxY; $y++) {
                $pixel = $image->getImagePixelColor($x, $y)->getColor();
                $r += $pixel['r'];
                $g += $pixel['g'];
                $b += $pixel['b'];
                $count++;
            }

        }

        // Return the averages
        return array(
            'r' => $r / $count,
            'g' => $g / $count,
            'b' => $b / $count
        );

    }

    /**
     * Get the photo config
     *
     * @return array containing the config for the module
     */
    public function getConfig()
    {
        $config = $this->sm->get('config');

        return $config['photo'];
    }
}
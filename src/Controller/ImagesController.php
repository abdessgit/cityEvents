<?php

namespace App\Controller;

use App\Entity\Images;
use App\Entity\Evenements;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ImagesController extends AbstractController
{

    public function addImages(Evenements $event, array $files, EntityManagerInterface $manager): array
    {
        $uploadDirectory = __DIR__ . '/../../public/uploads/images';

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 2 * 1024 * 1024; 
        $imagesMax = 3;
       
       
          
        $imagesExsist = $event->getImages()->count();
        $ImageAMettre = count($files);

        if ( $imagesExsist + $ImageAMettre > $imagesMax) {
            return ['error' => 'Maximum 3 images autorisées'];
        }

        $uploadedImages = [];
       
        foreach ($files as $file) {
            if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
               return ([
            'error' => "L'image' n'est pas au bon format."
            ]);  
            }
            if ($file->getSize() > $maxSize) {
               return ([
                'error' => "L'image f dapsse le 2 MO."
                ]);
            }

           

            $extension = $file->guessExtension();
            $newFilename = uniqid() . '_' . time() . '.' . $extension;

            $file->move($uploadDirectory, $newFilename);

            $image = new Images();
            $image->setNomImages($newFilename);
            $event->addImage($image);

            $manager->persist($image);
            $uploadedImages[] = $newFilename;
        }
        $manager->flush();
        return $uploadedImages;
    }


}



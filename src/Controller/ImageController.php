<?php

namespace App\Controller;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class ImageController extends AbstractController
{
    /*
     * GET GALLERIES AND IMAGES
     */

    #[Route(path: '/gallery/{path}', name: 'getPhotos', methods: 'GET')]
    public function getPhotos(string $path): JsonResponse
    {
        $file = new Filesystem();
        $current_dir = getcwd();
        // $path automaticky decoduje
        try {
            $gallery_file = $current_dir.'/files/gallery/'.$path.'/'.'gallery.json';
            $image_file = $current_dir.'/files/gallery/'.$path.'/items.json';

            if ($file->exists([$gallery_file, $image_file]))
            {
                $gallery = file_get_contents($gallery_file);
                $gallery_json = json_decode($gallery);
                $image = file_get_contents($image_file);
                $image_json = json_decode($image);
            }else{
                throw new \Exception('Gallery does not exists', 404);
            }
        }catch(IOExceptionInterface $exception) {
            throw new \Exception('WRONG', 500);
        }
        // TODO treba osetrit abz sa pri singel image zobrazovalo ako pole
        return $this->json(['gallery' => $gallery_json, 'images' => $image_json] , 200);
    }
    #[Route(path: '/images/{w}x{h}/{path}/{name}', methods: 'GET')]
    public function generateImg(int $w, int $h, string $path, string $name): Response
    {
        $current_dir = getcwd();
        $finder = new Finder();
        $file = new Filesystem();

        if (!$file->exists($current_dir.'/files/gallery/'.$path.'/'.$name))
        {
            throw new \Exception("Photo not found", 404);
        }

        foreach ($finder->files()->in($current_dir.'/files/gallery/'.$path) as $item)
        {
                if (strpos($item->getFilename(), 'jpg') && $name == $item->getFilename())
                {
                    if ($w < 0 || $w > 9000 || $h < 0 || $h > 9000)
                    {
                        throw new \Exception("The photo preview can't be generated", 500);
                    }
                    $photo = $item->getRealPath();
                    $imagine = new Imagine();
                    $image = $imagine->open($photo);
                    $image->resize(new Box($w, $h), );
                }
        }

        return new Response($image, 200, ['Content-type' => 'image/jpeg']);
    }

}
<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Entity\UploadFile;
use App\Form\Type\ChangePasswordType;
use App\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Controller used to manage current user.
 *
 * @Route("/file")
 * @IsGranted("ROLE_USER")
 *
 * @author Romain Monteil <monteil.romain@gmail.com>
 */
class FileUploadController extends AbstractController
{

    /**
     * @Route("/", defaults={"page": "1", "_format"="html"}, methods={"GET"}, name="upload_index")
     */
    public function indexAction(Request $request){

    }

    /**
     * @Route("/upload", methods={"POST"}, name="upload_file")
     */
    public function uploadAction(Request $request): Response
    {
        try {
            $uploadedFile = $request->files->all()["files"][0];
            $fileName = md5(uniqid()) . '.' . $uploadedFile->guessExtension();
            $original_name = $uploadedFile->getClientOriginalName();
            $uploadedFile->move($this->container->getParameter('file_directory'), $fileName);
            $status = UploadFile::STATUS_NEW;
            $file_entity = new UploadFile();

            $file_entity->setFileName($fileName);
            $file_entity->setCreatedAt(new \DateTime ());
            $file_entity->setStatus($status);

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($file_entity);
            $manager->flush();

            $jobInfo = json_encode(["id" => $file_entity->getId()]);
            $this->get("gearman")->doBackgroundJob("AppGearmanFileParseWorker~parseFile", $jobInfo);
        } catch (\Exception $exception){
            //
    }

    }
}

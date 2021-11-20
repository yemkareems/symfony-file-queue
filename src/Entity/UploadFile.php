<?php
/**
 * Copyright (c) Right Submission, LLC 2016-2018. All Rights Reserved.
 * All information contained herein is, and remains the property of Right Submission, LLC. the intellectual and technical
 * concepts contained herein are proprietary to Right Submission, LLC. Dissemination of this information or reproduction
 * of this material is strictly forbidden unless prior written permission is obtained from Right Submission, LLC.
 **/


namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * UploadFile
 *
 * @ORM\Table(name="upload_file")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\FileUploadRepository")
 */
class UploadFile {

    use ORMBehaviors\Timestampable\Timestampable;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="filename", type="string", length=255, nullable=false)
     * @var string
     */
    private $fileName;


    /**
     * @ORM\Column(name="status", type="string", length=255, nullable=false)
     * @var string
     */
    private $status;

    const STATUS_NEW = "NEW";
    const STATUS_QUEUED = "QUEUED";
    const STATUS_IN_PROGRESS = "PROGRESS";
    const STATUS_COMPLETE = "COMPLETE";
    const STATUS_ERROR = "ERROR";


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }



    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getCssClass(){
        $classMap = [
            self::STATUS_QUEUED => "info",
            self::STATUS_IN_PROGRESS => "info",
            self::STATUS_ERROR => "danger",
            self::STATUS_COMPLETE => "success"
        ];



        return $classMap[$this->status];
    }

    public function getStatusReadable(){
        $readable = [
            self::STATUS_QUEUED => "Queued",
            self::STATUS_IN_PROGRESS => "In Progress",
            self::STATUS_ERROR => "Error",
            self::STATUS_COMPLETE => "Complete",
            self::STATUS_NEW => "New"
        ];

        return $readable[$this->status];
    }


    /**
     * @return string
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }


}

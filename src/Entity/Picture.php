<?php

namespace App\Entity;

use App\Repository\PictureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: PictureRepository::class)]
/**
 * @Vich\Uploadable()
 */
class Picture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getPicture"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getPicture"])]
    #[Assert\Type(type: 'string', message:"Le nom du fichier doit être une chaîne de caractère.")]
    private ?string $realName = null;

    #[Groups(["getPicture"])]
    #[ORM\Column(length: 255)]
    private ?string $realPath = null;

    #[Groups(["getPicture"])]
    #[ORM\Column(length: 255)]
    #[Assert\Type(type: 'string', message:"Le path doit être une chaîne de caractère.")]
    private ?string $publicPath = null;

    #[Groups(["getPicture"])]
    #[ORM\Column(length: 255)]
    #[Assert\Type(type: 'string', message:"Le mimeType doit être une chaîne de caractère.")]
    private ?string $mimeType = null;

    #[Groups(["getPicture"])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $uploadDate = null;

    #[ORM\Column]
    #[Assert\Type(type: 'bool', message:"Le status doit être un boolean.")]
    private ?bool $status = null;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="images", fileNameProperty="realPath")
     */
    private $file;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRealName(): ?string
    {
        return $this->realName;
    }

    public function setRealName(string $realName): self
    {
        $this->realName = $realName;

        return $this;
    }

    public function getRealPath(): ?string
    {
        return $this->realPath;
    }

    public function setRealPath(string $realPath): self
    {
        $this->realPath = $realPath;

        return $this;
    }

    public function getPublicPath(): ?string
    {
        return $this->publicPath;
    }

    public function setPublicPath(string $publicPath): self
    {
        $this->publicPath = $publicPath;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getUploadDate(): ?\DateTimeInterface
    {
        return $this->uploadDate;
    }

    public function setUploadDate(\DateTimeInterface $uploadDate): self
    {
        $this->uploadDate = $uploadDate;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the value of file
     *
     * @return  File|null
     */ 
    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
     * Set the value of file
     *
     * @param  File|null  $file
     *
     * @return  self
     */ 
    public function setFile(File $file): ?Picture
    {
        $this->file = $file;

        return $this;
    }
}

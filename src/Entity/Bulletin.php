<?php

namespace App\Entity;

use App\Repository\BulletinRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BulletinRepository::class)]
class Bulletin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $category = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $creationDate = null;

    #[ORM\ManyToMany(targetEntity: Tag::class, mappedBy: 'bulletins')]
    private Collection $tags;

    #[ORM\Column]
    private ?bool $pinned = null;

    public function __construct($title, $category = "général", $content = ""){
        //Ce constructeur est appelé automatiquement au moment de la création de l'objet
        $this->title = $title;
        $this->category = $category;
        if(!$content) $this->generateContent();
        $this->creationDate = new \DateTime("now");
        $this->tags = new ArrayCollection();
        $this->pinned = false; //Chaque bulletin commence non-épinglé
    }

    public function getColorCode(): string
    {
        //Cette méthode rend un code couleur Bootstrap selon la catégorie de l'Entity
        switch($this->category){
            case 'général':
                return 'info';
            case 'divers':
                return 'warning';
            case 'urgent':
                return 'danger';
            case 'généré':
                return 'primary';
            default:
                return 'secondary';
        }
    }

    public function clearFields(): self
    {
        //Cette méthode retire le contenu généré de l'objet Bulletin
        $this->title = "";
        $this->category = "";
        $this->content = "";
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): self
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function generateContent(): string
    {
        //Cette méthode prépare un faux texte de type Lorem Ipsum de manière à garantir un contenu différent pour chaque Bulletin généré
        $lorem = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean malesuada, lorem id sodales eleifend, nisl magna luctus libero, eu tempus lacus nibh et diam. Sed gravida tortor sapien, faucibus euismod erat luctus sit amet. ";

        //Morceaux de texte aléatoire à arranger
        $snippets = [
            "In hac habitasse platea dictumst. Donec feugiat scelerisque euismod. Pellentesque gravida lobortis eros a congue.",

            "Aliquam velit sapien, blandit nec neque tincidunt, commodo elementum turpis. Donec convallis dolor ante, ut tristique eros sollicitudin ac. Phasellus in elementum neque. In fermentum lectus augue, pharetra imperdiet diam aliquet et.",

            "Nulla a massa vel ipsum gravida congue. Donec et neque sed enim tincidunt tincidunt. Suspendisse et arcu tellus. Morbi malesuada iaculis leo, non sollicitudin libero consequat ut. Fusce faucibus suscipit laoreet. Ut condimentum felis ut felis faucibus ultricies. Nunc in eleifend lectus.",

            "Integer cursus fermentum congue. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Aliquam non euismod tellus.",

            "Sed at placerat dui, vel eleifend mi. Vestibulum finibus ullamcorper ipsum, ac sodales felis ultrices eget. Proin vestibulum justo luctus, ullamcorper nisi vel, porttitor risus. Suspendisse urna metus, mollis vel condimentum ut, consectetur a leo.",

            "Phasellus vitae lorem mattis, euismod ex vel, vulputate ex. Aenean sapien turpis, semper a augue eu, tincidunt aliquet ante. Aenean eu varius magna. Aliquam sit amet nibh a nibh pretium auctor. Curabitur viverra efficitur consectetur. Aliquam erat volutpat.",

            "Quisque at lorem est. Nam varius, libero eu pulvinar finibus, libero tellus porta nulla, et dapibus arcu felis sed nulla.",

            "Nullam commodo, tortor ac sagittis sagittis, lacus turpis porta metus, a auctor ante ex vitae nisl. Nullam gravida, nunc vel dapibus iaculis, felis augue auctor nisi, eu lobortis erat leo eget erat. Proin rutrum tortor lorem, in vestibulum justo efficitur ut. Sed vel facilisis eros, eget rhoncus purus.",

            "Vivamus malesuada euismod rutrum. Etiam luctus ipsum nec finibus feugiat. Quisque blandit ultrices mauris, dapibus congue ligula maximus sed. Mauris a magna lorem. Mauris maximus sapien ut varius ullamcorper. In hac habitasse platea dictumst.",

            "Proin sed libero justo. Suspendisse pretium eleifend nulla. Quisque ornare vel purus a sollicitudin. Praesent magna mi, pharetra et luctus vel, rhoncus in lectus. Curabitur volutpat nisi eu ante accumsan malesuada. Morbi vestibulum diam quis nisi mollis eleifend.",

            "Quisque tincidunt turpis et efficitur fermentum. Nunc sit amet fermentum elit, in pharetra quam. Ut luctus pharetra faucibus. Vivamus et condimentum leo."
        ];
        //On prépare notre contenu original
        for($i=0;$i<5;$i++){
            //On choisit une clef du tableau snippets entre zéro et la taille totale du tableau moins un (étant donné que nous comptons à partir de zéro)
            $lorem .= $snippets[rand(0, count($snippets) - 1)];
            //Espace ou retour à la ligne?
            if(rand(0, 100) > 80){
                $lorem .= '
                ';
            } else $lorem .= ' ';
        }
        //On modifie la variable $content avec notre propre nouveau contenu
        $this->content = $lorem;
        //On retourne notre contenu préparé
        return $lorem;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->addBulletin($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->removeElement($tag)) {
            $tag->removeBulletin($this);
        }

        return $this;
    }

    public function isPinned(): ?bool
    {
        return $this->pinned;
    }

    public function setPinned(bool $pinned): self
    {
        $this->pinned = $pinned;

        return $this;
    }
}

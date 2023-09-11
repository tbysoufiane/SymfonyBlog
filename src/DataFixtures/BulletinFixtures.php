<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use App\Entity\Bulletin;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class BulletinFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        //On crée dix types de tags
        $tagNames = ['PHP', 'Symfony', 'POO', 'MVC', 'Divers', 'Doctrine', 'Twig', 'C++', 'Nouveau', 'Spécial'];
        //On crée à présent dix objets Tag que nous persistons et que nous rangeons dans un tableau pour les conserver
        $tags = [];
        foreach($tagNames as $tagName){
            $tag = new Tag;
            $tag->setName($tagName);
            $manager->persist($tag);
            array_push($tags, $tag);
        }

        $categories = ["général", "divers", "urgent"];

        for($i=0;$i<40;$i++){
            $bulletin = new Bulletin("Bulletin #" . uniqid());
            $bulletin->setCategory($categories[rand(0,2)]);
            $bulletin->setContent($this->generateContent());
            foreach($tags as $tag){
                //25% de chances de lier le bulletin au tag parcouru
                if(rand(0,100) > 75) $bulletin->addTag($tag);
            }
            $bulletin->setPinned(false);
            $manager->persist($bulletin);
        }

        $manager->flush();
        //On applique cette méthode grâce à la commande du terminal:
        //  php bin/console doctrine:fixtures:load
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
        //On retourne notre contenu préparé
        return $lorem;
    }
}

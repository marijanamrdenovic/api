<?php
namespace App\Controller;

use App\Repository\RecetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Recette;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class RecetteController extends AbstractController
{
    /**
     * Méthode pour créer une recette
     *
     * @Route("/recettes/add", name="add_recette", methods={"POST"})
     */
    public function addRecette(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator)
    {
        //On récupère les données JSON qu'on désérialise puis on crréer l'objet Recette
        $data = $request->getContent();
        try {
            $newRecette = $serializer->deserialize($data, Recette::class , 'json');
            //puis on vérifie que les requis qu'on a rajoutés avec le component Validator sont bien respectés, sinon on retourne l'erreur en question sous format JSON

            $errors = $validator->validate($newRecette);
            if (count($errors) > 0){
                $messagesError = [];
                foreach ($errors as $violation) {
                    $messagesError[$violation->getPropertyPath()][] = $violation->getMessage();
                }
                return $this->json(["error" =>  $messagesError], 400);
            }
            //si tout c'est bien déroulé on envoi la recette dans la base de donnée
            $em->persist($newRecette);
            $em->flush();
            //puis on retourne un message de validation avec les informations de l'objet créé
            return $this->json([$newRecette, 'message' => 'La recette a été créée!'],201);
        }catch (NotEncodableValueException $e){
            error_log($e);
            return $this->json([],404);
        }
    }

    /**
     * @Route("/recettes", name="get_all_recettes", methods={"GET"})
     */
    //Méthode pour récupérer toutes les recettes enregistrées
    public function getAll(RecetteRepository $recetteRepository)
    {
        // On recupere toutes les recettes
        try {
            $recettes = $recetteRepository->findAll();
            if (!empty($recettes)) {
                return $this->json($recettes, 200);
            } else {
                // si on trouve aucune on affiche un message en JSON
                return $this->json(['message' => 'Aucune recette'], 200);
            }
        }catch (NotEncodableValueException $e){
            error_log($e);
            return $this->json([],404);
        }
    }

    /**
     * @Route("/recettes/{id}", name="get_one_recette", methods={"GET"})
     */
    //Méthode pour récupérer une recette enregistrée avec le paramètre id
    public function getOne($id, RecetteRepository $recetteRepository)
    {
        try {
            //On vérifie que le paramètre est bien une valeur numérique, sinon on affiche un message JSON
            if(intval($id) == 0){
                return $this->json(['message' => "Merci de saisir une valeur numérique"],202);
            }
            //puis on récupère la recette correspondante au paramètre saisie
            $recette = $recetteRepository->findOneBy(['id' => $id]);
            //si elle n'existe pas on affiche un message en JSON
            if (!empty($recette)) {
                return $this->json($recette, 200);
            } else {
                return $this->json(['message' => 'Aucune recette'], 200);
            }//on affiche les erreurs si elles existent
        }catch (NotEncodableValueException $e){
            error_log($e);
            return $this->json([],404);
        }
    }

    /**
     * @Route("/recettes/update/{id}", name="update_recette", methods={"PUT"})
     */
    //Méthode pour modifier une recette avec le paramètre id
    public function update($id, Request $request, RecetteRepository $recetteRepository, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator)
    {
        //On vérifie que le paramètre est bien une valeur numérique, sinon on affiche un message JSON
        if(intval($id) == 0){
            return $this->json(['message' => "Merci de saisir une valeur numérique"],202);
        }
        ///puis on récupère la recette correspondante au paramètre saisie
        $recette = $recetteRepository->findOneBy(['id' => $id]);
        //et on vérifie qu'elle existe
        if (!empty($recette)) {
            //on récupère en suite les données JSON saisie
            $data = $request->getContent();
            try {
                //puis on crée l'objet Recette avec ces données
                $recetteUpdate = $serializer->deserialize($data,Recette::class, 'json');
                //on vérifie qu'elles ne contiennent pas d'erreurs, si les errors existent on les affiche avec un statut 400
                $errors = $validator->validate($recetteUpdate);
                if (count($errors) > 0){
                    $messagesError = [];
                    foreach ($errors as $violation) {
                        $messagesError[$violation->getPropertyPath()][] = $violation->getMessage();
                    }
                    return $this->json(["error" =>  $messagesError], 400);
                }
                //si tout est bon on set les données récupérées dans la recette qu'on veut modifier
                $recette->setTitre($recetteUpdate->getTitre());
                $recette->setSousTitre($recetteUpdate->getSousTitre());
                $recette->setListeIngredients($recetteUpdate->getListeIngredients());
                //on envoi les modifications dans la base de données
                $em->persist($recette);
                $em->flush();
                //et on affiche un message de succès avec l'objet modifié
                return $this->json(['data' => $recette, 'message' => 'La recette a été mise a jour!'],200);
            }catch (NotEncodableValueException $e){
                error_log($e);
                return $this->json([],404);
            }
            //si la recette qu'on souhaite modifier n´existe pas on affiche le message suivant
        }else {
            return $this->json(['message' => 'Aucune recette'], 200);
        }
    }

    /**
     * @Route("/recettes/delete/{id}", name="delete_recette", methods={"DELETE"})
     */
    //Méthode pour supprimer une recette avec le paramètre id
    public function delete($id, EntityManagerInterface $em, RecetteRepository $recetteRepository)
    {
        //On vérifie que le paramètre est bien une valeur numérique, sinon on affiche un message JSON
        if(intval($id) == 0){
            return $this->json(['message' => "Merci de saisir une valeur numérique"],202);
        }
        try {
            //puis on récupère la recette qui correspond au Id saisi
            $recette = $recetteRepository->findOneBy(['id' => $id]);
            //si la recette existe on la supprime de la base de données
            if (!empty($recette)) {
                $em->remove($recette);
                $em->flush();
                //et on retourne un message de confirmation en JSON
                return $this->json(['status' => 'La recette a été supprimée !'], 200);
                //si la recette n´existe pas on affiche le message suivant
            } else {
                return $this->json(['message' => 'Aucune recette'], 200);
            }//on récupère les erreurs et on les affichent avec le status 400
        }catch (NotEncodableValueException $e){
            error_log($e);
            return $this->json([],404);
        }
    }

}
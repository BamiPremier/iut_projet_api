<?php

namespace App\Controller;

use App\Entity\Batiment;
use App\Entity\Reservation;
use App\Entity\Salle;
use App\Entity\TypeUser;
use App\Entity\UserPlateform;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

use FFI\Exception;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Knp\Component\Pager\PaginatorInterface;

class ActionController extends AbstractController
{

    private $em;
    private   $serializer;
    private $clientWeb;
    private $paginator;
    public function __construct(
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        HttpClientInterface $clientWeb,
        PaginatorInterface $paginator,


    ) {
        $this->em = $em;
        $this->serializer = $serializer;

        $this->paginator = $paginator;

        $this->clientWeb = $clientWeb;
    }

    /**
     * @Route("/sales", name="saleAdd", methods={"POST"})
     *  @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * 
     */
    public function saleAdd(Request $request, SluggerInterface $slugger)
    {



        if (empty($request->get('idBatiment')) || empty($request->get('nom')) || empty($request->get('numero')) || empty($request->get('capacite')) || empty($request->get('longitude')) || empty($request->get('latitude'))) {
            return new JsonResponse([
                'message' => 'Veuillez  reessayer',

            ], 400);
        }

        $nom = $request->get('nom');

        $numero = $request->get('numero');
        $capacite = $request->get('capacite');
        $longitude = $request->get('longitude');
        $latitude = $request->get('latitude');
        $idBatiment = $request->get('idBatiment');

        $Batiment = $this->em->getRepository(Batiment::class)->findOneBy(['id' => $idBatiment]);
        if (!$Batiment) {
            return new JsonResponse([
                'message' => 'Veuillez reessayer',

            ], 400);
        }
        $sale = new Salle();

        $sale->setNomSalle($nom);
        $sale->setNumeroSalle($numero);
        $sale->setCapaciteSalle($capacite);
        $sale->setLongitude($longitude);
        $sale->setLatitude($latitude);
        $sale->setAltitude($latitude);
        $sale->setBatiment($Batiment);
        $file =  $request->files->get('file');
        if ($file) {
            $originalFilenameData = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilenameData = $slugger->slug($originalFilenameData);
            $newFilenameData =
                $nom  . '.' . $file->guessExtension();

            $file->move(
                $this->getParameter('salle_object'),
                $newFilenameData
            );
            $sale->setSrc($newFilenameData);
        }
        $this->em->persist($sale);
        $this->em->flush();
        return new JsonResponse([
            'message' => 'Success',


        ], 200);
    }

    /**
     * @Route("/sales", name="saleRead", methods={"GET"})
     *  @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * 
     */
    public function saleRead(Request $request,)
    {


        $lsale = [];

        $lsaleCollections = $this->em->getRepository(Salle::class)->findBy(['etatSalle' => false]);

        foreach ($lsaleCollections as $sale) {



            $saleU =  [
                'id' => $sale->getId(),
                'nomSalle' => $sale->getNomSalle(),
                'numeroSalle' => $sale->getNumeroSalle(),

                'capaciteSalle' =>  $sale->getCapaciteSalle(),
                'longitude' =>  $sale->getLongitude(),
                'latitude' =>  $sale->getLatitude(),
                'src' =>  'http' . '://' . $_SERVER['HTTP_HOST'] . '/images/salle/' . ($sale->getSrc() ?? 'default.jpg'),


            ];
            array_push($lsale, $saleU);
        }
        return new JsonResponse([
            'data' =>
            $lsale

        ], 200);
    }
    /**
     * @Route("/sales/default", name="saleReadD", methods={"GET"})
     *  @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * 
     */
    public function saleReadD(Request $request,)
    {


        $lsale = [];

        $lsaleCollections = $this->em->getRepository(Salle::class)->findAll();

        foreach ($lsaleCollections as $sale) {



            $saleU =  [
                'id' => $sale->getId(),
                'nomSalle' => $sale->getNomSalle(),
                'numeroSalle' => $sale->getNumeroSalle(),
                'etat' => $sale->isEtatSalle(),

                'capaciteSalle' =>  $sale->getCapaciteSalle(),
                'longitude' =>  $sale->getLongitude(),
                'latitude' =>  $sale->getLatitude(),
                'src' =>  'http' . '://' . $_SERVER['HTTP_HOST'] . '/images/salle/' . ($sale->getSrc() ?? 'default.jpg'),


            ];
            array_push($lsale, $saleU);
        }
        return new JsonResponse([
            'data' =>
            $lsale

        ], 200);
    }

    /**
     * @Route("/sales/remove", name="saleRemove", methods={"POST"})
     *  @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * 
     */
    public function saleRemove(Request $request,)
    {
        // $keySecret = $request->query->get('keySecret');
        $idSalle = $request->query->get('idSalle');
        $page = $request->query->get('page');

        $this->em->beginTransaction();
        try {

            // if (empty($keySecret)) {
            //     return new JsonResponse([
            //         'message' => 'Veuillez recharger la page et reessayer   ',

            //     ], 400);
            // }

            $salle = $this->em->getRepository(Salle::class)->findOneBy(['id' => $idSalle]);
            $this->em->remove($salle);

            return new JsonResponse([
                'message' => 'Success',


            ], 200);
        } catch (\Exception $e) {
            // Une erreur s'est produite, annulez la transaction

            return new JsonResponse([
                'message' => 'Une erreur est survenue'
            ], 203);
        }
    }

    /**
     * @Route("/reservations", name="reservationAdd", methods={"POST"})
     *  @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * 
     */
    public function reservationAdd(Request $request,)
    {

        $data
            =     $data = $request->toArray();


        if (empty($data['idUser']) || empty($data['idSalle'])) {
            return new JsonResponse([
                'message' => 'Veuillez recharger la page et reessayer   ',

            ], 400);
        }

        $idUser = $data['idUser'];

        $idSalle = $data['idSalle'];
        $user = $this->em->getRepository(UserPlateform::class)->findOneBy(['id' => $idUser]);


        $sale = $this->em->getRepository(Salle::class)->findOneBy(['id' => $idSalle]);
        if (!$sale->isEtatSalle()) {

            $reservation = new Reservation();

            $reservation->setUtilisateur($user);
            $reservation->setSale($sale);
            $sale->setEtatSalle(true);

            $this->em->persist($sale);
            $this->em->persist($reservation);
            $this->em->flush();
            return new JsonResponse([
                'message' => 'Success',


            ], 200);
        } else {
            return new JsonResponse([
                'message' => 'Cette salle a deja une reservation en cours',


            ], 203);
        }
    }

    /**
     * @Route("/reservations", name="ReadReservation", methods={"GET"})
     *  @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * 
     */
    public function ReadReservation(Request $request,)
    {
        $keySecret = $request->query->get('keySecret');
        $page = $request->query->get('page');
        $lsale = [];

        try {


            $lsaleCollections = $this->em->getRepository(Salle::class)->findBy(['etatSalle' => true]);

            foreach ($lsaleCollections as $sale) {





                $saleU =  [
                    'id' => $sale->getId(),
                    'nomSalle' => $sale->getNomSalle(),
                    'numeroSalle' => $sale->getNumeroSalle(),

                    'capaciteSalle' =>  $sale->getCapaciteSalle(),
                    'longitude' =>  $sale->getLongitude(),
                    'latitude' =>  $sale->getLatitude(),
                    'src' =>  'http' . '://' . $_SERVER['HTTP_HOST'] . '/images/salle/' . ($sale->getSrc() ?? 'default.jpg'),



                ];
                array_push($lsale, $saleU);
            }
            return new JsonResponse(
                [
                    'data' =>
                    $lsale

                ],
                200
            );
        } catch (\Exception $e) {
            // Une erreur s'est produite, annulez la transaction

            return new JsonResponse([
                'message' => 'Une erreur est survenue'
            ], 203);
        }
    }

    /**
     * @Route("/reservations/finish", name="reservationFInish", methods={"POST"})
     *  @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * 
     */
    public function reservationFInish(Request $request,)
    {

        $data
            =        $data = $request->toArray();


        if (empty($data['idSalle'])) {
            return new JsonResponse([
                'message' => 'Veuillez recharger la page et reessayer   ',

            ], 400);
        }


        $idSalle = $data['idSalle'];


        $sale = $this->em->getRepository(Salle::class)->findOneBy(['id' => $idSalle]);
        if ($sale->isEtatSalle()) {
            $sale->setEtatSalle(false);
            $this->em->persist($sale);
            $this->em->flush();
            return new JsonResponse([
                'message' => 'Success',


            ], 200);
        } else {
            return new JsonResponse([
                'message' => 'Cette salle n\'a pas de reservation',


            ], 203);
        }
    }


    /**
     * @Route("/batiments", name="batimentAdd ", methods={"POST"})
     *  @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * 
     */
    public function batimentAdd(Request $request, SluggerInterface $slugger)
    {



        if (empty($request->get('idUser')) || empty($request->get('nom'))) {
            return new JsonResponse([
                'message' => 'Remplir tous les champs',

            ], 203);
        }

        $idUser =  $request->get('idUser');

        $nom =  $request->get('nom');
        $descripitionBatiment =  $request->get('description');
        $file =  $request->files->get('file');
        $user = $this->em->getRepository(UserPlateform::class)->findOneBy(['id' => $idUser]);

        $batiment = new Batiment();

        $batiment->setNomBatiment($nom);
        $batiment->setDescriptionBatiment($descripitionBatiment);

        if ($file) {
            $originalFilenameData = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilenameData = $slugger->slug($originalFilenameData);
            $newFilenameData =
                $nom  . '.' . $file->guessExtension();

            $file->move(
                $this->getParameter('batiment_object'),
                $newFilenameData
            );
            $batiment->setSrc($newFilenameData);
        }
        // if ($user->getTypeUser()->getId() == 3) {


        $this->em->persist($batiment);
        $this->em->flush();
        return new JsonResponse([
            'message' => 'Success',


        ], 200);
        // } else {
        //     return new JsonResponse([
        //         'message' => 'Vous n\'avez pas le droit',


        //     ], 203);
        // }
    }

    /**
     * @Route("/batiments", name="batimentRead", methods={"GET"})
     *  @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * 
     */
    public function batimentRead(Request $request,)
    {
        $keySecret = $request->query->get('keySecret');
        $page = $request->query->get('page');
        $lF
            = [];

        try {


            $lCollections = $this->em->getRepository(Batiment::class)->findAll();

            foreach ($lCollections as $collection) {


                $data =  [
                    'id' => $collection->getId(),
                    'nomBatiment' => $collection->getNomBatiment(),
                    'descripitionBatiment' => $collection->getDescriptionBatiment(),
                    'nombreSalle' => count($collection->getSalles()),
                    'src' =>  'http' . '://' . $_SERVER['HTTP_HOST'] . '/images/batiment/' . ($collection->getSrc() ?? 'default.jpg'),



                ];
                array_push($lF, $data);
            }

            return new JsonResponse([
                'data' =>
                $lF

            ], 200);
        } catch (\Exception $e) {
            // Une erreur s'est produite, annulez la transaction

            return new JsonResponse([
                'message' => 'Une erreur est survenue'
            ], 203);
        }
    }
    /**
     * @Route("/batiments/salle", name="batimentReadBatiment", methods={"GET"})
     *  @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * 
     */
    public function batimentReadBatiment(Request $request,)
    {
        $idBatiment = $request->query->get('idBatiment');

        $lsale
            = [];

        try {


            $batiment = $this->em->getRepository(Batiment::class)->findOneBy(['id' => $idBatiment]);


            foreach ($batiment->getSalles() as $salle) {


                $saleU =  [
                    'id' => $salle->getId(),
                    'nomSalle' => $salle->getNomSalle(),
                    'numeroSalle' => $salle->getNumeroSalle(),

                    'capaciteSalle' =>  $salle->getCapaciteSalle(),
                    'longitude' =>  $salle->getLongitude(),
                    'latitude' =>  $salle->getLatitude(),
                    'src' =>  'http' . '://' . $_SERVER['HTTP_HOST'] . '/images/salle/' . ($salle->getSrc() ?? 'default.jpg'),



                ];
                array_push($lsale, $saleU);
            }
            return new JsonResponse([
                'data' =>
                $lsale


            ], 200);
        } catch (\Exception $e) {
            // Une erreur s'est produite, annulez la transaction

            return new JsonResponse([
                'message' => 'Une erreur est survenue'
            ], 203);
        }
    }

    /**
     * @Route("/batiments/remove", name="batimentRemove ", methods={"POST"})
     *  @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * 
     */
    public function batimentRemove(Request $request,)
    {

        $data
            =        $data = $request->toArray();


        if (empty($data['idUser']) || empty($data['idBatiment'])) {
            return new JsonResponse([
                'message' => 'Remplir tous les champs',

            ], 203);
        }

        $idUser = $data['idUser'];
        $idBatiment = $data['idBatiment'];



        $user = $this->em->getRepository(UserPlateform::class)->findOneBy(['id' => $idUser]);
        $batiment = $this->em->getRepository(Batiment::class)->findOneBy(['id' => $idBatiment]);



        if ($user->getTypeUser()->getId() ==  1 && $batiment) {

            // Supprimer toutes les salles associées au bâtiment
            foreach ($batiment->getSalles() as $salle) {
                $this->em->remove($salle);
            }

            // Supprimer le bâtiment lui-même
            $this->em->remove($batiment);
            $this->em->flush();

            return new JsonResponse([
                'message' => 'Success',


            ], 200);
        } else {
            return new JsonResponse([
                'message' => 'Vous n\'avez pas le droit',


            ], 203);
        }
    }

    /**
     * @Route("/delegue/set", name="setDelegue ", methods={"POST"})
     *  @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * 
     */
    public function setDelegue(Request $request,)
    {

        $data
            =        $data = $request->toArray();


        if (empty($data['idUser']) || empty($data['idDelegue'])) {
            return new JsonResponse([
                'message' => 'Remplir tous les champs',

            ], 203);
        }

        $idUser = $data['idUser'];
        $idDelegue = $data['idDelegue'];



        $user = $this->em->getRepository(UserPlateform::class)->findOneBy(['id' => $idUser]);
        $delegue = $this->em->getRepository(UserPlateform::class)->findOneBy(['id' => $idDelegue]);

        if (!$user || !$delegue) {
            return new JsonResponse([
                'message' => 'Utilisateur introuvable',

            ], 203);
        }

        // if ($user->getTypeUser()->getId() ==  3) {

        $type_delegue = $this->em->getRepository(TypeUser::class)->findOneBy(['id' => 2]);

        $delegue->setTypeUser($type_delegue);

        $this->em->persist($delegue);
        $this->em->flush();

        return new JsonResponse([
            'message' => 'Success',


        ], 200);
        // } else {
        //     return new JsonResponse([
        //         'message' => 'Vous n\'avez pas le droit',


        //     ], 203);
        // }
    }
    /**
     * @Route("/delegue/unset", name="unsetDelegue ", methods={"POST"})
     *  @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * 
     */
    public function unsetDelegue(Request $request,)
    {

        $data
            =        $data = $request->toArray();


        if (empty($data['idUser']) || empty($data['idDelegue'])) {
            return new JsonResponse([
                'message' => 'Remplir tous les champs',

            ], 203);
        }

        $idUser = $data['idUser'];
        $idDelegue = $data['idDelegue'];



        $user = $this->em->getRepository(UserPlateform::class)->findOneBy(['id' => $idUser]);
        $delegue = $this->em->getRepository(UserPlateform::class)->findOneBy(['id' => $idDelegue]);

        if (!$user || !$delegue) {
            return new JsonResponse([
                'message' => 'Utilisateur introuvable',

            ], 203);
        }

        // if ($user->getTypeUser()->getId() ==  3) {

        $type_delegue = $this->em->getRepository(TypeUser::class)->findOneBy(['id' => 3]);

        $delegue->setTypeUser($type_delegue);

        $this->em->persist($delegue);
        $this->em->flush();

        return new JsonResponse([
            'message' => 'Success',


        ], 200);
        // } else {
        //     return new JsonResponse([
        //         'message' => 'Vous n\'avez pas le droit',


        //     ], 203);
        // }
    }


    /**
     * @Route("/users", name="userRead", methods={"GET"})
     *  @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * 
     */
    public function userRead(Request $request,)
    {
        $keySecret = $request->query->get('keySecret');

        $lF
            = [];

        try {


            $lCollections = $this->em->getRepository(UserPlateform::class)->findAll();

            foreach ($lCollections as $user) {


                $data =  [
                    'id' => $user->getId(),
                    'nom' => $user->getNom(),
                    'prenom' => $user->getPrenom(),
                    'email' => $user->getEmail(),
                    'phone' => $user->getPhone(),
                    'typeUser' => [
                        'id' => $user->getTypeUser()->getId(),
                        'libelle' => $user->getTypeUser()->getLibelle(),
                    ],




                ];
                array_push($lF, $data);
            }

            return new JsonResponse([
                'data' =>
                $lF

            ], 200);
        } catch (\Exception $e) {
            // Une erreur s'est produite, annulez la transaction

            return new JsonResponse([
                'message' => 'Une erreur est survenue'
            ], 203);
        }
    }
    /**
     * @Route("/type", name="type ", methods={"GET"})
     *  @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * 
     */
    public function type(Request $request,)
    {

        $t = ['Admin', 'Delegue', 'Etudiant'];

        for ($i = 0; $i < 3; $i++) {
            # code...

            $typr = new TypeUser();

            $typr->setLibelle($t[$i]);

            $this->em->persist($typr);
            $this->em->flush();
        }
        return new JsonResponse([
            'message' => 'Success',


        ], 200);
    }
    /**
     * @Route("/setadmin", name="setAdmin ", methods={"GET"})
     *  @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * 
     */
    public function setAdmin(Request $request,)
    {

        $type_delegue = $this->em->getRepository(TypeUser::class)->findOneBy(['id' => 2]);
        $delegue = $this->em->getRepository(UserPlateform::class)->findOneBy(['id' => 2]);
        $delegue->setTypeUser($type_delegue);

        $type_admin = $this->em->getRepository(TypeUser::class)->findOneBy(['id' => 2]);
        $admin = $this->em->getRepository(UserPlateform::class)->findOneBy(['id' => 1]);
        $admin->setTypeUser($type_admin);

        $this->em->persist($admin);
        $this->em->persist($delegue);
        $this->em->flush();
        return new JsonResponse([
            'message' => 'Success',


        ], 200);
    }
}

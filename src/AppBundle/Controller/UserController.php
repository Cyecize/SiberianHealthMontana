<?php
/**
 * Created by PhpStorm.
 * User: cyecize
 * Date: 25.4.2018 г.
 * Time: 17:48
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Township;
use AppBundle\Entity\UserAddress;
use AppBundle\Form\UserAddressType;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use function Sodium\add;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Util\DirectoryCreator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{

    /**
     * @Route("/user/profile/show", name="profile_page")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $user = $this->getUser();
        $addresses = $this->getDoctrine()->getRepository(UserAddress::class)->findBy(array('userId' => $user->getId()));
        $error = $request->get('error');
        $success = $request->get('success');

        return $this->render('user-related/profile-page.html.twig', [
            'addresses' => $addresses,
            'error' => $error,
            'success' => $success,
        ]);
    }


    /**
     * @Route("/user/address/add", name="add_address", methods={"POST"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addAddressAction(Request $request)
    {
        $user = $this->getUser();
        $address = new UserAddress();
        $address->setUserId($user->getId());

        $form = $this->createForm(UserAddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            try{
                $address = $this->createAddress($address);
            }catch (Exception $e){
                return $this->redirectToRoute('profile_page', ['error' => $e->getMessage()]);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($address);
            $entityManager->flush();
        }

        return $this->redirectToRoute('profile_page', ['success' => "Успешно добавен адрес"]);
    }

    /**
     * @Route("/user/address/find/{id}", name="get_single_address", defaults={"id"=null}, methods={"GET"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAddressAction($id){
        $result = ['status'=>0, 'message'=> null,'address'=>null];
        $user = $this->getUser();


        if($id == null){
            $result['status'] = 404;
            $result['message'] = "Cannot have null id";
            goto escape;
        }

        $addr = $this->getDoctrine()->getRepository(UserAddress::class)->findOneBy(array('id'=>$id));
        if($addr == null){
            $result['status'] = 404;
            $result['message'] = "Invalid Id";
            goto escape;
        }

        if($addr->getUserId() != $user->getId()){
            $result['status'] = 403;
            $result['message'] = "You don't have the rights";
            goto escape;
        }

        $addressObj = ['id'=>$addr->getId(), 'fullName'=>$addr->getFullName(), 'address'=>$addr->getAddress(), 'postCode'=>$addr->getPostCode(), 'townshipId'=>$addr->getTownshipId(),
            'phoneNumber'=>$addr->getPhoneNumber(), 'residential'=>$addr->getResidential()];


        $result['status'] = 200;
        $result['message'] = "OK";
        $result['address'] = $addressObj;

        escape:

        $result = json_encode($result);
        return $this->render('queries/generic-query-aftermath-message.twig',  [
            'error'=>$result,
        ]);
    }

    /**
     * @Route("/user/address/edit/{addressId}", name="edit_address", methods={"POST"}, defaults={"addressId"=null})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @param $addressId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public  function editAddressAction(Request $request, $addressId){
        $user = $this->getUser();
        $addressToEdit = $this->getDoctrine()->getRepository(UserAddress::class)->findOneBy(array('id'=>$addressId));

        if($addressToEdit == null || $user->getId() != $addressToEdit->getUserId()){
            return $this->redirectToRoute('profile_page', ['error'=>"Имаше проблем с редакцията!"]);
        }

        $address = new UserAddress();
        $form = $this->createForm(UserAddressType::class, $address);
        $form->handleRequest($request);

        if($form->isSubmitted()){
            try{
                $address = $this->createAddress($address);

                $addressToEdit->setTownship($address->getTownShip());
                $addressToEdit->setFullName($address->getFullName());
                $addressToEdit->setPostCode($address->getPostCode());
                $addressToEdit->setResidential($address->getResidential());
                $addressToEdit->setPhoneNumber($address->getPhoneNumber());
                $addressToEdit->setTownshipId($address->getTownshipId());
                $addressToEdit->setAddress($address->getAddress());

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->merge($addressToEdit);
                $entityManager->flush();

                return $this->redirectToRoute('profile_page', ['success'=>"Успешно редактирахте вашия адрес"]);

            }catch (Exception $e){
                return $this->redirectToRoute('profile_page', ['error'=>$e->getMessage()]);
            }
        }

        return $this->redirectToRoute('profile_page', ['error'=>'Имаше грешка с изпращането на формата. Ако продължавате да имате този проблем, свържете се с нас']);

    }

    /**
     * @Route("/user/address/remove/{addressId}", name="remove_address", defaults={"addressId"=null})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param $addressId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAddressAction($addressId){
        $user = $this->getUser();
        $address = $this->getDoctrine()->getRepository(UserAddress::class)->findOneBy(array('id'=>$addressId));
        if($address == null || $address->getUserId() != $user->getId()){
            return $this->redirectToRoute('profile_page', ['error'=>"Имаше проблем при изтриването! Ако проблемът продължава, свържете се с нас"]);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($address);
        $entityManager->flush();

        return $this->redirectToRoute('profile_page', ['success'=>"Успешно премахнахте ваш адрес!"]);

    }


    /**
     * @param UserAddress $address
     * @return UserAddress
     * @throws Exception
     */
    private function createAddress(UserAddress $address) : UserAddress{
        if (!$address->isAddressValid())
            throw new Exception("Навалиден адрес! Попълнете всички полета");
        $township = $this->getDoctrine()->getRepository(Township::class)->findOneBy(array('id' => $address->getTownshipId()));
        if ($township == null)
            throw new Exception( "Навалиден адрес! Тази област не съществува");
        $address->setTownship($township);
        return $address;
    }
}
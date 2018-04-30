<?php
/**
 * Created by PhpStorm.
 * User: cyecize
 * Date: 25.4.2018 г.
 * Time: 17:48
 */

namespace AppBundle\Controller;

use AppBundle\Constant\Config;
use AppBundle\Entity\Notification;
use AppBundle\Entity\Township;
use AppBundle\Entity\User;
use AppBundle\Entity\UserAddress;
use AppBundle\Entity\UserPasswordChange;
use AppBundle\Form\UserAddressType;
use AppBundle\Form\UserPasswordType;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use function Sodium\add;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Util\DirectoryCreator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends Controller
{

    /**
     * @param User $user
     * @return bool
     */
    private function isUserPrivileged(User $user)
    {
        if ($user->getAuthorityLevel() <= Config::$ADMIN_USER_LEVEL)
            return true;
        return false;
    }

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

        $passwordForm = $this->createForm(UserPasswordType::class);

        return $this->render('user-related/profile-page.html.twig', [
            'addresses' => $addresses,
            'error' => $error,
            'success' => $success,
            'changePasswordForm' => $passwordForm->createView(),
        ]);
    }

    /**
     * @Route("/user/profile/show/{userId}", name="show_user_info", defaults={"userId"=null})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param $userId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function showUserInfo($userId)
    {
        if ($userId == null)
            return $this->redirectToRoute('homepage');
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(array('id' => $userId));
        if ($user == null)
            $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(array('username' => $userId));
        if ($user == null)
            return $this->redirectToRoute('homepage');

        $addresses = $this->getDoctrine()->getRepository(UserAddress::class)->findBy(array('userId' => $user->getId()));

        return $this->render('user-related/profile-page-details.html.twig', array(
            'user' => $user,
            'isAppUserAdmin' => $this->isUserPrivileged($this->getUser()),
            'addresses' => $addresses,
        ));
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

        $redirection = $request->get('redirect');
        if($redirection == null)
            $redirection = "profile";
        $redirections = [
            'checkout'=>'shopping_cart_checkout',
            'profile'=>'profile_page'
        ];

        $form = $this->createForm(UserAddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            try {
                $address = $this->createAddress($address);
            } catch (Exception $e) {
                return $this->redirectToRoute($redirections[$redirection], ['error' => $e->getMessage()]);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($address);
            $entityManager->flush();
        }

        return $this->redirectToRoute($redirections[$redirection], ['success' => "Успешно добавен адрес"]);
    }

    /**
     * @Route("/user/address/find/{id}", name="get_single_address", defaults={"id"=null}, methods={"GET"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAddressAction($id)
    {
        $result = ['status' => 0, 'message' => null, 'address' => null];
        $user = $this->getUser();


        if ($id == null) {
            $result['status'] = 404;
            $result['message'] = "Cannot have null id";
            goto escape;
        }

        $addr = $this->getDoctrine()->getRepository(UserAddress::class)->findOneBy(array('id' => $id));
        if ($addr == null) {
            $result['status'] = 404;
            $result['message'] = "Invalid Id";
            goto escape;
        }

        if ($addr->getUserId() != $user->getId()) {
            $result['status'] = 403;
            $result['message'] = "You don't have the rights";
            goto escape;
        }

        $addressObj = ['id' => $addr->getId(), 'fullName' => $addr->getFullName(), 'address' => $addr->getAddress(), 'postCode' => $addr->getPostCode(), 'townshipId' => $addr->getTownshipId(),
            'phoneNumber' => $addr->getPhoneNumber(), 'residential' => $addr->getResidential()];


        $result['status'] = 200;
        $result['message'] = "OK";
        $result['address'] = $addressObj;

        escape:

        $result = json_encode($result);
        return $this->render('queries/generic-query-aftermath-message.twig', [
            'error' => $result,
        ]);
    }

    /**
     * @Route("/user/address/edit/{addressId}", name="edit_address", methods={"POST"}, defaults={"addressId"=null})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @param $addressId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editAddressAction(Request $request, $addressId)
    {
        $user = $this->getUser();
        $addressToEdit = $this->getDoctrine()->getRepository(UserAddress::class)->findOneBy(array('id' => $addressId));
        $redirection = $request->get('redirect');
        if($redirection == null)
            $redirection = "profile";
        $redirections = [
            'checkout'=>'shopping_cart_checkout',
            'profile'=>'profile_page'
        ];


        if ($addressToEdit == null || $user->getId() != $addressToEdit->getUserId()) {
            return $this->redirectToRoute($redirections[$redirection], ['error' => "Имаше проблем с редакцията!"]);
        }

        $address = new UserAddress();
        $form = $this->createForm(UserAddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            try {
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

                return $this->redirectToRoute($redirections[$redirection], ['success' => "Успешно редактирахте вашия адрес"]);

            } catch (Exception $e) {
                return $this->redirectToRoute($redirections[$redirection], ['error' => $e->getMessage()]);
            }
        }

        return $this->redirectToRoute($redirections[$redirection], ['error' => 'Имаше грешка с изпращането на формата. Ако продължавате да имате този проблем, свържете се с нас']);

    }

    /**
     * @Route("/user/address/remove/{addressId}", name="remove_address", defaults={"addressId"=null})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param $addressId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAddressAction($addressId)
    {
        $user = $this->getUser();
        $address = $this->getDoctrine()->getRepository(UserAddress::class)->findOneBy(array('id' => $addressId));
        if ($address == null || $address->getUserId() != $user->getId()) {
            return $this->redirectToRoute('profile_page', ['error' => "Имаше проблем при изтриването! Ако проблемът продължава, свържете се с нас"]);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($address);
        $entityManager->flush();

        return $this->redirectToRoute('profile_page', ['success' => "Успешно премахнахте ваш адрес!"]);

    }

    /**
     * @Route("/user/password/change", name="change_password", methods={"POST"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function changePasswordAction(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $appUser = $this->getUser();
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(array('id' => $appUser->getId()));

        $pass = new UserPasswordChange();
        $form = $this->createForm(UserPasswordType::class, $pass);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (!password_verify($pass->getOldPassword(), $user->getPassword())) {
                return $this->redirectToRoute('profile_page', ['error' => "Неправилна парола"]);
            }
            if (!$pass->isPasswordsSame()) {
                return $this->redirectToRoute('profile_page', ['error' => "Паролите не съвпадат"]);
            }
            if (strlen($pass->getNewPassword()) < 6) {
                return $this->redirectToRoute('profile_page', ['error' => "Въведете парола с поне 6 знака"]);
            }

            $hashedPassword = $encoder->encodePassword($user, $pass->getNewPassword());
            $user->setPassword($hashedPassword);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute("security_logout");

        } else {
            return $this->redirectToRoute('profile_page', ['error' => "Имаше проблем с изпращането на формата. Ако продължите да получавате тази грешка, свържете се с нас"]);
        }
    }

    /**
     * @Route("/user/destroy", name="remove_profile")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @param TokenStorageInterface $tokenStorage
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeProfileAction(Request $request, TokenStorageInterface $tokenStorage)
    {
        $appUser = $this->getUser();
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id' => $appUser->getId()]);
        $cart = $user->getCart();

        $confirmation = $request->get('conf');
        if ($confirmation != 'yes')
            return $this->render('queries/generic-query-aftermath-message.twig',
                [
                    'error' => 'account NOT',
                ]);

        $addresses = $this->getDoctrine()->getRepository(UserAddress::class)->findBy(array('userId' => $user->getId()));

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($cart);
        $entityManager->remove($user);
        foreach ($addresses as $address)
            $entityManager->remove($address);
        $entityManager->flush();
        $tokenStorage->setToken(null);

        return $this->render('queries/generic-query-aftermath-message.twig',
            [
                'error' => 'account removed',
            ]);
    }

    /**
     * @Route("/user/notifications/request", name="get_notifications")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function loadNotificationsAction(){
        $user = $this->getUser();
        $notifications = $this->getDoctrine()->getRepository(Notification::class)->findBy(array('targetId'=>$user->getId()));

        return $this->render('user-related/templates/notification-update-result.html.twig',
            [
               'notis'=>$notifications,
            ]);
    }

    /**
     * @Route("/user/notifications/remove-all", name="remove_notifications")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function removeAllNotisAction(){
        $notifications = $this->getDoctrine()->getRepository(Notification::class)->findBy(array('targetId'=>$this->getUser()->getId()));
        if($notifications != null){
            $entityManager  = $this->getDoctrine()->getManager();
            foreach ($notifications as $notification)
                $entityManager->remove($notification);
            $entityManager->flush();
        }

        return $this->render('user-related/templates/notification-update-result.html.twig',
            [
                'notis'=>null,
            ]);
    }

    /**
     * @Route("/user/notifications/remove", name="remove_single_notification")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeSingleNotificationAction(Request $request){
        $id = $request->get('notificationId');
        $notification = $this->getDoctrine()->getRepository(Notification::class)->findOneBy(array('id'=>$id));
        if($notification != null && $notification->getTargetId() == $this->getUser()->getid()){
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($notification);
            $entityManager->flush();
        }

        return $this->redirectToRoute('get_notifications');
    }


    /**
     * @param UserAddress $address
     * @return UserAddress
     * @throws Exception
     */
    private function createAddress(UserAddress $address): UserAddress
    {
        if (!$address->isAddressValid())
            throw new Exception("Навалиден адрес! Попълнете всички полета");
        $township = $this->getDoctrine()->getRepository(Township::class)->findOneBy(array('id' => $address->getTownshipId()));
        if ($township == null)
            throw new Exception("Навалиден адрес! Тази област не съществува");
        $address->setTownship($township);
        return $address;
    }
}
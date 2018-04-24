<?php
/**
 * Created by PhpStorm.
 * User: Cyecize
 * Date: 2/17/2018
 * Time: 10:10 PM
 */

namespace AppBundle\Controller;

use AppBundle\Constant\Config;
use AppBundle\Entity\ShoppingCart;
use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use AppBundle\Service\CartManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends Controller
{
    function isUserLogged(){
        return $this->get('security.authorization_checker')->isGranted('ROLE_USER', 'ROLES');  //when user is logged
    }

    /**
     * @Route("/login", name="security_login")
     */

    public function loginAction(Request $request, AuthenticationUtils $authUtils, CartManager $cartManager)
    {
        $user = new User();
        $userForm = $this->createForm(UserType::class, $user);
        if($this->isUserLogged())
            return $this->redirectToRoute("homepage");
        $lastUsername = null;
        $error = $authUtils->getLastAuthenticationError();
        // get the login error if there is one

        // last username entered by the user
        $lastUsername = $authUtils->getLastUsername();

        if($error != null) {
            $error =  "Wrong Password!";
            $repo = $this->getDoctrine()->getRepository(User::class);
            $existingUser = $repo->findOneBy(array("username"=>$lastUsername));
            if($existingUser == null)
                $existingUser = $repo->findOneBy(array("email"=>$lastUsername));
            if ($existingUser == null) {
                $lastUsername = null;
                $error = "Username or Email does not exist!";
            }
        }



        return $this->render("default/login-register.html.twig",
            array(
                "last_username"=>$lastUsername,
                "error" => $error,
                "userform"=>new User(),
                'form'=>$userForm->createView(),
            ));

    }

    /**
     * @Route("/register", name="security_register")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function regiesterAction(Request $request){
        if($this->isUserLogged())
            return $this->redirectToRoute("homepage");

        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user = new User();
        $userForm = $this->createForm(UserType::class, $user);
        $userForm->handleRequest($request);
        $error = null;

        if($userForm->isSubmitted()){
            //validate Username
            $username = $userRepo->findOneBy(array('username'=>$user->getUsername()));
            if($username != null){
                $error = "Потребителското име е заето!";
                $user->setUsername("");
                goto escape;
            }
            if(!$user->isValidUsername()){
                $error= "Невалидно потребителско име!";
                $user->setUsername("");
                goto  escape;
            }
            //end validate username
            //validate email
            $email = $userRepo->findOneBy(array('email'=>$user->getEmail()));
            if($email != null){
                $error = "E-Mail адресът е зает!";
                $user->setEmail("");
                goto  escape;
            }
            if (!filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
                $error = "Невалиден E-Mail адрес!";
                $user->setEmail("");
                goto  escape;
            }
            //end email validation
            //password validation
            if($user->getPassword() != $user->getConfPassword()){
                $error = "Паролите не съвпадат";
                goto  escape;
            }
            if(strlen($user->getPassword()) < Config::$PASSWORD_MIN_LEN){
                $error = "Паролата е под 6 знака";
                goto escape;
            }

            $user->setPassword($this->get('security.password_encoder')->encodePassword($user, $user->getPassword()));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            //IMPORTANT SET UP CART
            $userDb = $this->getDoctrine()->getRepository(User::class)->findOneBy(array('username'=>$user->getUsername()));
            $shoppingCart = new ShoppingCart();
            $shoppingCart->setUserId($userDb->getId());
            $shoppingCart->setRawProducts("");

            $entityManager->persist($shoppingCart);
            $entityManager->flush();

            return $this->redirectToRoute("security_login");

        }

        escape:

        return $this->render("default/login-register.html.twig", array(
            "formToLoad"=>'register',
            "userform"=>$user,
            'form'=>$userForm->createView(),
            "error" => $error
        ));

    }

    /**
     * This is the route the user can use to logout.
     *
     * But, this will never be executed. Symfony will intercept this first
     * and handle the logout automatically. See logout in app/config/security.yml
     *
     * @Route("/logout/", name="security_logout")
     */
    public function logoutAction()
    {
        throw new \Exception('This should never be reached!');
    }


}
<?php

namespace AppBundle\Controller;

use AppBundle\Constant\Config;
use AppBundle\Constant\ConstantValues;
use AppBundle\Entity\HomeFlexBanner;
use AppBundle\Entity\Notification;
use AppBundle\Entity\PasswordRecovery;
use AppBundle\Entity\ProductCategory;
use AppBundle\Entity\ProductOrder;
use AppBundle\Entity\SocialLink;
use AppBundle\Entity\User;
use AppBundle\Form\ProductCategoryType;
use AppBundle\Repository\SocialLinkRepository;
use AppBundle\Service\CartManager;
use AppBundle\Service\DoctrineNotificationManager;
use AppBundle\Service\TwigInformer;
use AppBundle\Service\ProductManager;
use AppBundle\Util\CharacterTranslator;
use AppBundle\Util\DirectoryCreator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swift_TransportException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class DefaultController extends Controller
{

    //TODO WHEN UPDATING schema make sure that you remove the ProductCategoryForCreationClass
    /**
     * @Route("/", name="homepage")
     * @param Request $request
     * @param ProductManager $productManager
     * @param TwigInformer $informator
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, ProductManager $productManager, TwigInformer $informator)
    {

        $informator->setError($request->get('error'));
        // replace this example code with whatever you need
        $banners = $this->getDoctrine()->getRepository(HomeFlexBanner::class)->findBy(array('hidden' => false));

        $trendingProds = $productManager->getTrendingProducts(ConstantValues::$MAX_PRODUCTS_PER_SLIDE, rand(0, 10));
        $trendingProdsP2 = $productManager->getTrendingProducts(ConstantValues::$MAX_PRODUCTS_PER_SLIDE, rand(10, 20));
        $newProds = $productManager->getNewestProducts(ConstantValues::$MAX_PRODUCTS_PER_SLIDE, rand(0, 50));
        $newProdsP2 = $productManager->getNewestProducts(ConstantValues::$MAX_PRODUCTS_PER_SLIDE, rand(50, 110));

        return $this->render('default/index.html.twig', [
            'flexBanners' => $banners,
            'trendingProds' => $trendingProds,
            'trendingProdsP2' => $trendingProdsP2,
            'newProds' => $newProds,
            'newProdsP2' => $newProdsP2,
        ]);
    }


    /**
     * @Route("/contacts", name="contacts")
     * @param Request $request
     * @param DoctrineNotificationManager $notificationManager
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function contactsAction(Request $request, DoctrineNotificationManager $notificationManager)
    {
        $error = null;
        $info = null;

        $name = $request->get('name');
        $email = $request->get('email');
        $message = $request->get('body');

        if ($name != null) {
            if ($email == null || $message == null) {
                $error = "Поля попълнете всички полета!";
                goto escape;
            }

            $msgSkeleton = ConstantValues::$NEW_CONTACT_US_MESSAGE;
            $msg = preg_replace('/{{question}}/', $message, $msgSkeleton);
            $msg = preg_replace('/{{email}}/', $email, $msg);
            $msg = preg_replace('/{{name}}/', $name, $msg);

            $notification = new Notification();
            $notification->setNotificationType("Запитване");
            $notification->setContent($msg);

            $notificationManager->sendToAdmins($notification);

            $info = "Вашето запитване беше изпратено!";
        }

        escape:

        return $this->render("default/contact-us.html.twig",
            [
                'error' => $error,
                'success' => $info,
            ]);
    }

    /**
     * @Route("/about-us", name="about_us")
     */
    public function aboutUsPage()
    {

        return $this->render('default/about-us.html.twig');
    }


    /**
     * @Route("/user/recover/{userSearchParam}", name="recover_user_password", defaults={"userSearchParam"=null})
     * @param Request $request
     * @param $userSearchParam
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function recoverPasswordAction(Request $request, $userSearchParam)
    {
        if ($this->getUser() != null)
            return $this->redirectToRoute('homepage');

        $error = $request->get('error');
        $success = $request->get('success');

        $repo = $this->getDoctrine()->getRepository(User::class);
        $user = null;
        $hiddenMail = "";
        if ($userSearchParam != null) {
            $user = $repo->findOneBy(array('id' => $userSearchParam));
            if ($user == null)
                $user = $repo->findOneBy(array('username' => $userSearchParam));
            if ($user == null)
                $user = $repo->findOneBy(array('email' => $userSearchParam));

            if ($user == null) {
                $error = "Не беше намерен потребител";
                goto  escape;
            } else {
                $success = "Потребителя беше намерен";
                $hiddenMail = preg_replace("/.*?@/", "********", $user->getEmail());
            }
        }

        escape:

        return $this->render('user-related/recover-password.html.twig',
            [
                'user' => $user,
                'error' => $error,
                'success' => $success,
                'hiddenMail' => $hiddenMail,
                'searchParam' => $userSearchParam
            ]);
    }

    /**
     * @Route("/user/recovery/sendCode/{userId}", name="recovery_send_code", defaults={"userId"=null})
     * @param $userId
     * @param \Swift_Mailer $mailer
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function sendCodeToUserAction($userId, \Swift_Mailer $mailer)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(array('id' => $userId));
        if ($user == null)
            return $this->redirectToRoute('homepage');
        $entityManager = $this->getDoctrine()->getManager();

        $passwordRecovery = new PasswordRecovery();
        $passwordRecovery->setUserId($user->getId());

        $entityManager->persist($passwordRecovery);
        $entityManager->flush();

        //send mail
        $message = (new \Swift_Message("Забравена парола - Сибирско Здраве - Монтана"))
            ->setFrom([Config::$MAILER_EMAIL_ADDRESS => Config::$MAILER_DISPLAY_NAME])
            ->setTo($user->getEmail())
            ->setBody($this->renderView(
                'mailing/forgotten-password.html.twig',
                array('user' => $user, 'recoveryCode' => $passwordRecovery->getCode())
            ),
                'text/html');

        try {
            $mailer->send($message);
            $spool = $mailer->getTransport()->getSpool();
            $transport = $this->get('swiftmailer.transport.real');

            $spool->flushQueue($transport);
        } catch (Swift_TransportException $e) {
            return $this->redirectToRoute('recovery_retrieve_code', ['error' => "Имаше проблем с изпращането на адреса, моля свържете се с нас!"]);
        }
        //end send mail

        return $this->redirectToRoute('recovery_retrieve_code', ['success' => "Кодът за връщане на акаунта ви беше изпратен. Проверете в пощата."]);

    }

    /**
     * @Route("/user/recovery/enterCode", name="recovery_retrieve_code")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function retrieveCodeAction(Request $request)
    {
        $code = $request->get('code');
        $username = $request->get('username');
        $isCodeValid = false;
        $error = $request->get('error');
        $success = $request->get('success');

        if ($code != null) {
            $passwordRecovery = $this->getDoctrine()->getRepository(PasswordRecovery::class)->findOneBy(array('code' => $code));
            if ($passwordRecovery == null || time() - $passwordRecovery->getTime() > Config::$PASSWORD_RECOVERY_LEASE_TIME || $passwordRecovery->getIsUsed()) {
                $error = "Невалиден или вече употребяван код";
                goto  escape;
            }

            $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(array('id' => $passwordRecovery->getUserId()));
            if ($user == null || $username != $user->getUsername()) {
                $error = "Невалидно потребилтеско име!";
                goto escape;
            }

            $isCodeValid = true;
            $success = "Въведете нова парола";

        }

        escape:

        return $this->render('user-related/retreive-code-change-password.html.twig',
            [
                'code' => $code,
                'error' => $error,
                'success' => $success,
                'isCodeValid' => $isCodeValid,
            ]);
    }

    /**
     * @Route("/user/recovery/commit/{code}", name="recovery_commit_code", defaults={"code"=null})
     * @param Request $request
     * @param $code
     * @param UserPasswordEncoderInterface $encoder
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function commitRecoverPassword(Request $request, $code, UserPasswordEncoderInterface $encoder)
    {
        $passwordRecovery = $this->getDoctrine()->getRepository(PasswordRecovery::class)->findOneBy(array('code' => $code));
        if ($passwordRecovery == null || time() - $passwordRecovery->getTime() > Config::$PASSWORD_RECOVERY_LEASE_TIME || $passwordRecovery->getIsUsed()) {
            $error = "Невалиден или вече употребяван код";
            return $this->redirectToRoute('recovery_retrieve_code', ['error' => $error]);
        }

        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(array('id' => $passwordRecovery->getUserId()));
        if ($user == null) {
            $error = "Потребителя не съществува";
            return $this->redirectToRoute('recovery_retrieve_code', ['error' => $error]);
        }

        $password = $request->get('new-password');
        $passwordConf = $request->get('conf-password');
        if ($password == null || strlen($password) < 6) {
            $error = "Паролата трябва да е поне 6 знака";
            return $this->redirectToRoute('recovery_retrieve_code', ['error' => $error, 'code' => $code, 'username' => $user->getUsername()]);
        }

        if ($password != $passwordConf) {
            $error = "Паролите не съвпадат";
            return $this->redirectToRoute('recovery_retrieve_code', ['error' => $error, 'code' => $code, 'username' => $user->getUsername()]);
        }


        $passwordNew = $encoder->encodePassword($user, $password);
        $user->setPassword($passwordNew);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->merge($user);
        $entityManager->flush();

        $recoveries = $this->getDoctrine()->getRepository(PasswordRecovery::class)->findBy(array('userId' => $user->getId()));
        foreach ($recoveries as $recovery)
            $entityManager->remove($recovery);
        $entityManager->flush();

        return $this->redirectToRoute('security_login');

    }

    /**
     *  Route("/order-email", name="about_email")
     */
    /* public function mailAction(CartManager $cartManager){
         $order = $this->getDoctrine()->getRepository(ProductOrder::class)->findOneBy(array());
         $forgetProds = $cartManager->forgeProductsFromCookie(json_decode($order->getShoppingCart()), $this->getDoctrine()->getManager());
         return $this->render('mailing/order-accepted-message.html.twig',
             [
                 'order'=>$order,
                 'forgedProducts'=>$forgetProds,
             ]);
     }
 */

}

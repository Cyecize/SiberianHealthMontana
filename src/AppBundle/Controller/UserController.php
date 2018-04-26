<?php
/**
 * Created by PhpStorm.
 * User: cyecize
 * Date: 25.4.2018 г.
 * Time: 17:48
 */

namespace AppBundle\Controller;

use AppBundle\Entity\UserAddress;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
        $addresses = $this->getDoctrine()->getRepository(UserAddress::class)->findBy(array('userId'=>$user->getId()));


        return $this->render('user-related/profile-page.html.twig', [
            'addresses'=>$addresses,
        ]);
    }


}
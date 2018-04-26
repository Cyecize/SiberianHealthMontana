<?php
/**
 * Created by PhpStorm.
 * User: cyecize
 * Date: 25.4.2018 г.
 * Time: 17:48
 */

namespace AppBundle\Controller;

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
     */
    public function indexAction(Request $request)
    {


        return $this->render('user-related/profile-page.html.twig', [

        ]);
    }


}
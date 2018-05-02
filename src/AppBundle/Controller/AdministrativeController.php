<?php
/**
 * Created by PhpStorm.
 * User: Cyecize
 * Date: 2/13/2018
 * Time: 10:33 AM
 */

namespace AppBundle\Controller;

use AppBundle\Constant\Config;
use AppBundle\Constant\ConstantValues;
use AppBundle\Constant\PathConstants;
use AppBundle\Entity\HomeFlexBanner;
use AppBundle\Entity\Product;
use AppBundle\Entity\ProductCategory;
use AppBundle\Entity\ProductCategoryForCreation;
use AppBundle\Entity\SocialLink;
use AppBundle\Entity\User;
use AppBundle\Entity\UserAddress;
use AppBundle\EventListener\LoginListener;
use AppBundle\Form\ProductCategoryType;
use AppBundle\Form\ProductType;
use AppBundle\Repository\SocialLinkRepository;
use AppBundle\Service\TwigInformer;
use AppBundle\Service\ProductManager;
use AppBundle\Util\CharacterTranslator;
use AppBundle\Util\DirectoryCreator;
use AppBundle\Util\ImageCompressorManager;
use AppBundle\Util\ImgCompressor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use function Symfony\Component\DependencyInjection\Tests\Fixtures\factoryFunction;
use Symfony\Component\HttpFoundation\Request;


class AdministrativeController extends Controller
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
     * @Route("/admin", name="admin_panel")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function adminPanelAction(Request $request)
    {
        if (!$this->isUserPrivileged($this->getUser()))
            return $this->redirectToRoute('homepage');


        return $this->render('administrative/admin-panel.html.twig',
            [
                'error' => $request->get('error'),
                'success' => $request->get('success'),
            ]);
    }

    /**
     * @Route("/admin/newCategory", name="create_new_category")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function newCategoryAction(CharacterTranslator $translator, Request $request, DirectoryCreator $directoryCreator)
    {
        if (!$this->isUserPrivileged($this->getUser()))
            return $this->redirectToRoute('homepage');
        $error = null;
        $allCategories = $this->getDoctrine()->getRepository(ProductCategory::class)->findBy(array(), array('parentId' => "ASC"));

        $category = new ProductCategory();
        $form = $this->createForm(ProductCategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $parentCat = null;
            if ($category->getParentId() != 0) {
                $parentCat = $this->getDoctrine()->getRepository(ProductCategory::class)
                    ->findOneBy(array('id' => $category->getParentId()));
                if ($parentCat == null) {
                    $error = "Невалидна бащина категория!";
                    goto  escape;
                }

            }

            if ($category->getCategoryName() == "" || $category->getCategoryName() == null) {
                $error = "Невалидно име!";
                goto  escape;
            }
            $category->setLatinName($translator->convertFromCyrilicToLatin($category->getCategoryName()));
            $entityManager = $this->getDoctrine()->getManager();
            $cat = new ProductCategoryForCreation();

            $cat->setCategoryName($category->getCategoryName());
            $cat->setParentId($category->getParentId());
            $cat->setLatinName($category->getLatinName());

            $entityManager->persist($cat);
            $entityManager->flush();

            $directoryCreator::createCategoryDirectory($category->getLatinName());

            return $this->redirectToRoute("admin_panel");
        }


        escape:

        return $this->render('administrative/add-category.html.twig',
            [
                'error' => $error,
                'categories' => $allCategories,
                'form' => $form->createView(),
            ]);
    }


    /**
     * @Route("/admin/renameCategory", name="rename_category")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function renameCategoryAction(Request $request)
    {
        if (!$this->isUserPrivileged($this->getUser()))
            return $this->redirectToRoute('homepage');

        $error = null;
        $allCategories = $this->getDoctrine()->getRepository(ProductCategory::class)->findBy(array(), array('parentId' => "ASC"));

        $currentCat = new ProductCategory();
        $form = $this->createForm(ProductCategoryType::class, $currentCat);
        $form->handleRequest($request);


        //PARENT IS IS THE ACTUAL ID
        if ($form->isSubmitted()) {
            $actualCat = $this->getDoctrine()->getRepository(ProductCategory::class)
                ->findOneBy(array('id' => $currentCat->getParentId()));
            if ($actualCat == null) {
                $error = "Невалидна категория!";
                goto  escape;
            }


            if ($currentCat->getCategoryName() == "" || $currentCat->getCategoryName() == null) {
                $error = "Невалидно име!";
                goto  escape;
            }

            $actualCat->setCategoryName($currentCat->getCategoryName());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->merge($actualCat);
            $entityManager->flush();

            return $this->redirectToRoute('homepage', ['error' => "Успещно променихте името на " . $actualCat->getCategoryName()]);
        }

        escape:

        return $this->render('administrative/rename-category.html.twig',
            [
                'error' => $error,
                'categories' => $allCategories,
                'form' => $form->createView(),
            ]);
    }

    /**
     * @Route("/admin/focused-link/{id}", name="get_focused_social_link", defaults={"id"=null})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editSocialLinkAction(Request $request, $id)
    {
        if (!$this->isUserPrivileged($this->getUser()))
            return $this->redirectToRoute('homepage');
        if ($id == null)
            return $this->redirectToRoute('admin_panel');
        $link = $this->getDoctrine()->getRepository(SocialLink::class)->find($id);

        $editLink = new SocialLink();
        // $form = $this->createForm()
        return $this->render('queries/get-social-link-to-edit.html.twig',
            [
                "link" => $link
            ]);
    }

    /**
     * @Route("/admin/edit-social-links", name="edit_social_links", defaults={"id"=null})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editSocialLinksAction(Request $request)
    {
        return $this->render('administrative/edit-social-links.html.twig',
            [

            ]);
    }


    /**
     *
     * @Route("/admin/addNewProduct", name="add_new_product")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @param TwigInformer $basicInformator
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addNewProductAction(Request $request, TwigInformer $basicInformator)
    {
        if (!$this->isUserPrivileged($this->getUser()))
            return $this->redirectToRoute('homepage');

        $error = null;
        $allCategories = $this->getDoctrine()->getRepository(ProductCategory::class)->findAll();
        $product = new Product();
        $product->setCategoryId(-10);

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $isCatExist = false;
            foreach ($allCategories as $category) {
                if ($category->getId() == $product->getCategoryId()) {
                    $product->setFatherCategory($category);
                    $product->setCurrentCategory($category);
                    $isCatExist = true;
                }
            }
            if (!$isCatExist) {
                $error = "Няма такава категория!с ID: " . $product->getCategoryId();
                goto escape;
            }

            $prodRepo = $this->getDoctrine()->getRepository(Product::class);
            $sibirCode = $product->getSibirCode();
            $existingProd = $prodRepo->findOneBy(array('sibirCode' => $sibirCode));
            if ($existingProd != null) {
                $error = "Има продукт с този сибирски код!";
                goto escape;
            }

            //begin image processing
            $imageName = $_FILES['image']['name']['img_file'];
            $tmpImgName = $_FILES['image']['tmp_name']['img_file'];
            if ($imageName == null || (!ImageCompressorManager::isValidImage('img_file'))) {
                $error = "Имаше проблем с качването на снимката! Изберете Jpg или Png с разбер до " . ConstantValues::$MAX_UPLOAD_FILESIZE;
                goto escape;
            }

            $imageSize = $_FILES['image']['size']['img_file'];
            if ($imageSize > ConstantValues::$MAX_UPLOAD_FILESIZE) {
                $error = "Файлът не трябва да надвишава 6MB., тoзи е " . round($imageSize / 1024 / 1024, 2);
                goto escape;
            }

            $tempDestination = PathConstants::$TEMPORARY_UPLOAD_DIRECTORY . $imageName;
            move_uploaded_file($tmpImgName, $tempDestination);

            //compressing
            $setting = ImageCompressorManager::createSettingsForImage(PathConstants::$TEMPORARY_OUTPUT_DIRECTORY);
            $compressor = new ImgCompressor($setting);
            $compressedImgName = ImageCompressorManager::compress($compressor, $tempDestination);
            //end compressing


            $product->setImgPath(md5($compressedImgName) . ".jpg");

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();

            $productAfterFlush = $prodRepo->findOneBy(array('sibirCode' => $product->getSibirCode()));
            DirectoryCreator::createProductDirectory($productAfterFlush->getId(), $productAfterFlush->getFatherCategory()->getLatinName());

            //relate product with category
            $this->insertProductCategoryRelation($productAfterFlush->getId(), $productAfterFlush->getCategoryId());

            copy(PathConstants::$TEMPORARY_OUTPUT_DIRECTORY . $compressedImgName, PathConstants::$CATEGORIES_PATH . $productAfterFlush->getFatherCategory()->getLatinName() . DIRECTORY_SEPARATOR . $productAfterFlush->getId() . DIRECTORY_SEPARATOR . md5($compressedImgName) . '.jpg');
            unlink($tempDestination);  //erase temp file
            unlink(PathConstants::$TEMPORARY_OUTPUT_DIRECTORY . $compressedImgName); //erase temp output file
            //end image processing


            $basicInformator->setError("Успешно добавен продукт");
            return $this->redirectToRoute('admin_panel');
        }


        escape:

        return $this->render('administrative/add-product.html.twig',
            [
                'error' => $error,
                'categories' => $allCategories,
                'form' => $form->createView(),
                'product' => $product
            ]);

    }

    /**
     * @Route("/admin/product/edit", name="edit_product")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editProductAction(Request $request)
    {
        if (!$this->isUserPrivileged($this->getUser()))
            return $this->redirectToRoute('homepage');
        $allCategories = $this->getDoctrine()->getRepository(ProductCategory::class)->findAll();
        $error = null;


        $product = new Product();
        $productId = $request->get('productId');
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $productToEdit = $this->getDoctrine()->getRepository(Product::class)->findOneBy(array('id' => $productId));
            if ($productToEdit == null) {
                $error = "Несъществуващ продукт!";
                goto escape;
            }

            $productToEdit->setTitle($product->getTitle());
            $productToEdit->setSibirCode($product->getSibirCode());
            $productToEdit->setPrice($product->getPrice());
            $productToEdit->setDescription($product->getDescription());
            $productToEdit->setManufacturer($product->getManufacturer());
            $productToEdit->setQuantity($product->getQuantity());
            $productToEdit->setSoldCount($product->getSoldCount());
            $productToEdit->setHidden($product->getHidden());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->merge($productToEdit);
            $entityManager->flush();


            //start image processing
            $imageName = $_FILES['image']['name']['img_file'];
            $tmpImgName = $_FILES['image']['tmp_name']['img_file'];
            if($imageName != null) /* image is posted */ {
                if(!ImageCompressorManager::isValidImage('img_file')){
                    $error = "Имаше проблем с качването на снимката";
                    goto  escape;
                }

                $imageSize = $_FILES['image']['size']['img_file'];
                if ($imageSize > ConstantValues::$MAX_UPLOAD_FILESIZE) {
                    $error = "Файлът не трябва да надвишава 6MB., тoзи е " . round($imageSize / 1024 / 1024, 2);
                    goto escape;
                }

                $tempDestination = PathConstants::$TEMPORARY_UPLOAD_DIRECTORY . $imageName;
                move_uploaded_file($tmpImgName, $tempDestination);

                //compressing
                $setting = ImageCompressorManager::createSettingsForImage(PathConstants::$TEMPORARY_OUTPUT_DIRECTORY);
                $compressor = new ImgCompressor($setting);
                $compressedImgName = ImageCompressorManager::compress($compressor, $tempDestination);
                //end compressing

                copy(PathConstants::$TEMPORARY_OUTPUT_DIRECTORY . $compressedImgName, PathConstants::$CATEGORIES_PATH . $productToEdit->getFatherCategory()->getLatinName() . DIRECTORY_SEPARATOR . $productToEdit->getId() . DIRECTORY_SEPARATOR . md5($compressedImgName) . '.jpg');
                unlink($tempDestination);
                unlink(PathConstants::$TEMPORARY_OUTPUT_DIRECTORY . $compressedImgName); //erase temp output file

                $oldImageName = $productToEdit->getImgPath();
                $productToEdit->setImgPath(md5($compressedImgName) . ".jpg");

                $entityManager->merge($productToEdit);
                $entityManager->flush();

                try {
                    unlink(PathConstants::$CATEGORIES_PATH . $productToEdit->getFatherCategory()->getLatinName() . DIRECTORY_SEPARATOR . $productToEdit->getId() . DIRECTORY_SEPARATOR . $oldImageName);
                }
                catch (\Exception $e){
                }
            }

            return $this->redirectToRoute('admin_panel');
        }

        escape:

        return $this->render('administrative/edit-product.html.twig',
            [
                'error' => $error,
                'categories' => $allCategories,
                'form' => $form->createView(),
                'product' => $product,
            ]);
    }

    /**
     * @Route("/admin/category/relation/remove", name="remote_relation")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function removeRelationAction(Request $request)
    {
        if (!$this->isUserPrivileged($this->getUser()))
            return $this->redirectToRoute('homepage');

        $catId = $request->get('catId');
        $prodId = $request->get('prodId');

        $category = $this->getDoctrine()->getRepository(ProductCategory::class)->findOneBy(array('id' => $catId));
        $product = $this->getDoctrine()->getRepository(Product::class)->findOneBy(array('id' => $prodId));

        if ($category != null && $product != null) {
            $em = $this->getDoctrine()->getManager(); // ...or getEntityManager() prior to Symfony 2.1
            $connection = $em->getConnection();
            $statement = $connection->prepare("DELETE FROM product_category_product WHERE product_category_id = $catId AND product_id = $prodId");
            $statement->execute();
        }

        return $this->render('queries/generic-query-aftermath-message.twig', ['error' => ""]);
    }


    /**
     *
     * @Route("/admin/searchProdBySibirCode/{code}", name="search_prod_by_sibir_code", defaults={"code"=null})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function searchProdBySibirCodeAction(Request $request, $code)
    {
        if (!$this->isUserPrivileged($this->getUser()))
            return $this->redirectToRoute('homepage');

        $error = ['status' => 0, 'productId' => null, 'productName' => null];
        if ($code == null) {
            $error['status'] = 404;
        }
        $prod = $this->getDoctrine()->getRepository(Product::class)->findOneBy(array('sibirCode' => $code));
        if ($prod == null) {
            $error['status'] = 404;
        } else {
            $error['status'] = 200;
            $error['productId'] = $prod->getId();
            $error['productName'] = $prod->getTitle();
        }

        $error = json_encode($error);
        return $this->render('queries/generic-query-aftermath-message.twig',
            [
                'error' => $error,
            ]);
    }

    /**
     * @Route("/admin/searchById/", name="search_prod_by_id", methods={"POST"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function searchProdByIdAction(Request $request)
    {
        if (!$this->isUserPrivileged($this->getUser()))
            return $this->redirectToRoute('homepage');

        $prodId = $request->get('productId');
        $error = ['status' => 0,
            'productId' => null,
            'productName' => null,
            'siberianCode' => null,
            'price' => null,
            'description' => null,
            'manufacturer' => null,
            'quantity' => null,
            'soldCount' => null,
            'hidden' => null,
            'categories' => array(),
        ];
        if ($prodId == null) {
            $error['status'] = 404;
        }
        $prod = $this->getDoctrine()->getRepository(Product::class)->findOneBy(array('id' => $prodId));
        if ($prod == null) {
            $error['status'] = 404;
        } else {
            $error['status'] = 200;
            $error['productId'] = $prod->getId();
            $error['productName'] = $prod->getTitle();
            $error['siberianCode'] = $prod->getSibirCode();
            $error['price'] = $prod->getPrice();
            $error['description'] = $prod->getDescription();
            $error['manufacturer'] = $prod->getManufacturer();
            $error['quantity'] = $prod->getQuantity();
            $error['soldCount'] = $prod->getSoldCount();
            $error['hidden'] = $prod->getHidden();

            $categories = $prod->getCategories();
            foreach ($categories as $category)
                if ($category->getId() != $prod->getCategoryId())
                    $error['categories'][] = ['id' => $category->getId(), 'name' => $category->getCategoryName()];
        }

        $error = json_encode($error);
        return $this->render('queries/generic-query-aftermath-message.twig',
            [
                'error' => $error,
            ]);
    }

    /**
     * @Route("/admin/requestProductCatRelation", name="relate_product_cat", methods={"POST"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function relateProdCatAction(Request $request)
    {
        if (!$this->isUserPrivileged($this->getUser()))
            return $this->redirectToRoute('homepage');
        $params = $request->get('product_rel');

        $prodId = intval($params['productId']);
        $catId = intval($params['categoryId']);

        if ($prodId == null || $prodId < 1 || $catId == null || $catId < 0) {
            return $this->redirectToRoute('homepage', ['error' => "Грешка при валидация!"]);
        }

        $this->insertProductCategoryRelation($prodId, $catId);

        return $this->redirectToRoute('admin_panel');
    }


    /**
     * @Route("/admin/users", name="manage_users")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function manageUsersAction(Request $request)
    {
        $error = $request->get('error');
        $success = $request->get('success');
        $user = $this->getUser();
        if ($user->getAuthorityLevel() > Config::$ADMINISTRATOR_LEVEL)
            return $this->redirectToRoute('admin_panel', ['error' => "Нямате права"]);


        escape:

        return $this->render('administrative/manage-users.html.twig', [
            'error' => $error,
            'success' => $success,
        ]);
    }

    /**
     * @Route("/admin/users/findByParam", name="find_user_by_username_id_email")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchUserByNameOrId(Request $request)
    {
        $param = $request->get('searchParam');

        $error = ['status' => 0, 'message' => null, 'userId' => null, 'username' => null];
        if ($this->getUser()->getAuthorityLevel() > Config::$ADMINISTRATOR_LEVEL) {
            $error['status'] = 401;
            $error['message'] = "Unauthorized";
            goto escape;
        }

        $repo = $this->getDoctrine()->getRepository(User::class);
        $user = $repo->findOneBy(array('id' => $param));
        if ($user == null)
            $user = $repo->findOneBy(array('username' => $param));
        if ($user == null)
            $user = $repo->findOneBy(array('email' => $param));

        if ($user == null) {
            $error['status'] = 404;
            $error['message'] = "Not Found";
            goto escape;
        }

        $error['status'] = 200;
        $error['message'] = "OK";
        $error['username'] = $user->getUsername();
        $error['userId'] = $user->getId();


        escape:

        return $this->render('queries/generic-query-aftermath-message.twig',
            [
                'error' => json_encode($error),
            ]);
    }

    /**
     * @Route("/admin/users/destroy", name="admin_destroy_user")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function destroyUserAction(Request $request){
        $userId = $request->get('userId');

        $error = ['status' => 0, 'message' => null];
        if ($this->getUser()->getAuthorityLevel() > Config::$ADMINISTRATOR_LEVEL) {
            $error['status'] = 401;
            $error['message'] = "Unauthorized";
            goto escape;
        }

        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(array('id'=>$userId));
        if($user == null){
            $error['status'] = 404;
            $error['message'] = "Not found (user) with id $userId";
            goto escape;
        }

        if($user->getAuthorityLevel() == Config::$ADMINISTRATOR_LEVEL){
            $error['status'] = 401;
            $error['message'] = "Cannot Remove Administrator";
            goto escape;
        }

        $addresses = $this->getDoctrine()->getRepository(UserAddress::class)->findBy(array('userId' => $user->getId()));

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user->getCart());
        $entityManager->remove($user);
        foreach ($addresses as $address)
            $entityManager->remove($address);
        $entityManager->flush();

        $error['status'] = 200;
        $error['message'] = "User with Id $userId has been removed";

        escape:

        return $this->render('queries/generic-query-aftermath-message.twig',
            [
               'error'=>json_encode($error),
            ]);
    }

    /**
     * @Route("/admin/users/all", name="admin_get_all_users")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAllUsers(){
        $res = [];

        if($this->getUser()->getAuthorityLevel() > Config::$ADMINISTRATOR_LEVEL){
            goto  escape;
        }

        $users = $this->getDoctrine()->getRepository(User::class)->findAll();
        foreach ($users as $user)
            $res[] = ['userId'=>$user->getId(), 'username'=>$user->getUsername(), 'level'=>$user->getAuthorityLevel()];

        escape:

        return $this->render('queries/generic-query-aftermath-message.twig',
            [
               'error'=>json_encode($res),
            ]);
    }


    /**
     * @param int $prodId
     * @param int $catId
     * @return void
     */
    private function insertProductCategoryRelation(int $prodId, int $catId): void
    {
        $connection = $this->getDoctrine()->getConnection();
        $statement = $connection->prepare("INSERT INTO product_category_product VALUES ($catId, $prodId)");
        $statement->execute();
    }
}
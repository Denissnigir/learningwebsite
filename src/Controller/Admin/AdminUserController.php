<?php

namespace App\Controller\Admin;


use App\Entity\User;
use App\Form\UserType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdminUserController extends AdminBaseController
{
    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }

    /**
     * @Route("/admin/user", name="admin_user")
     * @return Response
     */
    public function index(){
        $users = $this->managerRegistry->getRepository(User::class)->findAll();

        $forRender = parent::renderDefault();
        $forRender['title'] = 'Пользователи';
        $forRender['users'] = $users;
        return $this->render('admin/user/index.html.twig', $forRender);
    }

    /**
     * @Route("admin/user/create", name="admin_user_create")
     * @param Request $request
     * @param UserPasswordHasher $passwordHasher
     * @return RedirectResponse|Response
     */
    public function create(Request $request, UserPasswordHasherInterface $passwordHasher){
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $em = $this->managerRegistry->getManager();
        $form->handleRequest($request);
        if(($form->isSubmitted()) && ($form->isValid())){
            $password = $passwordHasher->hashPassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $user->setRoles(["ROLE_ADMIN"]);
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('admin_user');
        }

        $forRender = parent::renderDefault();
        $forRender['title'] = 'Форма создания пользователя';
        $forRender['form'] = $form->createView();
        return $this->render('admin/user/form.html.twig', $forRender);
    }
}
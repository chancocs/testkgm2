<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Form\Type\SchoolFinderType;
use AppBundle\Entity\User;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        return $this->redirectToRoute('school', array(), 301);
    }
    /**
     * @Route("/schoollist_ajax", name="school_ajax", condition="request.isXmlHttpRequest()",options={"expose"=true})
     */
    public function listSchoolsAction(Request $request){//this method will only be called through ajax
        $id = $request->query->get('id');
        $schools = $this->getDoctrine()->getRepository('AppBundle:School')->findByIddistrict($id);

        /*add the list of schools to the session
        The reason we want to do this is because select lists populated using ajax always cause $form->isValid()
         to return false. Therefore, we need to change the 'choices' attribute of this element in the SchoolFinderType
         form class to include the new list.
        since we are using a FlashBag, this session variable will be cleared after the next request
        */
        $this->get('session')->getFlashBag()->set('schoolList', $schools);
        return $this->render('school/schoollist.html.twig', array('schools'=>$schools));
    }
    /**
     * @Route("/school", name="school")
     */
    public function schoolAction(Request $request){
        $session = $request->getSession();
        if($session->has('emiscode')){
            return $this->redirectToRoute('school_main', array('emisCode'=>$session->get('emiscode')), 301);
        }
        return $this->render('school/school.html.twig');
    }
    /**
     * @Route("/findSchoolForm", name="find_school_form")
     */
    public function schoolSelectFormAction(Request $request){
        $schoolFinderForms = $this->container->get('school_finder');
        $formAction = $this->generateUrl('find_school_form');
        $schoolFinderForms->createForms($formAction);
        $schoolFinderForms->processForms();
        
        if($schoolFinderForms->areValid()){
            return $this->redirectToRoute('school_main',array('emisCode'=>$schoolFinderForms->getSchoolId()), 301);
        }
        $schoolName = "";
        if($request->getSession()->has('emiscode')){
            $schoolName = $request->getSession()->get('school_name');
        }
        return $this->render('school/findschoolform.html.twig',
                             array('form2' => $schoolFinderForms->getView2(),
                                    'form1' => $schoolFinderForms->getView1(),
                                    'error'=>$schoolFinderForms->getError(),
                                    'schoolName' => $schoolName,
                                    )                                   
                             );
    }
    /**
     * @Route("/zone", name="zone")
     */
    public function zoneAction(){
        return $this->render('zone/zone.html.twig');
    }
    /**
     * @Route("/district", name="district")
     */
    public function districtAction(){
        return $this->render('district/district.html.twig');
    }
    /**
     * @Route("/national", name="national")
     */
    public function nationalAction(){
        return $this->render('national/national.html.twig');
    }
    public function removeTrailingSlashAction(Request $request){
        $pathInfo = $request->getPathInfo();
        $requestUri = $request->getRequestUri();
        $url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);
        return $this->redirect($url, 301);
    }
    /**
     * @Route("/add_user", name="add_user")
     */
    public function addUserAction(){
        $em = $this->getDoctrine()->getManager();
        $user = new User();
        $plainPassword = 'jonathanpass';
        $encoder = $this->container->get('security.password_encoder');
        $encoded = $encoder->encodePassword($user, $plainPassword);
        $user->setPassword($encoded);
        $user->setUsername('jonathan');
        $user->setEmail('jonathanmojoo@yahoo.com');
        $user->setEnabled(true);

        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('homepage', array(), 301);
    }
}

<?php

namespace Ml\ServiceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Ml\ServiceBundle\Entity\Service;
use Ml\ServiceBundle\Entity\Carpooling;
use Ml\ServiceBundle\Entity\CarpoolingUser;
use Ml\ServiceBundle\Form\CarpoolingType;
use Ml\ServiceBundle\Entity\CouchSurfing;
use Ml\ServiceBundle\Entity\CouchSurfingUser;
use Ml\ServiceBundle\Form\CouchSurfingType;
use Ml\ServiceBundle\Entity\Sale;
use Ml\ServiceBundle\Entity\SaleUser;
use Ml\ServiceBundle\Form\SaleType;
use Ml\ServiceBundle\Entity\Basic;
use Ml\ServiceBundle\Entity\BasicUser;
use Ml\ServiceBundle\Form\BasicType;
use Ml\UserBundle\Entity\User;
use Ml\TransactionBundle\Entity\BasicEval;
use Ml\TransactionBundle\Entity\CouchsurfingEval;
use Ml\TransactionBundle\Entity\SaleEval;
use Ml\TransactionBundle\Entity\CarpoolingEval;
use Ml\ServiceBundle\Entity\Document;

/**
 * Service Controller extending Controller
 * This one is used to manage service (create, delete, reserve a service) and to display them
 */
class ServiceController extends Controller {

	/**
	 * Display all services and data
	 * @return Twig MlServiceBundle:Service:index.html.twig
	 */
	public function indexAction() {
		/* Test connexion */
		$req = $this->get('request');	
		
		try {		
		    $login = $this->container->get('ml.session')->sessionExist($req);
		}
		catch (\Exception $e) {
		    return $this->redirect($this->generateUrl('ml_user_add'));		    
		}
		
		$user = $this->getDoctrine()
				->getManager()
				->getRepository('MlUserBundle:User')
				->findOneByLogin($login);
				
		$services = NULL;
		$price = NULL;
		$creator = NULL;
		$creator_login = NULL;
		$basic = NULL;
		$sale = NULL;
		$carpooling = NULL;
		$couchsurfing = NULL;
		
		if ($req->getMethod() == 'POST') {
			$carpooling = false;
			$couchsurfing = false;
			$sale = false;
			$basic = false;
			
			$price = $req->request->get('price');
			$creator_login = $req->request->get('creator');
			$creator = NULL;

			
			if ($creator_login != NULL && $creator_login != "no_login") {
				$creator = $this->getDoctrine()
							   ->getManager()
							   ->getRepository('MlUserBundle:User')
							   ->findByLogin($creator_login);
			}
			
			if ($req->request->get('type') != null) {
				foreach ($req->request->get('type') as $key => $value) {
					if ($value == 'carpooling') {
						$carpooling = true;
					}
					else if ($value =='couchsurfing'){
						$couchsurfing = true;
					}
					else if ($value == 'sale') {
						$sale = true;
					}
					else if ($value == 'basic') {
						$basic = true;
					}

				}
				
				if ($basic == true) {
					if ($price == "desc") {
						if ($creator == NULL) {
							$basic = $this->getDoctrine()
										   ->getManager()
										   ->getRepository('MlServiceBundle:Basic')
										   ->findBy(array("visibility" => true), array("price" => 'desc'));
						}
						else {
							$basic = $this->getDoctrine()
									   ->getManager()
									   ->getRepository('MlServiceBundle:Basic')
									   ->findBy(array("visibility" => true, "user" => $creator), array("price" => 'desc'));
						}	
					}
					else if ($price == "asc") {
						if ($creator == NULL) {
							$basic = $this->getDoctrine()
										   ->getManager()
										   ->getRepository('MlServiceBundle:Basic')
										   ->findBy(array("visibility" => true), array("price" => 'asc'));
						}
						else {
							$basic = $this->getDoctrine()
									   ->getManager()
									   ->getRepository('MlServiceBundle:Basic')
									   ->findBy(array("visibility" => true, "user" => $creator), array("price" => 'asc'));
						}	
					}
					else {
						if ($creator == NULL) {
							$basic = $this->getDoctrine()
										   ->getManager()
										   ->getRepository('MlServiceBundle:Basic')
										   ->findByVisibility(true);
						}
						else {
							$basic = $this->getDoctrine()
									   ->getManager()
									   ->getRepository('MlServiceBundle:Basic')
									   ->findBy(array("visibility" => true, "user" => $creator));
						}	
					}
								   
					$services[] = $basic;
				}
				
				if ($carpooling == true) {
					if ($price == "desc") {
						if ($creator == NULL) {
							$carpoolings = $this->getDoctrine()
										   ->getManager()
										   ->getRepository('MlServiceBundle:Carpooling')
										   ->findBy(array("visibility" => true), array("price" => 'desc'));
						}
						else {
							$carpoolings = $this->getDoctrine()
									   ->getManager()
									   ->getRepository('MlServiceBundle:Carpooling')
									   ->findBy(array("visibility" => true, "user" => $creator), array("price" => 'desc'));
						}	
					}
					else if ($price == "asc") {
						if ($creator == NULL) {
							$carpoolings = $this->getDoctrine()
										   ->getManager()
										   ->getRepository('MlServiceBundle:Carpooling')
										   ->findBy(array("visibility" => true), array("price" => 'asc'));
						}
						else {
							$carpoolings = $this->getDoctrine()
									   ->getManager()
									   ->getRepository('MlServiceBundle:Carpooling')
									   ->findBy(array("visibility" => true, "user" => $creator), array("price" => 'asc'));
						}	
					}
					else {
						if ($creator == NULL) {
							$carpoolings = $this->getDoctrine()
										   ->getManager()
										   ->getRepository('MlServiceBundle:Carpooling')
										   ->findByVisibility(true);
						}
						else {
							$carpoolings = $this->getDoctrine()
									   ->getManager()
									   ->getRepository('MlServiceBundle:Carpooling')
									   ->findBy(array("visibility" => true, "user" => $creator));
						}	
					}
								   
					$services[] = $carpoolings;
				}
				if ($couchsurfing == true) {
					if ($price == "desc") {
						if ($creator == NULL) {
							$couchsurfings = $this->getDoctrine()
										 ->getManager()
										 ->getRepository('MlServiceBundle:CouchSurfing')
										 ->findBy(array("visibility" => true), array("price" => 'desc'));
						}
						else {
							$couchsurfings = $this->getDoctrine()
									   ->getManager()
									   ->getRepository('MlServiceBundle:CouchSurfing')
									   ->findBy(array("visibility" => true, "user" => $creator), array("price" => 'desc'));
						}	
					}
					else if ($price == "asc") {
						if ($creator == NULL) {
							$couchsurfings = $this->getDoctrine()
										 ->getManager()
										 ->getRepository('MlServiceBundle:CouchSurfing')
										 ->findBy(array("visibility" => true), array("price" => 'asc'));
						}
						else {
							$couchsurfings = $this->getDoctrine()
									   ->getManager()
									   ->getRepository('MlServiceBundle:CouchSurfing')
									   ->findBy(array("visibility" => true, "user" => $creator), array("price" => 'asc'));
						}	
					}
					else {
						if ($creator == NULL) {
							$couchsurfings = $this->getDoctrine()
										   ->getManager()
										   ->getRepository('MlServiceBundle:CouchSurfing')
										   ->findByVisibility(true);
						}
						else {
							$couchsurfings = $this->getDoctrine()
									   ->getManager()
									   ->getRepository('MlServiceBundle:CouchSurfing')
									   ->findBy(array("visibility" => true, "user" => $creator));
						}	
					}
									 
					$services[] = $couchsurfings;
				}
				if ($sale == true) {
					if ($price == "desc") {
						if ($creator == NULL) {
							$sales = $this->getDoctrine()
										 ->getManager()
										 ->getRepository('MlServiceBundle:Sale')
										 ->findBy(array("visibility" => true), array("price" => 'desc'));
						}
						else {
							$sales = $this->getDoctrine()
									   ->getManager()
									   ->getRepository('MlServiceBundle:Sale')
									   ->findBy(array("visibility" => true, "user" => $creator), array("price" => 'desc'));
						}
					}
					else if ($price == "asc") {
						if ($creator == NULL) {
							$sales = $this->getDoctrine()
										 ->getManager()
										 ->getRepository('MlServiceBundle:Sale')
										 ->findBy(array("visibility" => true), array("price" => 'asc'));
						}
						else {
							$sales = $this->getDoctrine()
									   ->getManager()
									   ->getRepository('MlServiceBundle:Sale')
									   ->findBy(array("visibility" => true, "user" => $creator), array("price" => 'asc'));
						}
					}
					else {
						if ($creator == NULL) {
							$sales = $this->getDoctrine()
										   ->getManager()
										   ->getRepository('MlServiceBundle:Sale')
										   ->findByVisibility(true);
						}
						else {
							$sales = $this->getDoctrine()
									   ->getManager()
									   ->getRepository('MlServiceBundle:Sale')
									   ->findBy(array("visibility" => true, "user" => $creator));
						}	
					}
									 
					$services[] = $sales;
				}
			}
			else if ($price != NULL) {
				if ($price == "desc") {
					/* Récupération de tous les Services du site par prix décroissants */
					if ($creator == NULL) {
						$basic = $this->getDoctrine()
								 ->getManager()
								 ->getRepository('MlServiceBundle:Basic')
								 ->findBy(array("visibility" => true), array("price" => 'desc'));
								 
						$carpoolings = $this->getDoctrine()
									   ->getManager()
									   ->getRepository('MlServiceBundle:Carpooling')
									   ->findBy(array("visibility" => true), array("price" => 'desc'));
									   
						$couchsurfings = $this->getDoctrine()
										 ->getManager()
										 ->getRepository('MlServiceBundle:CouchSurfing')
										 ->findBy(array("visibility" => true), array("price" => 'desc'));		

						$sales = $this->getDoctrine()
								 ->getManager()
								 ->getRepository('MlServiceBundle:Sale')
								 ->findBy(array("visibility" => true), array("price" => 'desc'));
					}
					else {
						$basic = $this->getDoctrine()
									   ->getManager()
									   ->getRepository('MlServiceBundle:Basic')
									   ->findBy(array("visibility" => true, "user" => $creator), array("price" => 'desc'));
									   
						$carpoolings = $this->getDoctrine()
									   ->getManager()
									   ->getRepository('MlServiceBundle:Carpooling')
									   ->findBy(array("visibility" => true, "user" => $creator), array("price" => 'desc'));
									   
						$couchsurfings = $this->getDoctrine()
										 ->getManager()
										 ->getRepository('MlServiceBundle:CouchSurfing')
										 ->findBy(array("visibility" => true, "user" => $creator), array("price" => 'desc'));		

						$sales = $this->getDoctrine()
								 ->getManager()
								 ->getRepository('MlServiceBundle:Sale')
								 ->findBy(array("visibility" => true, "user" => $creator), array("price" => 'desc'));
					}
				}
				else if ($price == "asc") {
					/* Récupération de tous les Services du site par prix croissants */
					if ($creator == NULL) {
						$basic = $this->getDoctrine()
									   ->getManager()
									   ->getRepository('MlServiceBundle:Basic')
									   ->findBy(array("visibility" => true), array("price" => 'asc'));
							
						$carpoolings = $this->getDoctrine()
									   ->getManager()
									   ->getRepository('MlServiceBundle:Carpooling')
									   ->findBy(array("visibility" => true), array("price" => 'asc'));
									   
						$couchsurfings = $this->getDoctrine()
										 ->getManager()
										 ->getRepository('MlServiceBundle:CouchSurfing')
										 ->findBy(array("visibility" => true), array("price" => 'asc'));		

						$sales = $this->getDoctrine()
								 ->getManager()
								 ->getRepository('MlServiceBundle:Sale')
								 ->findBy(array("visibility" => true), array("price" => 'asc'));
					}
					else {
						$basic = $this->getDoctrine()
									   ->getManager()
									   ->getRepository('MlServiceBundle:Basic')
									   ->findBy(array("visibility" => true, "user" => $creator), array("price" => 'asc'));
						
						$carpoolings = $this->getDoctrine()
									   ->getManager()
									   ->getRepository('MlServiceBundle:Carpooling')
									   ->findBy(array("visibility" => true, "user" => $creator), array("price" => 'asc'));
									   
						$couchsurfings = $this->getDoctrine()
										 ->getManager()
										 ->getRepository('MlServiceBundle:CouchSurfing')
										 ->findBy(array("visibility" => true, "user" => $creator), array("price" => 'asc'));		

						$sales = $this->getDoctrine()
								 ->getManager()
								 ->getRepository('MlServiceBundle:Sale')
								 ->findBy(array("visibility" => true, "user" => $creator), array("price" => 'asc'));
					}
				}
				else {
					/* Récupération de tous les Services sans tri sur le prix*/
					if ($creator == NULL) {
						$basic = $this->getDoctrine()
									   ->getManager()
									   ->getRepository('MlServiceBundle:Basic')
									   ->findByVisibility(true);
							
						$carpoolings = $this->getDoctrine()
									   ->getManager()
									   ->getRepository('MlServiceBundle:Carpooling')
									   ->findByVisibility(true);
									   
						$couchsurfings = $this->getDoctrine()
										 ->getManager()
										 ->getRepository('MlServiceBundle:CouchSurfing')
										 ->findByVisibility(true);		

						$sales = $this->getDoctrine()
								 ->getManager()
								 ->getRepository('MlServiceBundle:Sale')
								 ->findByVisibility(true);
					}
					else {
						$basic = $this->getDoctrine()
									   ->getManager()
									   ->getRepository('MlServiceBundle:Basic')
									   ->findBy(array("visibility" => true, "user" => $creator));
						
						$carpoolings = $this->getDoctrine()
									   ->getManager()
									   ->getRepository('MlServiceBundle:Carpooling')
									   ->findBy(array("visibility" => true, "user" => $creator));
									   
						$couchsurfings = $this->getDoctrine()
										 ->getManager()
										 ->getRepository('MlServiceBundle:CouchSurfing')
										 ->findBy(array("visibility" => true, "user" => $creator));		

						$sales = $this->getDoctrine()
								 ->getManager()
								 ->getRepository('MlServiceBundle:Sale')
								 ->findBy(array("visibility" => true, "user" => $creator));
					}
				}
				
				if ($basic != NULL) {
					$services[] = $basic;
				}
				if ($couchsurfings != NULL) {
					$services[] = $couchsurfings;
				}
				if ($carpoolings != NULL) {
					$services[] = $carpoolings;
				}
				if ($sales != NULL) {
					$services[] = $sales;
				}
			}
			else {
				/* Récupération de tous les Services du site */

				$basic = $this->getDoctrine()
							   ->getManager()
							   ->getRepository('MlServiceBundle:Basic')
							   ->findByVisibility(true);
				
				$carpoolings = $this->getDoctrine()
							   ->getManager()
							   ->getRepository('MlServiceBundle:Carpooling')
							   ->findByVisibility(true);
							   
				$couchsurfings = $this->getDoctrine()
								 ->getManager()
								 ->getRepository('MlServiceBundle:CouchSurfing')
								 ->findByVisibility(true);		

				$sales = $this->getDoctrine()
						 ->getManager()
						 ->getRepository('MlServiceBundle:Sale')
						 ->findByVisibility(true);						
				
				if ($basic != NULL) {
					$services[] = $basic;
				}				 
				if ($couchsurfings != NULL) {
					$services[] = $couchsurfings;
				}
				if ($carpoolings != NULL) {
					$services[] = $carpoolings;
				}
				if ($sales != NULL) {
					$services[] = $sales;
				}	
			}
		}
		else {
			/* Récupération de tous les Services du site */
			$basic = $this->getDoctrine()
						   ->getManager()
						   ->getRepository('MlServiceBundle:Basic')
						   ->findByVisibility(true);
			
			$carpoolings = $this->getDoctrine()
						   ->getManager()
						   ->getRepository('MlServiceBundle:Carpooling')
						   ->findByVisibility(true);
							   
			$couchsurfings = $this->getDoctrine()
							 ->getManager()
							 ->getRepository('MlServiceBundle:CouchSurfing')
							 ->findByVisibility(true);		

			$sales = $this->getDoctrine()
					 ->getManager()
					 ->getRepository('MlServiceBundle:Sale')
					 ->findByVisibility(true);						
			
			if ($basic != NULL) {
				$services[] = $basic;
			}			 
			if ($couchsurfings != NULL) {
				$services[] = $couchsurfings;
			}
			if ($carpoolings != NULL) {
				$services[] = $carpoolings;
			}
			if ($sales != NULL) {
				$services[] = $sales;
			}	
		}

		if ($services == NULL || $services[0] == NULL) {
			$services = NULL;
		}
		
		$users = $this->getDoctrine()
						 ->getManager()
						 ->getRepository('MlUserBundle:User')
						 ->findAll();	
		
		return $this->render('MlServiceBundle:Service:index.html.twig', array(
		  'servicess'=>$services,
		  'user' => $user,
		  'users' => $users,
		  'price' => $price,
		  'creator_login' => $creator_login,
		  'basic' => $basic,
		  'sale' => $sale,
		  'carpooling' => $carpooling,
		  'couchsurfing' => $couchsurfing));
	}
	
	/**
	 * Add a service in database (user can choose the kind of service he wants to create)
	 * @return Twig MlServiceBundle:Service:add_service.html.twig
	 */
	public function addServiceAction() {
		$req = $this->get('request');		
		try {		
		    $login = $this->container->get('ml.session')->sessionExist($req);
		}
		catch (\Exception $e) {
		    return $this->redirect($this->generateUrl('ml_user_add'));		    
		}
		
		$user = $this->getDoctrine()
				->getManager()
				->getRepository('MlUserBundle:User')
				->findOneByLogin($login);

		if($req->getMethod() == 'POST') {
			if ($req->request->get("type") == "basic") {
				return $this->redirect($this->generateUrl('ml_service_add_basic'));
			}
			else if ($req->request->get("type") == "carpooling") {
				return $this->redirect($this->generateUrl('ml_service_add_carpooling'));
			}
			else if ($req->request->get("type") == "couchsurfing") {
				return $this->redirect($this->generateUrl('ml_service_add_couchsurfing'));
			}
			else if ($req->request->get("type") == "sale") {
				return $this->redirect($this->generateUrl('ml_service_add_sale'));
			}			
		}
		
		return $this->render('MlServiceBundle:Service:add_service.html.twig', array(
		    'user' => $user));
	}

	/**
	 * Display a service and its data
	 * @param int $basic
	 * @return Twig MlServiceBundle:Service:see_basic.html.twig
	 */
	public function seeBasicAction($basic = null) {
		/* Test connexion */
		$req = $this->get('request');		
		try {		
		    $login = $this->container->get('ml.session')->sessionExist($req);
		}
		catch (\Exception $e) {
		    return $this->redirect($this->generateUrl('ml_user_add'));		    
		}
		
		$user = $this->getDoctrine()
				->getManager()
				->getRepository('MlUserBundle:User')
				->findOneByLogin($login);
				
		$em = $this->getDoctrine()->getManager();
		$data_basic = $em->getRepository('MlServiceBundle:Basic')->findOneById($basic);
		
		/* Si le Service demandé n'existe pas */
		if ($data_basic == null){
			return $this->redirect($this->generateUrl('ml_service_homepage'));
		}
		
		if ($data_basic->getVisibility() == false) {
			return $this->redirect($this->generateUrl('ml_service_homepage'));
		}
		
		if($req->getMethod() != 'POST'){			
			return $this->render('MlServiceBundle:Service:see_basic.html.twig', array('user' => $user,'basic' => $data_basic));
		}
		else {				
			if ($user == $data_basic->getUser()) {
				return $this->redirect($this->generateUrl('ml_service_homepage'));
			}
			
			if($this->container->get('ml.reservation')->canReserve($user,$data_basic,$em)) {
			    $basicUser = new BasicUser;
			
			    $basicUser->setApplicant($user);
			    $basicUser->setBasic($data_basic);
			
			    $em->persist($basicUser);
			    $em->flush();
			
			    $data_basic->setVisibility(false);
			
			    $em->persist($data_basic);
			    $em->flush();
            
			    return $this->redirect($this->generateUrl('ml_service_homepage'));
			}
			else {
			    return $this->render('MlServiceBundle:Service:see_basic.html.twig', array('user' => $user,'basic' => $data_basic,'error' => 'Désolé, vous n\'avez pas les moyens de réserver ce service.'));		
			}
		}
	}
	
	/**
	 * Add a basic service in database
	 * @return Twig MlServiceBundle:Service:add_basic.html.twig
	 */
	public function addBasicAction(){
		/* Test connexion */
		$req = $this->get('request');		
		try {		
		    $login = $this->container->get('ml.session')->sessionExist($req);
		}
		catch (\Exception $e) {
		    return $this->redirect($this->generateUrl('ml_user_add'));		    
		}
		
		$user = $this->getDoctrine()
				->getManager()
				->getRepository('MlUserBundle:User')
				->findOneByLogin($login);
	
		$basic = new Basic;
		
		$form = $this->createForm(new BasicType(),$basic);

		if($req->getMethod() == 'POST'){
			//lien requête<->form
			$form->bind($req);
		
			$em = $this->getDoctrine()->getManager();

			$basic->setUser($user);
			
			//var_dump($req->request->get("ml_servicebundle_basic")["associatedGroup"]);die;
			
			$assoc_initial = $req->request->get("ml_servicebundle_basic");
			$assoc = $assoc_initial["associatedGroup"];
			
			if ($assoc != NULL) {
				$group = $this->getDoctrine()
					->getManager()
					->getRepository('MlGroupBundle:Groupp')
					->findOneByName($assoc);
				
				$basic->setAssociatedGroup($group);
			}
			
			$em->persist($basic);
			$em->flush();

			//$this->get('session')->getFlashBag->add('ajouter', 'Votre service est ajoutée');
			
			$basic_id = $basic->getId();

			return $this->redirect($this->generateUrl('ml_service_see_basic', array('user'=>$user,'basic' => $basic_id)));
		}
		
		return $this->render('MlServiceBundle:Service:add_basic.html.twig', array(
			'form' => $form->createView(),
		    'user' => $user));
	}

	/**
	 * Delete a basic service from database
	 * @return Redirection to ml_service_homepage
	 */
	public function deleteBasicAction() {
		/* Test connexion */
		$req = $this->get('request');		
		try {		
		    $login = $this->container->get('ml.session')->sessionExist($req);
		}
		catch (\Exception $e) {
		    return $this->redirect($this->generateUrl('ml_user_add'));		    
		}
		
		$user = $this->getDoctrine()
				->getManager()
				->getRepository('MlUserBundle:User')
				->findOneByLogin($login);

		if ($user == NULL) {
			return $this->redirect($this->generateUrl('ml_user_add'));
		}
		
		$basic_id = $req->request->get("basic_id");
	
		$em = $this->getDoctrine()->getManager();
		
		$basic = $em->getRepository('MlServiceBundle:Basic')
			->findOneById($basic_id);

		$em->remove($basic);
		$em->flush();

		//$this->get('session')->getFlashBag->add('supprimer','Votre service a été supprimé');
		return $this->redirect($this->generateUrl('ml_service_homepage'));
	}
	
	/**
	 * Display a carpooling service and its data
	 * @param int $carpooling
	 * @return Twig MlServiceBundle:Service:see_carpooling.html.twig
	 */
	public function seeCarpoolingAction($carpooling = null) {
		/* Test connexion */
		$req = $this->get('request');		
		try {		
		    $login = $this->container->get('ml.session')->sessionExist($req);
		}
		catch (\Exception $e) {
		    return $this->redirect($this->generateUrl('ml_user_add'));		    
		}
		
		$user = $this->getDoctrine()
				->getManager()
				->getRepository('MlUserBundle:User')
				->findOneByLogin($login);
				
		$em = $this->getDoctrine()->getManager();
		$data_carpooling = $em->getRepository('MlServiceBundle:Carpooling')->findOneById($carpooling);
		
		/* Si le Service demandé n'existe pas */
		if ($data_carpooling == null){
			return $this->redirect($this->generateUrl('ml_service_homepage'));
		}
		
		if ($data_carpooling->getVisibility() == false) {
			return $this->redirect($this->generateUrl('ml_service_homepage'));
		}
		
		$current_date = date_create(date('Y-m-d'));
		$end_date = $data_carpooling->getDepartureDate();
		
		if (strtotime($end_date->format("d-m-y")) < strtotime($current_date->format("d-m-y"))) {
			$data_carpooling->setVisibility(false);
			$em->persist($data_carpooling);
			$em->flush();
			return $this->redirect($this->generateUrl('ml_service_homepage'));
		}
		
		if($req->getMethod() != 'POST'){			
			return $this->render('MlServiceBundle:Service:see_carpooling.html.twig', array('user' => $user,'carpool' => $data_carpooling));
		}
		else {				
			if ($user == $data_carpooling->getUser()) {
				return $this->redirect($this->generateUrl('ml_service_homepage'));
			}

			if($this->container->get('ml.reservation')->canReserve($user,$data_carpooling,$em)) {
			    $carpoolingUser = new CarpoolingUser;
			
			    $carpoolingUser->setApplicant($user);
			    $carpoolingUser->setCarpooling($data_carpooling);
			
			    $em->persist($carpoolingUser);
			    $em->flush();
			
			    $data_carpooling->setVisibility(false);
			
			    $em->persist($data_carpooling);
			    $em->flush();
            
			    return $this->redirect($this->generateUrl('ml_service_homepage'));
			}
			else {
			    return $this->render('MlServiceBundle:Service:see_carpooling.html.twig', array('user' => $user,'carpool' => $data_carpooling,'error' => 'Désolé, vous n\'avez pas les moyens de réserver ce service.'));			
			}
		}
	}
	
	/**
	 * Add a carpooling service in database
	 * @return Twig MlServiceBundle:Service:add_carpooling.html.twig
	 */
	public function addCarpoolingAction(){
		/* Test connexion */
		$req = $this->get('request');		
		try {		
		    $login = $this->container->get('ml.session')->sessionExist($req);
		}
		catch (\Exception $e) {
		    return $this->redirect($this->generateUrl('ml_user_add'));		    
		}
		
		$user = $this->getDoctrine()
				->getManager()
				->getRepository('MlUserBundle:User')
				->findOneByLogin($login);
	
		$carpooling = new Carpooling;
		
		$form = $this->createForm(new CarpoolingType(),$carpooling);


		if($req->getMethod() == 'POST'){
			//lien requête<->form
			$form->bind($req);
		
			$em = $this->getDoctrine()->getManager();

			$carpooling->setUser($user);
			
			//var_dump($req->request->get("ml_servicebundle_carpooling")["associatedGroup"]);die;
			
			$assoc_initial = $req->request->get("ml_servicebundle_carpooling");
			$assoc = $assoc_initial["associatedGroup"];
			
			if ($assoc != NULL) {
				$group = $this->getDoctrine()
					->getManager()
					->getRepository('MlGroupBundle:Groupp')
					->findOneByName($assoc);
				
				$carpooling->setAssociatedGroup($group);
			}
			
			$em->persist($carpooling);
			$em->flush();

			//$this->get('session')->getFlashBag->add('ajouter', 'Votre service est ajoutée');
			
			$carpooling_id = $carpooling->getId();

			return $this->redirect($this->generateUrl('ml_service_see_carpooling', array('user'=>$user,'carpooling' => $carpooling_id)));
		}
		
		return $this->render('MlServiceBundle:Service:add_carpooling.html.twig', array(
			'form' => $form->createView(),
		    'user' => $user));
	}

	/**
	 * Delete a carpooling service from database
	 * @return Redirection to ml_service_homepage
	 */
	public function deleteCarpoolingAction() {
		/* Test connexion */
		$req = $this->get('request');		
		try {		
		    $login = $this->container->get('ml.session')->sessionExist($req);
		}
		catch (\Exception $e) {
		    return $this->redirect($this->generateUrl('ml_user_add'));		    
		}
		
		$user = $this->getDoctrine()
				->getManager()
				->getRepository('MlUserBundle:User')
				->findOneByLogin($login);
		
		if ($user == NULL) {
			return $this->redirect($this->generateUrl('ml_user_add'));
		}
		
		$carpooling_id = $req->request->get("carpooling_id");
	
		$em = $this->getDoctrine()->getManager();
		
		$carpooling = $em->getRepository('MlServiceBundle:Carpooling')
			->findOneById($carpooling_id);

		$em->remove($carpooling);
		$em->flush();

		//$this->get('session')->getFlashBag->add('supprimer','Votre service a été supprimé');
		return $this->redirect($this->generateUrl('ml_service_homepage'));
	}

	/**
	 * Add a couchsurfing service in database
	 * @return Twig MlServiceBundle:Service:add_couchsurfing.html.twig
	 */
	public function addCouchSurfingAction(){
		/* Test connexion */
		$req = $this->get('request');		
		try {		
		    $login = $this->container->get('ml.session')->sessionExist($req);
		}
		catch (\Exception $e) {
		    return $this->redirect($this->generateUrl('ml_user_add'));		    
		}
		
		$user = $this->getDoctrine()
				->getManager()
				->getRepository('MlUserBundle:User')
				->findOneByLogin($login);
	
		$couchSurfing = new CouchSurfing;
		
		$form = $this->createForm(new CouchSurfingType(),$couchSurfing);


		if($req->getMethod() == 'POST'){
			//lien requête<->form
			$form->bind($req);
		
			$em = $this->getDoctrine()->getManager();

			$couchSurfing->setUser($user);
			
			$assoc_initial = $req->request->get("ml_servicebundle_couchsurfing");
			$assoc = $assoc_initial["associatedGroup"];
			
			if ($assoc != NULL) {
				$group = $this->getDoctrine()
					->getManager()
					->getRepository('MlGroupBundle:Groupp')
					->findOneByName($assoc);
				
				$couchSurfing->setAssociatedGroup($group);
			}
			
			$em->persist($couchSurfing);
			$em->flush();

			//$this->get('session')->getFlashBag->add('ajouter', 'Votre service est ajoutée');
			
			$couchSurfing_id = $couchSurfing->getId();

			return $this->redirect($this->generateUrl('ml_service_see_couchsurfing', array('user'=>$user,'couchsurfing' => $couchSurfing_id)));
		}
		
		return $this->render('MlServiceBundle:Service:add_couchsurfing.html.twig', array(
			'form' => $form->createView(),
		    'user' => $user));
	}

	/**
	 * Display a couchsurfing service and its data
	 * @param int $couchsurfing
	 * @return Twig MlServiceBundle:Service:see_couchsurfing.html.twig
	 */
	public function seeCouchSurfingAction($couchsurfing = null) {
		/* Test connexion */
		$req = $this->get('request');		
		try {		
		    $login = $this->container->get('ml.session')->sessionExist($req);
		}
		catch (\Exception $e) {
		    return $this->redirect($this->generateUrl('ml_user_add'));		    
		}
		
		$user = $this->getDoctrine()
				->getManager()
				->getRepository('MlUserBundle:User')
				->findOneByLogin($login);
				
		$em = $this->getDoctrine()->getManager();
		$data_couchsurfing = $em->getRepository('MlServiceBundle:CouchSurfing')->findOneById($couchsurfing);
		
		/* Si le Service demandé n'existe pas */
		if ($data_couchsurfing == null){
			return $this->redirect($this->generateUrl('ml_service_homepage'));
		}
		
		if ($data_couchsurfing->getVisibility() == false) {
			return $this->redirect($this->generateUrl('ml_service_homepage'));
		}
		
		$current_date = date_create(date('Y-m-d'));
		$end_date = $data_couchsurfing->getDateStart();
		
		if (strtotime($end_date->format("d-m-y")) < strtotime($current_date->format("d-m-y"))) {
			$data_couchsurfing->setVisibility(false);
			$em->persist($data_couchsurfing);
			$em->flush();
			return $this->redirect($this->generateUrl('ml_service_homepage'));
		}
		
		if($req->getMethod() != 'POST'){			
			return $this->render('MlServiceBundle:Service:see_couchsurfing.html.twig', array('user' => $user,'couchsurfing' => $data_couchsurfing));
		}
		else {				
			if ($user == $data_couchsurfing->getUser()) {
				return $this->redirect($this->generateUrl('ml_service_homepage'));
			}

			if($this->container->get('ml.reservation')->canReserve($user,$data_couchsurfing,$em)) {
			    $couchSurfingUser = new CouchSurfingUser;
			
			    $couchSurfingUser->setApplicant($user);
			    $couchSurfingUser->setCouchsurfing($data_couchsurfing);
			
			    $em->persist($couchSurfingUser);
			    $em->flush();
			
			    $data_couchsurfing->setVisibility(false);
			
			    $em->persist($data_couchsurfing);
			    $em->flush();
            
			    return $this->redirect($this->generateUrl('ml_service_homepage'));
			}
			else {
			    return $this->render('MlServiceBundle:Service:see_couchsurfing.html.twig', array('user' => $user,'couchsurfing' => $data_couchsurfing,'error' => 'Désolé, vous n\'avez pas les moyens de réserver ce service.'));			
			}
		}
	}
	
	/**
	 * Delete a couchsurfing service from database
	 * @return Redirection ml_service_homepage
	 */
	public function deleteCouchsurfingAction() {
		/* Test connexion */
		$req = $this->get('request');		
		try {		
		    $login = $this->container->get('ml.session')->sessionExist($req);
		}
		catch (\Exception $e) {
		    return $this->redirect($this->generateUrl('ml_user_add'));		    
		}
		
		$user = $this->getDoctrine()
				->getManager()
				->getRepository('MlUserBundle:User')
				->findOneByLogin($login);
		
		if ($user == NULL) {
			return $this->redirect($this->generateUrl('ml_user_add'));
		}

		$couchsurfing_id = $req->request->get("couchsurfing_id");
	
		$em = $this->getDoctrine()->getManager();
		
		$couchsurfing = $em->getRepository('MlServiceBundle:Couchsurfing')
			->findOneById($couchsurfing_id);
		
		$em->remove($couchsurfing);
		$em->flush();

		//$this->get('session')->getFlashBag->add('supprimer','Votre service a été supprimé');
		return $this->redirect($this->generateUrl('ml_service_homepage'));
	}

	/**
	 * Add a sale service in database
	 * @return Twig MlServiceBundle:Service:add_sale.html.twig
	 */
	public function addSaleAction(){
		/* Test connexion */
		$req = $this->get('request');		
		try {		
		    $login = $this->container->get('ml.session')->sessionExist($req);
		}
		catch (\Exception $e) {
		    return $this->redirect($this->generateUrl('ml_user_add'));		    
		}
		
		$user = $this->getDoctrine()
				->getManager()
				->getRepository('MlUserBundle:User')
				->findOneByLogin($login);
	
		$sale = new Sale;
		
		$form = $this->createForm(new SaleType(),$sale);


		if($req->getMethod() == 'POST'){
			//lien requête<->form
			$form->bind($req);
		
			$em = $this->getDoctrine()->getManager();

			$sale->setUser($user);
			
			$sale->upload();
			
			$assoc_initial = $req->request->get("ml_servicebundle_sale");
			$assoc = $assoc_initial["associatedGroup"];
			
			if ($assoc != NULL) {
				$group = $this->getDoctrine()
					->getManager()
					->getRepository('MlGroupBundle:Groupp')
					->findOneByName($assoc);
				
				$sale->setAssociatedGroup($group);
			}
			
			$em->persist($sale);
			$em->flush();

			//$this->get('session')->getFlashBag->add('ajouter', 'Votre service est ajoutée');
			
			$sale_id = $sale->getId();

			return $this->redirect($this->generateUrl('ml_service_see_sale', array('user'=>$user,'sale' => $sale_id)));
		}
		
		return $this->render('MlServiceBundle:Service:add_sale.html.twig', array(
			'form' => $form->createView(),
		    'user' => $user));
	}

	/**
	 * Display a sale service and its data
	 * @param int $sale
	 * @return Twig MlServiceBundle:Service:see_sale.html.twig
	 */
	public function seeSaleAction($sale = null) {
		/* Test connexion */
		$req = $this->get('request');		
		try {		
		    $login = $this->container->get('ml.session')->sessionExist($req);
		}
		catch (\Exception $e) {
		    return $this->redirect($this->generateUrl('ml_user_add'));		    
		}
		
		$user = $this->getDoctrine()
				->getManager()
				->getRepository('MlUserBundle:User')
				->findOneByLogin($login);
				
		$em = $this->getDoctrine()->getManager();
		$data_sale = $em->getRepository('MlServiceBundle:Sale')->findOneById($sale);
		
		/* Si le Service demandé n'existe pas */
		if ($data_sale == null){
			return $this->redirect($this->generateUrl('ml_service_homepage'));
		}
		
		if ($data_sale->getVisibility() == false) {
			return $this->redirect($this->generateUrl('ml_service_homepage'));
		}
		
		if($req->getMethod() != 'POST'){			
			return $this->render('MlServiceBundle:Service:see_sale.html.twig', array('user' => $user,'sale' => $data_sale));
		}
		else {				
			if ($user == $data_sale->getUser()) {
				return $this->redirect($this->generateUrl('ml_service_homepage'));
			}
			
			if($this->container->get('ml.reservation')->canReserve($user,$data_sale,$em)) {
			    $saleUser = new SaleUser;
			
			    $saleUser->setApplicant($user);
			    $saleUser->setSale($data_sale);
			
			    $em->persist($saleUser);
			    $em->flush();
			
			    $data_sale->setVisibility(false);
			
			    $em->persist($data_sale);
			    $em->flush();
            
			    return $this->redirect($this->generateUrl('ml_service_homepage'));
			}
			else {
			    return $this->render('MlServiceBundle:Service:see_sale.html.twig', array('user' => $user,'sale' => $data_sale,'error' => 'Désolé, vous n\'avez pas les moyens de réserver ce service.'));			
			}
		}
	}
	
	/**
	 * Delete a sale service from database
	 * @return Redirection ml_service_homepage
	 */
	public function deleteSaleAction() {
		/* Test connexion */
		$req = $this->get('request');		
		try {		
		    $login = $this->container->get('ml.session')->sessionExist($req);
		}
		catch (\Exception $e) {
		    return $this->redirect($this->generateUrl('ml_user_add'));		    
		}
		
		$user = $this->getDoctrine()
				->getManager()
				->getRepository('MlUserBundle:User')
				->findOneByLogin($login);

		if ($user == NULL) {
			return $this->redirect($this->generateUrl('ml_user_add'));
		}
		
		$sale_id = $req->request->get("sale_id");
	
		$em = $this->getDoctrine()->getManager();
		
		$sale = $em->getRepository('MlServiceBundle:Sale')
			->findOneById($sale_id);

		$em->remove($sale);
		$em->flush();

		//$this->get('session')->getFlashBag->add('supprimer','Votre service a été supprimé');
		return $this->redirect($this->generateUrl('ml_service_homepage'));
	}

	/**
	 * Display all services of current user
	 * @return Twig MlServiceBundle:Service:index_my_services.html.twig
	 */
    public function seeMyServicesAction() {
        $req = $this->get('request');	
        	
		try {		
		    $login = $this->container->get('ml.session')->sessionExist($req);
		}
		catch (\Exception $e) {
		    return $this->redirect($this->generateUrl('ml_user_add'));		    
		}
				
		$user = $this->getDoctrine()
				->getManager()
				->getRepository('MlUserBundle:User')
				->findOneByLogin($login);
		
		$basic = $this->getDoctrine()
						   ->getManager()
						   ->getRepository('MlServiceBundle:Basic')
						   ->findBy(array('user'=>$user,'visibility'=>true));
		
		$carpoolings = $this->getDoctrine()
						   ->getManager()
						   ->getRepository('MlServiceBundle:Carpooling')
						   ->findBy(array('user'=>$user,'visibility'=>true));
							   
	    $couchsurfings = $this->getDoctrine()
							 ->getManager()
							 ->getRepository('MlServiceBundle:CouchSurfing')
							 ->findBy(array('user'=>$user,'visibility'=>true));		

		$sales = $this->getDoctrine()
					 ->getManager()
					 ->getRepository('MlServiceBundle:Sale')
					 ->findBy(array('user'=>$user,'visibility'=>true));
							 
							 
		$services = array_merge($basic,$couchsurfings,$carpoolings,$sales);
		
		$basicReserved = $this->getDoctrine()
						   ->getManager()
						   ->getRepository('MlServiceBundle:BasicUser')
						   ->findByOwner($user);
		
		$carpoolingsReserved = $this->getDoctrine()
						   ->getManager()
						   ->getRepository('MlServiceBundle:CarpoolingUser')
						   ->findByOwner($user);
							   
	    $couchsurfingsReserved = $this->getDoctrine()
							 ->getManager()
							 ->getRepository('MlServiceBundle:CouchSurfingUser')
						     ->findByOwner($user);	

		$salesReserved = $this->getDoctrine()
					 ->getManager()
					 ->getRepository('MlServiceBundle:SaleUser')
				     ->findByOwner($user);
			
		if ($services == NULL) {
			$services = NULL;
		}
		
		if ($salesReserved == NULL) {
			$salesReserved = NULL;
		}
		
		if ($couchsurfingsReserved == NULL) {
			$couchsurfingsReserved = NULL;
		}
		
		if ($carpoolingsReserved == NULL) {
			$carpoolingsReserved = NULL;
		}
		
		if ($basicReserved == NULL) {
			$basicReserved = NULL;
		}
		
		return $this->render('MlServiceBundle:Service:index_my_services.html.twig', array('user' => $user,'services' => $services,'basicReserved' => $basicReserved,'salesReserved' => $salesReserved,'couchsurfingsReserved' => $couchsurfingsReserved,'carpoolingsReserved' => $carpoolingsReserved));
    }
    
	/**
	 * Set a service as done
	 * @return Redirection ml_service_see_mine
	 */
    public function serviceDoneAction() {
         $req = $this->get('request');	
        	
		try {		
		    $login = $this->container->get('ml.session')->sessionExist($req);
		}
		catch (\Exception $e) {
		    return $this->redirect($this->generateUrl('ml_user_add'));		    
		}
		
		if($req->getMethod() != 'POST') {
		    return $this->redirect($this->generateUrl('ml_service_see_mine'));
		}
		else {
		    switch($req->request->get('type')) {
		        case 'basic':
		            $eval = new BasicEval();
		            $service = $this->getDoctrine()
                            ->getRepository('MlServiceBundle:Basic')
                            ->findOneById($req->request->get('id'));
                    $reservation = $this->getDoctrine()
                            ->getRepository('MlServiceBundle:BasicUser')
                            ->findOneById($req->request->get('reservation-id'));
		            break;
		        case 'sale':
		            $eval = new SaleEval();
		            $service = $this->getDoctrine()
                            ->getRepository('MlServiceBundle:Sale')
                            ->findOneById($req->request->get('id'));
                    $reservation = $this->getDoctrine()
                            ->getRepository('MlServiceBundle:SaleUser')
                            ->findOneById($req->request->get('reservation-id'));
		            break;
		        case 'couchsurfing':
		            $eval = new CouchsurfingEval();
		            $service = $this->getDoctrine()
                            ->getRepository('MlServiceBundle:Couchsurfing')
                            ->findOneById($req->request->get('id'));
                    $reservation = $this->getDoctrine()
                            ->getRepository('MlServiceBundle:CouchsurfingUser')
                            ->findOneById($req->request->get('reservation-id'));
		            break;
		        case 'carpooling':
		            $eval = new CarpoolingEval();
		            $service = $this->getDoctrine()
                            ->getRepository('MlServiceBundle:Carpooling')
                            ->findOneById($req->request->get('id'));
                    $reservation = $this->getDoctrine()
                            ->getRepository('MlServiceBundle:CarpoolingUser')
                            ->findOneById($req->request->get('reservation-id'));
		            break;
		        default:
		            $eval = null;
		            $service = null;
		            $reservation = null;
		            break;
		    }
				
		    if(($eval == null) || ($service == null) || ($reservation == null)) {
		        return $this->redirect($this->generateUrl('ml_service_see_mine'));
		    }

		    $eval->setService($service);
		    $eval->setPayed(false);
		    $eval->setSubscriber($reservation->getApplicant());
		    $eval->setOwner($service->getUser());
		    $eval->setEval(0);
		    
		    $service->setVisibility(true);
		    
		    $this->getDoctrine()->getManager()->remove($reservation);
		    $this->getDoctrine()->getManager()->persist($eval);
		    $this->getDoctrine()->getManager()->persist($service);
			$this->getDoctrine()->getManager()->flush();
		}
		
        return $this->redirect($this->generateUrl('ml_service_see_mine'));
    }
}


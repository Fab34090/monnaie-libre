<?php
namespace Ml\GroupBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Ml\GroupBundle\Entity\Groupp;
use Ml\GroupBundle\Entity\GroupUser;

/**
 * Group Controller extending Controller
 * This one is used to display groups and all its data and to manage groups for groups' creators or to join a group
 */
class GroupController extends Controller {	
	
	/**
	 * Display all groups with data
	 * @return Twig template MlGroupBundle:Group:groups.html.twig
	 */
	public function indexAction() {
		$request = $this->get('request');
		
        try {		
		    $login = $this->container->get('ml.session')->sessionExist($request);
		}
		catch (\Exception $e) {
		    return $this->redirect($this->generateUrl('ml_user_add'));		    
		}
		
		if ($login == NULL) {
			return $this->redirect($this->generateUrl('ml_user_add'));
		}
		
		$current_user = $this->getDoctrine()
			->getRepository('MlUserBundle:User')
			->findOneByLogin($login);
		
		$groups = $this->getDoctrine()
			->getRepository('MlGroupBundle:Groupp')
			->findAll();
		
		$message = NULL;
		
		if ($groups == NULL) {
			$groups = NULL;
		}
		
		if ($groups == NULL) {
			$message = "Pas de groupe enregistré à ce jour";
		}
		
		return $this->render('MlGroupBundle:Group:groups.html.twig', array(
			'user' => $current_user,
			'groups' => $groups,
			'message' => $message));
	}
	
	/**
	 * Allow a user to create a group
	 * @return Redirection to ml_group_display_group
	 */
	public function creationGroupAction() {
		$req = $this->get('request');
		
        try {		
		    $login = $this->container->get('ml.session')->sessionExist($req);
		}
		catch (\Exception $e) {
		    return $this->redirect($this->generateUrl('ml_user_add'));		    
		}
		
		if ($login == NULL) {
			return $this->redirect($this->generateUrl('ml_user_add'));
		}
		
		$current_user = $this->getDoctrine()
				->getRepository('MlUserBundle:User')
				->findOneByLogin($login);
		
		$group = new Groupp;
		
		// Get all users from database in order that the group's creator can add members to the group
		$users = $this->getDoctrine()
				->getRepository('MlUserBundle:User')
				->findAll();
		
		if ($users != NULL) {
			for ($i = 0; $i<sizeof($users); $i++) {
				// Add all users except current user
				if ($users[$i]->getLogin() != $login) {
					$users_login[$users[$i]->getLogin()] = $users[$i]->getLogin();
				}
			}
			
			// If there are users in database we display them
			if(isset($users_login)) {
				$form = $this->createFormBuilder($group)
							 ->add('name', 'text', array(
														'label' => 'Nom'))
							 ->add('description', 'text')
							 ->add('users', 'choice', array(
														'choices' => $users_login,
														'multiple' => true,
														'required' => false,
														'mapped' => false,
														'label' => 'Utilisateurs'))
							 ->getForm();
							 
				$name_initial = $req->request->get("form");
				$name = $name_initial['name'];
							 
				$group_already_exist = $this->getDoctrine()
					->getRepository('MlGroupBundle:Groupp')
					->findOneByName($name);
				
				if($group_already_exist != NULL) {
						return $this->render('MlGroupBundle:Group:creation_group.html.twig', array(
						  'form' => $form->createView(),
						  'user' => $current_user,
						  'error' => "Un groupe avec le même nom existe déjà, veuillez en choisir un autre s'il vous plait."));
				}

				if ($req->getMethod() == 'POST') {	
				    // Link Request <-> Form
				    $form->bind($req);
			  
					$group->setAdministrator($current_user);

					// Save object $group in database
					$em = $this->getDoctrine()->getManager();
					$em->persist($group);
					$em->flush();
					
					// If creator add members
					$users_initial = $req->request->get("form");
					if(isset($users_initial['users'])) {
						$users = $users_initial['users'];
					}
					else {
						$users = NULL;
					}
					
					
					if($users != NULL) {		
						$group_id = $this->getDoctrine()
							->getRepository('MlGroupBundle:Groupp')
							->findOneByName($name);
							
						// Add members to the group
						for($i = 0; $i < sizeof($users); $i++) {
							  $groupUser[$i] = new GroupUser;
							  
							  $user_id = $this->getDoctrine()
								->getRepository('MlUserBundle:User')
								->findOneByLogin($users[$i]);

							  // Link to the group which is here always the same
							  $groupUser[$i]->setGroupp($group_id);
							  // Link to the user which change at each loop
							  $groupUser[$i]->setUser($user_id);
							  // By default users are accepted
							  $groupUser[$i]->setAccepted(true);

							  $em->persist($groupUser[$i]);
							}
					
							$em->flush();
						}

					return $this->redirect($this->generateUrl('ml_group_display_group', array('group_id' => $group->getId())));
				}
			}
			// No users in database
			else {
				$form = $this->createFormBuilder($group)
						 ->add('name', 'text')
						 ->add('description', 'text')
						 ->getForm();

				if ($req->getMethod() == 'POST') {	
					$form->bind($req);
					
					$name_initial = $req->request->get("form");
					$name = $name_initial['name'];
					
					$group_already_exist = $this->getDoctrine()
					->getRepository('MlGroupBundle:Groupp')
					->findOneByName($name);
					
					if($group_already_exist != NULL) {
							return $this->render('MlGroupBundle:Group:creation_group.html.twig', array(
							  'form' => $form->createView(),
							  'user' => $current_user,
							  'error' => "Un groupe avec le même nom existe déjà, veuillez en choisir un autre s'il vous plait."));
					}
			  
					$group->setAdministrator($current_user);

					$em = $this->getDoctrine()->getManager();
					$em->persist($group);
					$em->flush();

					return $this->redirect($this->generateUrl('ml_group_display_group', array('group_id' => $group->getId())));

				}
			}	
		}
		else {
			$form = $this->createFormBuilder($group)
					 ->add('name', 'text')
					 ->add('description', 'text')
					 ->getForm();

			if ($req->getMethod() == 'POST') {
				$form->bind($req);
				
				$name_initial = $req->request->get("form");
				$name = $name_initial['name'];
				
				$group_already_exist = $this->getDoctrine()
					->getRepository('MlGroupBundle:Groupp')
					->findOneByName($name);
				
				if($group_already_exist != NULL) {
						return $this->render('MlGroupBundle:Group:creation_group.html.twig', array(
						  'form' => $form->createView(),
						  'user' => $current_user,
						  'error' => "Un groupe avec le même nom existe déjà, veuillez en choisir un autre s'il vous plait."));
				}
		  
				$group->setAdministrator($current_user);

				$em = $this->getDoctrine()->getManager();
				$em->persist($group);
				$em->flush();

				return $this->redirect($this->generateUrl('ml_home_homepage'));

			}
		}

		return $this->render('MlGroupBundle:Group:creation_group.html.twig', array(
		  'form' => $form->createView(),
		  'user' => $current_user));
	}
	
	/**
	 * Display a group with all its data
	 * @return Twig template MlGroupBundle:Group:display_group.html.twig
	 */
	public function displayGroupAction($group_id = null) {
		$request = $this->get('request');
		
        try {		
		    $login = $this->container->get('ml.session')->sessionExist($request);
		}
		catch (\Exception $e) {
		    return $this->redirect($this->generateUrl('ml_user_add'));		    
		}
		
		if ($login == NULL) {
			return $this->redirect($this->generateUrl('ml_user_add'));
		}
		
		$current_user = $this->getDoctrine()
			->getRepository('MlUserBundle:User')
			->findOneByLogin($login);
		
		$group_users = NULL;
		
		$group_data = $this->getDoctrine()
			->getRepository('MlGroupBundle:Groupp')
			->findOneById($group_id);
			
		if ($group_data != NULL) {
			$is_member = false;
			$is_admin = false;
			$services = NULL;
		
			$req_groups_users = $this->getDoctrine()
				->getRepository('MlGroupBundle:GroupUser')
				->findByGroupp($group_data);
			
			foreach ($req_groups_users as $key => $value) {
				$group_users[] = $value;
			}
			
			$administrator_group_data = $this->getDoctrine()
					->getRepository('MlUserBundle:User')
					->findOneByLogin($group_data->getAdministrator()->getLogin());
					
			if (isset($administrator_group_data)) {
				if ($administrator_group_data == $current_user) {
					$is_admin = true;
				}
			}
			
			if (isset($group_users)) {
				foreach ($group_users as $key => $value) {
					if ($value->getUser()->getLogin() == $login) {
						if ($value->getAccepted() == true) {
							$is_member = true;
						}
					}
				}
			}
			
			$basic = $this->getDoctrine()
				->getRepository('MlServiceBundle:Basic')
				->findByAssociatedGroup($group_data);
			
			if ($basic != NULL) {
				foreach($basic as $key => $value) {
					$services[] = $value;
				}
			}
			
			$sales = $this->getDoctrine()
				->getRepository('MlServiceBundle:Sale')
				->findByAssociatedGroup($group_data);
			
			if ($sales != NULL) {
				foreach($sales as $key => $value) {
					$services[] = $value;
				}
			}
			
			$couchsurfings = $this->getDoctrine()
				->getRepository('MlServiceBundle:CouchSurfing')
				->findBy(array("associatedGroup" => $group_data, "visibility" => true));
			
			if ($couchsurfings != NULL) {
				foreach($couchsurfings as $key => $value) {
					$services[] = $value;
				}
			}
			
			$carpoolings = $this->getDoctrine()
				->getRepository('MlServiceBundle:Carpooling')
				->findBy(array("associatedGroup" => $group_data, "visibility" => true));
			
			if ($carpoolings != NULL) {
				foreach($carpoolings as $key => $value) {
					$services[] = $value;
				}
			}
			
			$all_users = $this->getDoctrine()
				->getRepository('MlUserBundle:User')
				->findAll();
			
			$group_users = $this->getDoctrine()
				->getRepository('MlGroupBundle:GroupUser')
				->findBy(array('groupp' => $group_data, 'accepted' => true));
				
			$users = NULL;
			$users_final = NULL;
		
			if ($all_users != NULL) {
				$cpt = sizeof($group_users);
			
				foreach ($all_users as $key => $value) {
					$is_member_of_group = false;
					
					for ($i = ($cpt-1); $i >= 0; $i--) {
						if ($group_users[$i]->getUser() === $value) {
							$is_member_of_group = true;
						}
					}
					
					if ($is_member_of_group == false) {
						$users[] = $value;
					}
				}
			}
			
			foreach ($users as $key => $value) {
				if ($value !== $administrator_group_data) {
					$users_final[] = $value;
				}
			}
			
			$requests = $this->getDoctrine()
				->getRepository('MlGroupBundle:GroupUser')
				->findBy(array('groupp' => $group_data, 'accepted' => false));
			
			if ($requests == NULL) {
				$requests = NULL;
			}
			
			$is_valid = false;
			$is_waiting = false;
			
			if (isset($requests)) {
				foreach ($requests as $key => $value) {
					if ($value->getUser()->getLogin() == $login) {
						$is_waiting = true;
						$is_valid = true;
					}
				}
			}
			
			if ($is_admin == true || $is_member == true) {
				$is_valid = true;
			}
			
			if (!isset($group_users)) {
				$group_users = NULL;
			}
		}
			
		if ($group_data == NULL) {
				return $this->render('MlGroupBundle:Group:display_group.html.twig', array(
						'user' => $current_user,
						'message' => $group_id, 
						'group_id' => $group_id));		
		} 
		else {
			return $this->render('MlGroupBundle:Group:display_group.html.twig', array(
						'user' => $current_user,
						'members' => $group_users,
						'group' => $group_data, 
						'group_id' => $group_id,
						'administrator_group_data' => $administrator_group_data,
						'is_member' => $is_member,
						'is_administrator' => $is_admin,
						'is_valid' => $is_valid,
						'associated_services' => $services,
						'is_waiting' => $is_waiting,
						'requests' => $requests,
						'users' => $users_final));	
		}
	}
	
	/**
	 * Add a user to the group
	 * @return Redirection to ml_group_display_group
	 */
	public function addUserAction() {
		$request = $this->get('request');
		
		try {		
		    $login = $this->container->get('ml.session')->sessionExist($request);
		}
		catch (\Exception $e) {
		    return $this->redirect($this->generateUrl('ml_user_add'));		    
		}
		
		$group_id = $request->request->get("group_id");
		
		if ($group_id == NULL) {
			$this->redirect($this->generateUrl('ml_home_homepage'));
		}
		
		$group = $this->getDoctrine()
			->getRepository('MlGroupBundle:Groupp')
			->findOneById($group_id);
		
		if ($group == NULL) {
			return $this->redirect($this->generateUrl('ml_home_homepage'));
		}
			
		$user = $this->getDoctrine()
			->getRepository('MlUserBundle:User')
			->findOneById($request->request->get("user_id"));
		
		$group_user = $this->getDoctrine()
			->getRepository('MlGroupBundle:GroupUser')
			->findOneBy(array('user' => $user, 'groupp' => $group));
			
		if ($group_user == NULL) {
			$group_user = NULL;
		}
		
		if ($group_user != NULL) {
			$group_user->setAccepted(true);
			
			$em = $this->getDoctrine()->getManager();
			$em->persist($group_user);
			$em->flush();
		}
		else {
			$group_user = new GroupUser();
			$group_user->setUser($user);
			$group_user->setGroupp($group);
			$group_user->setAccepted(true);
			
			$em = $this->getDoctrine()->getManager();
			$em->persist($group_user);
			$em->flush();
		}
		
		$group_id = (int)$group_id;
		
		return $this->redirect($this->generateUrl('ml_group_display_group', array('group_id' => $group_id)));
	}
	
	/**
	 * Send a request to join the group
	 * @return Redirection to ml_group_display_group
	 */
	public function joinGroupAction($group_id = NULL) {
		$request = $this->get('request');
		
		if ($group_id == NULL) {
			$this->redirect($this->generateUrl('ml_home_homepage'));
		}
		
		try {		
		    $login = $this->container->get('ml.session')->sessionExist($request);
		}
		catch (\Exception $e) {
		    return $this->redirect($this->generateUrl('ml_user_add'));		    
		}
		
		$user = $this->getDoctrine()
				->getRepository('MlUserBundle:User')
				->findOneByLogin($login);
		
		$group_data = $this->getDoctrine()
			->getRepository('MlGroupBundle:Groupp')
			->findOneById($group_id);	
			
		if (isset($group_data)) {				
			if ($group_data->getAdministrator() == $user) {
				return $this->redirect($this->generateUrl('ml_home_homepage'));
			}
			
			$group_current_user_current = $this->getDoctrine()
				->getRepository('MlGroupBundle:GroupUser')
				->findOneBy(array('groupp' => $group_data, 'user' => $user));
				
			if ($group_current_user_current != NULL) {
				return $this->redirect($this->generateUrl('ml_home_homepage'));
			}
			
			$request_group_user = new GroupUser;
			
			$request_group_user->setGroupp($group_data);
			$request_group_user->setUser($user);
			$request_group_user->setAccepted(false);
			$request_group_user->setMessage($request->request->get("message_join_group"));
			
			$em = $this->getDoctrine()->getManager();
			$em->persist($request_group_user);
			$em->flush();
			
			return $this->redirect($this->generateUrl('ml_group_display_group', array('group_id' => $group_id)));
		}
		else {
			return $this->redirect($this->generateUrl('ml_home_homepage'));
		}
	}
	
	/**
	 * Remove a user from the group
	 * @return Redirection to ml_group_display_group
	 */
	public function deleteUserAction() {
		$request = $this->get('request');
		
		try {		
		    $login = $this->container->get('ml.session')->sessionExist($request);
		}
		catch (\Exception $e) {
		    return $this->redirect($this->generateUrl('ml_user_add'));		    
		}
		
		$group_id = $request->request->get("group_id");
		
		if ($group_id == NULL) {
			$this->redirect($this->generateUrl('ml_home_homepage'));
		}
		
		$user_id = (int)$request->request->get("member_id");
		
		if ($user_id == NULL) {
			return $this->redirect($this->generateUrl('ml_home_homepage'));
		}
		
		$group = $this->getDoctrine()
			->getRepository('MlGroupBundle:Groupp')
			->findOneById($group_id);
			
		if ($group == NULL) {
			return $this->redirect($this->generateUrl('ml_home_homepage'));
		}	
			
		$user = $this->getDoctrine()
			->getRepository('MlUserBundle:User')
			->findOneById($user_id);
		
		if ($user == NULL) {
			return $this->redirect($this->generateUrl('ml_home_homepage'));
		}
			
		$em = $this->getDoctrine()->getManager();
		
		// Remove from the group
		$group_user = $this->getDoctrine()
			->getRepository('MlGroupBundle:GroupUser')
			->findBy(array("groupp" => $group, "user" => $user));
		
		if ($group_user != NULL) {
			foreach ($group_user as $key => $value) {
				$em->remove($value);
				$em->flush();
			}
		}
		
		// Set NULL associatedGroup to him created services
		$associated_services = NULL;
		
		$associated_basic = $this->getDoctrine()
			->getRepository('MlServiceBundle:Basic')
			->findBy(array("associatedGroup" => $group, "user" => $user));
			
		if ($associated_basic != NULL) {
			foreach ($associated_basic as $key => $value) {
				$associated_services[] = $value;
			}
		}
		
		$associated_carpoolings = $this->getDoctrine()
			->getRepository('MlServiceBundle:Carpooling')
			->findBy(array("associatedGroup" => $group, "user" => $user));
			
		if ($associated_carpoolings != NULL) {
			foreach ($associated_carpoolings as $key => $value) {
				$associated_services[] = $value;
			}
		}
		
		$associated_couchsurfings = $this->getDoctrine()
			->getRepository('MlServiceBundle:CouchSurfing')
			->findBy(array("associatedGroup" => $group, "user" => $user));
			
		if ($associated_couchsurfings != NULL) {
			foreach ($associated_couchsurfings as $key => $value) {
				$associated_services[] = $value;
			}
		}
		
		$associated_sales = $this->getDoctrine()
			->getRepository('MlServiceBundle:Sale')
			->findBy(array("associatedGroup" => $group, "user" => $user));
			
		if ($associated_sales != NULL) {
			foreach ($associated_sales as $key => $value) {
				$associated_services[] = $value;
			}
		}
		
		if ($associated_services != NULL) {
			foreach ($associated_services as $key => $value) {
				$value->setAssociatedGroup(NULL);
				
				$em->persist($value);
				$em->flush();
			}
		}
		
		return $this->redirect($this->generateUrl('ml_group_display_group', array('group_id' => $group_id)));
	}
	
	/**
	 * Refuse a request to join the group
	 * @return Redirection to ml_group_display_group
	 */
	public function refuseUserAction() {
		$request = $this->get('request');
		
		try {		
		    $login = $this->container->get('ml.session')->sessionExist($request);
		}
		catch (\Exception $e) {
		    return $this->redirect($this->generateUrl('ml_user_add'));		    
		}
		
		$group_id = $request->request->get("group_id");
		
		if ($group_id == NULL) {
			$this->redirect($this->generateUrl('ml_home_homepage'));
		}
		
		$group = $this->getDoctrine()
			->getRepository('MlGroupBundle:Groupp')
			->findOneById($group_id);
		
		if ($group == NULL) {
			return $this->redirect($this->generateUrl('ml_home_homepage'));
		}
			
		$user = $this->getDoctrine()
			->getRepository('MlUserBundle:User')
			->findOneById($request->request->get("user_id"));
		
		$group_user = $this->getDoctrine()
			->getRepository('MlGroupBundle:GroupUser')
			->findOneBy(array('user' => $user, 'groupp' => $group));
		
		$em = $this->getDoctrine()->getManager();
		$em->remove($group_user);
		$em->flush();
		
		$group_id = (int)$group_id;
		
		return $this->redirect($this->generateUrl('ml_group_display_group', array('group_id' => $group_id)));
	}
	
	/**
	 * Delete a user from the group because he decided to leave the group
	 * @return Redirection to ml_home_homepage
	 */
	public function leaveGroupAction($group_id = null) {
		$request = $this->get('request');
		
		try {		
		    $login = $this->container->get('ml.session')->sessionExist($request);
		}
		catch (\Exception $e) {
		    return $this->redirect($this->generateUrl('ml_user_add'));		    
		}
		
		if ($group_id == NULL) {
			$this->redirect($this->generateUrl('ml_home_homepage'));
		}
		
		if ($login != NULL) {
			$user = $this->getDoctrine()
					->getRepository('MlUserBundle:User')
					->findOneByLogin($login);
			
			$group_data = $this->getDoctrine()
				->getRepository('MlGroupBundle:Groupp')
				->findOneById($group_id);	
				
			if (isset($group_data)) {				
				if ($group_data->getAdministrator() == $user) {
					return $this->redirect($this->generateUrl('ml_home_homepage'));
				}
				
				$group_current_user_current = $this->getDoctrine()
					->getRepository('MlGroupBundle:GroupUser')
					->findOneBy(array('groupp' => $group_data, 'user' => $user));
					
				if ($group_current_user_current == NULL) {
					return $this->redirect($this->generateUrl('ml_home_homepage'));
				}
				
				 $em = $this->getDoctrine()->getManager();
				 $em->remove($group_current_user_current);
				 $em->flush();
				 
				 // Set NULL associatedGroup to him created services
				$associated_services = NULL;
				
				
				$associated_basic = $this->getDoctrine()
					->getRepository('MlServiceBundle:Basic')
					->findBy(array("associatedGroup" => $group_data, "user" => $user));
					
				if ($associated_basic != NULL) {
					foreach ($associated_basic as $key => $value) {
						$associated_services[] = $value;
					}
				}
				
				$associated_carpoolings = $this->getDoctrine()
					->getRepository('MlServiceBundle:Carpooling')
					->findBy(array("associatedGroup" => $group_data, "user" => $user));
					
				if ($associated_carpoolings != NULL) {
					foreach ($associated_carpoolings as $key => $value) {
						$associated_services[] = $value;
					}
				}
				
				$associated_couchsurfings = $this->getDoctrine()
					->getRepository('MlServiceBundle:CouchSurfing')
					->findBy(array("associatedGroup" => $group_data, "user" => $user));
					
				if ($associated_couchsurfings != NULL) {
					foreach ($associated_couchsurfings as $key => $value) {
						$associated_services[] = $value;
					}
				}
				
				$associated_sales = $this->getDoctrine()
					->getRepository('MlServiceBundle:Sale')
					->findBy(array("associatedGroup" => $group_data, "user" => $user));
					
				if ($associated_sales != NULL) {
					foreach ($associated_sales as $key => $value) {
						$associated_services[] = $value;
					}
				}
				
				if ($associated_services != NULL) {
					foreach ($associated_services as $key => $value) {
						$value->setAssociatedGroup(NULL);
						
						$em->persist($value);
						$em->flush();
					}
				}
				
				return $this->redirect($this->generateUrl('ml_home_homepage'));
			}
			else {
				return $this->redirect($this->generateUrl('ml_home_homepage'));
			}
		}
		else {
			return $this->redirect($this->generateUrl('ml_home_homepage'));
		}
	}
	
	/**
	 * Delete a group and all its data (set associatedGroup of Services to NULL)
	 * @return Redirection to ml_home_homepage
	 */
	public function deleteGroupAction($group_id = NULL) {
		$request = $this->get('request');
		
		try {		
		    $login = $this->container->get('ml.session')->sessionExist($request);
		}
		catch (\Exception $e) {
		    return $this->redirect($this->generateUrl('ml_user_add'));		    
		}
		
		if ($group_id == NULL) {
			$this->redirect($this->generateUrl('ml_home_homepage'));
		}
				
		$em = $this->getDoctrine()->getManager();	
		
		$group = $this->getDoctrine()
			->getRepository('MlGroupBundle:Groupp')
			->findOneById($group_id);
		
		if ($group == NULL) {
			return $this->redirect($this->generateUrl('ml_home_homepage'));
		}
		
		// Members and moderators
		$group_user = $this->getDoctrine()
			->getRepository('MlGroupBundle:GroupUser')
			->findByGroupp($group);
		
		if ($group_user != NULL) {
			foreach ($group_user as $key => $value) {
				$em->remove($value);
				$em->flush();
			}
		}
		
		 // Set NULL associatedGroup to created services
		$associated_services = NULL;
		
		$associated_basic = $this->getDoctrine()
			->getRepository('MlServiceBundle:Basic')
			->findByAssociatedGroup($group);
			
		if ($associated_basic != NULL) {
			foreach ($associated_basic as $key => $value) {
				$associated_services[] = $value;
			}
		}
		
		$associated_carpoolings = $this->getDoctrine()
			->getRepository('MlServiceBundle:Carpooling')
			->findByAssociatedGroup($group);
			
		if ($associated_carpoolings != NULL) {
			foreach ($associated_carpoolings as $key => $value) {
				$associated_services[] = $value;
			}
		}
		
		$associated_couchsurfings = $this->getDoctrine()
			->getRepository('MlServiceBundle:CouchSurfing')
			->findByAssociatedGroup($group);
			
		if ($associated_couchsurfings != NULL) {
			foreach ($associated_couchsurfings as $key => $value) {
				$associated_services[] = $value;
			}
		}
		
		$associated_sales = $this->getDoctrine()
			->getRepository('MlServiceBundle:Sale')
			->findByAssociatedGroup($group);
			
		if ($associated_sales != NULL) {
			foreach ($associated_sales as $key => $value) {
				$associated_services[] = $value;
			}
		}
		
		if ($associated_services != NULL) {
			foreach ($associated_services as $key => $value) {
				$value->setAssociatedGroup(NULL);
				
				$em->persist($value);
				$em->flush();
			}
		}

		// Group itself
		$em->remove($group);
		$em->flush();
		
		return $this->redirect($this->generateUrl('ml_home_homepage'));
	}
}

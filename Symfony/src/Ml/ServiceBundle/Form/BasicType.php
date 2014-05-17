<?php
 
 namespace Ml\ServiceBundle\Form;
 
 use Symfony\Component\Form\AbstractType;
 use Symfony\Component\Form\FormBuilderInterface;
 use Symfony\Component\OptionsResolver\OptionsResolverInterface;
 
 class BasicType extends AbstractType
 {
	  /**
      * @param FormBuilderInterface $builder
      * @param array $options
      */
     public function buildForm(FormBuilderInterface $builder, array $options)
     {
 		global $kernel;
 	
 		/* Test connexion */
 		$req = $kernel
 			    ->getContainer()
 				->get('request');		
 		try {		
 		    $login = $kernel
 			    ->getContainer()
 				->get('ml.session')
 				->sessionExist($req);
 		}
 		catch (\Exception $e) {
 		    return $kernel
 			    ->getContainer()
 				->redirect($kernel
 			    ->getContainer()->generateUrl('ml_user_add'));		    
 		}
 		
 		if ($login == NULL) {
 			return $kernel
 			    ->getContainer()
 				->redirect($kernel
 			    ->getContainer()->generateUrl('ml_user_add'));
 		}
 		
 		$user = $kernel
 			    ->getContainer()
 			    ->get('doctrine.orm.entity_manager')
 				->getRepository('MlUserBundle:User')
 				->findOneByLogin($login);
 	
 		$groups_user = $kernel
 				  ->getContainer()
 				  ->get('doctrine.orm.entity_manager')
 				  ->getRepository('MlGroupBundle:GroupUser')
 			      ->findByUser($user);
 		
 		$groups_administrator = $kernel
 				  ->getContainer()
 				  ->get('doctrine.orm.entity_manager')
 				  ->getRepository('MlGroupBundle:Groupp')
 			      ->findByAdministrator($user);
 		
 		if ($groups_user == NULL) {
 			$groups_user = NULL;
 		}
 		
 		if ($groups_administrator == NULL) {
 			$groups_administrator = NULL;
 		}
 		
 		$groups_name = NULL;
 		
 		if ($groups_user != NULL) {
 			foreach ($groups_user as $key => $value) {
 				$groups[] = $value->getGroupp();
 			}
 			
 			foreach ($groups as $key => $value) {
 				$groups_name[$value->getName()] = $value->getName();
 			}
 		}
 		
 		if ($groups_administrator != NULL) {
 			foreach ($groups_administrator as $key => $value) {
 				$groups_a[] = $value;
 			}
 			
 			foreach ($groups_a as $key => $value) {
 				$groups_name[$value->getName()] = $value->getName();
 			}
 		}
 		
         $builder
            ->add('title', 'text', array(
									'label' => 'Titre'))
            ->add('comment', 'text', array(
									'label' => 'Commentaire'))
            ->add('price', 'integer', array(
									'label' => 'Prix'))
 			->add('associatedGroup', 'choice', array( 
 													'choices' => $groups_name,
 													'required' => false,
 													'mapped' => false,
													'label' => 'Groupe associé'))
         ;
     }
     
     /**
      * @param OptionsResolverInterface $resolver
      */
	 public function setDefaultOptions(OptionsResolverInterface $resolver)
     {
         $resolver->setDefaults(array(
             'data_class' => 'Ml\ServiceBundle\Entity\Basic'
         ));
     }
 
     /**
      * @return string
      */
     public function getName()
     {
         return 'ml_servicebundle_basic';
     }
 }
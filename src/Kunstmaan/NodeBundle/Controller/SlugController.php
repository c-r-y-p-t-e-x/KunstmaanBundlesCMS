<?php

namespace Kunstmaan\ViewBundle\Controller;

use Kunstmaan\AdminNodeBundle\Modules\NodeMenu;

use Kunstmaan\AdminBundle\Entity\PageIFace;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SlugController extends Controller
{
	/**
	 * @Route("/draft/{slug}", requirements={"slug" = ".+"}, name="_slug_draft")
	 * @Template("KunstmaanViewBundle:Slug:slug.html.twig")
	 */
	public function slugDraftAction($slug)
	{
		$em = $this->getDoctrine()->getEntityManager();
    	$request = $this->getRequest();
    	$locale = $request->getSession()->getLocale();
    	$nodeTranslation = $em->getRepository('KunstmaanAdminNodeBundle:NodeTranslation')->getNodeTranslationForSlug(null, $slug);
    	if($nodeTranslation){
            $page = $nodeTranslation->getNodeVersion('draft')->getRef($em);
            $node = $nodeTranslation->getNode();
    	} else {
    		throw $this->createNotFoundException('No page found for slug ' . $slug);
    	}

        //check if the requested node is online, else throw a 404 exception
        if(!$nodeTranslation->isOnline()){
            throw $this->createNotFoundException("The requested page is not online");
        }

        $currentUser = $this->get('security.context')->getToken()->getUser();

        $permissionManager = $this->get('kunstmaan_admin.permissionmanager');
        $canViewPage = $permissionManager->hasPermision($page, $currentUser, 'read', $em);

        if($canViewPage) {
            $nodeMenu = new NodeMenu($this->container, $locale, $node);

        	//render page
            $pageparts = array();
            foreach($page->getPagePartAdminConfigurations() as $pagePartAdminConfiguration){
            	$context = $pagePartAdminConfiguration->getDefaultContext();
            	$pageparts[$context] = $em->getRepository('KunstmaanPagePartBundle:PagePartRef')->getPageParts($page, $context);
            }

            $result = array(
            			'page'      => $page,
            			'pageparts' => $pageparts,
            			'nodemenu'  => $nodeMenu);

            if(method_exists($page, "service")){
            	$page->service($this->container, $request, $result);
            }

            if(method_exists($page, "getDefaultView")){
            	return $this->render($page->getDefaultView(), $result);
            } else {
            	return $result;
            }

        }
        throw $this->createNotFoundException('You do not have suffucient rights to access this page.');
	}

    /**
     * @Route("/{slug}", requirements={"slug" = ".+"}, name="_slug")
     * @Template()
     */
    public function slugAction($slug)
    {
    	$em = $this->getDoctrine()->getEntityManager();
    	$request = $this->getRequest();
    	$locale = $request->getSession()->getLocale();
    	$nodeTranslation = $em->getRepository('KunstmaanAdminNodeBundle:NodeTranslation')->getNodeTranslationForSlug(null, $slug);
    	if($nodeTranslation){
            $page = $nodeTranslation->getPublicNodeVersion()->getRef($em);
            $node = $nodeTranslation->getNode();
    	} else {
    		throw $this->createNotFoundException('No page found for slug ' . $slug);
    	}

        //check if the requested node is online, else throw a 404 exception
        if(!$nodeTranslation->isOnline()){
            throw $this->createNotFoundException("The requested page is not online");
        }

        $currentUser = $this->get('security.context')->getToken()->getUser();

        $permissionManager = $this->get('kunstmaan_admin.permissionmanager');
        $canViewPage = $permissionManager->hasPermision($page, $currentUser, 'read', $em);

        if($canViewPage) {
            $nodeMenu = new NodeMenu($this->container, $locale, $node);

        	//render page
            $pageparts = array();
            foreach($page->getPagePartAdminConfigurations() as $pagePartAdminConfiguration){
            	$context = $pagePartAdminConfiguration->getDefaultContext();
            	$pageparts[$context] = $em->getRepository('KunstmaanPagePartBundle:PagePartRef')->getPageParts($page, $context);
            }

        	$result = array(
            			'page'      => $page,
            			'pageparts' => $pageparts,
            			'nodemenu'  => $nodeMenu);

            if(method_exists($page, "service")){
            	$page->service($this->container, $request, $result);
            }

            if(method_exists($page, "getDefaultView")){
            	return $this->render($page->getDefaultView(), $result);
            } else {
            	return $result;
            }
        }
        throw $this->createNotFoundException('You do not have suffucient rights to access this page.');
    }


}

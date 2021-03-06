<?php

/*
 * This file is part of the CCDNForum AdminBundle
 *
 * (c) CCDN (c) CodeConsortium <http://www.codeconsortium.com/>
 *
 * Available on github <http://www.github.com/codeconsortium/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CCDNForum\AdminBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 *
 * @author Reece Fowell <reece@codeconsortium.com>
 * @version 1.0
 */
class RepairController extends ContainerAware
{

    /**
     *
     * Displays a list of button repair tools.
     *
     * @access public
     * @return RenderResponse
     */
    public function showRepairAction()
    {
        if ( ! $this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('You do not have access to this section.');
        }

        $boardIntegrity 		= $this->container->get('ccdn_forum_forum.repository.board')->getTableIntegrityStatus();
        $topicIntegrity 		= $this->container->get('ccdn_forum_forum.repository.topic')->getTableIntegrityStatus();
        $postIntegrity 			= $this->container->get('ccdn_forum_forum.repository.post')->getTableIntegrityStatus();
        $draftIntegrity			= $this->container->get('ccdn_forum_forum.repository.draft')->getTableIntegrityStatus();
        $subscribedIntegrity	= $this->container->get('ccdn_forum_forum.repository.subscription')->getTableIntegrityStatus();
        $registryIntegrity 		= $this->container->get('ccdn_forum_forum.repository.registry')->getTableIntegrityStatus();

        // setup crumb trail.
        $crumbs = $this->container->get('ccdn_component_crumb.trail')
            ->add($this->container->get('translator')->trans('ccdn_forum_admin.crumbs.dashboard.admin', array(), 'CCDNForumAdminBundle'), $this->container->get('router')->generate('ccdn_component_dashboard_show', array('category' => 'admin')), "sitemap")
            ->add($this->container->get('translator')->trans('ccdn_forum_admin.crumbs.tools.repair', array(), 'CCDNForumAdminBundle'), $this->container->get('router')->generate('ccdn_forum_admin_repair_tools'), "repair");

        return $this->container->get('templating')->renderResponse('CCDNForumAdminBundle:Repair:repair.html.' . $this->getEngine(), array(
            'crumbs' => $crumbs,
            'board_integrity' => $boardIntegrity,
            'topic_integrity' => $topicIntegrity,
            'post_integrity' => $postIntegrity,
            'draft_integrity' => $draftIntegrity,
            'subscribed_integrity' => $subscribedIntegrity,
            'registry_integrity' => $registryIntegrity,
        ));
    }

    /**
     *
     * Restores/Deletes items, marked for removal from db via checkboxes for each
     * item present in a form. This can only be done via a member with role_admin!
     *
     * @access public
     * @return RedirectResponse
     */
    public function bulkAction()
    {
        if ( ! $this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('You do not have access to this section.');
        }

        //
        // Get all the checked item id's.
        //
        $itemIds = array();
        $ids = $_POST;
        foreach ($ids as $itemKey => $itemId) {
            if (substr($itemKey, 0, 6) == 'check_') {
                //
                // Cast the key values to int upon extraction.
                //
                $id = (int) substr($itemKey, 6, (strlen($itemKey) - 6));

                if (is_int($id) == true) {
                    $itemIds[] = $id;
                }
            }
        }

        //
        // Don't bother if there are no checkboxes to process.
        //
        if (count($itemIds) < 1) {
            return new RedirectResponse($this->container->get('router')->generate('ccdn_forum_admin_topic_deleted_show'));
        }

        $user = $this->container->get('security.context')->getToken()->getUser();

        $topics = $this->container->get('ccdn_forum_forum.repository.topic')->findTheseTopicsByIdForModeration($itemIds);

        if ( ! $topics || empty($topics)) {
            $this->container->get('session')->setFlash('notice', $this->container->get('translator')->trans('flash.topic.no_topics_found', array(), 'CCDNForumAdminBundle'));

            return new RedirectResponse($this->container->get('router')->generate('ccdn_forum_admin_topic_deleted_show'));
        }

        if (isset($_POST['submit_close'])) {
            $this->container->get('ccdn_forum_admin.manager.topic')->bulkClose($topics, $user)->flush();
        }
        if (isset($_POST['submit_reopen'])) {
            $this->container->get('ccdn_forum_admin.manager.topic')->bulkReopen($topics)->flush();
        }
        if (isset($_POST['submit_restore'])) {
            $this->container->get('ccdn_forum_admin.manager.topic')->bulkRestore($topics)->flush();
        }
        if (isset($_POST['submit_soft_delete'])) {
            $this->container->get('ccdn_forum_admin.manager.topic')->bulkSoftDelete($topics, $user)->flush();
        }
        if (isset($_POST['submit_hard_delete'])) {
            $this->container->get('ccdn_forum_admin.manager.topic')->bulkHardDelete($topics)->flush();
        }

        return new RedirectResponse($this->container->get('router')->generate('ccdn_forum_admin_topic_deleted_show'));
    }

    /**
     *
     * @access protected
     * @return String
     */
    protected function getEngine()
    {
        return $this->container->getParameter('ccdn_forum_admin.template.engine');
    }

}

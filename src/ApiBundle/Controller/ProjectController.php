<?php
/**
 * Created by PhpStorm.
 * User: evgeny
 * Date: 08.09.15
 * Time: 11:06
 */

namespace ApiBundle\Controller;

use ApiBundle\Exception\InvalidFormException;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Controller\Annotations\View;

use ApiBundle\Entity\Project;
use ApiBundle\Form\ProjectType;
use ApiBundle\Entity\IProject;

class ProjectController extends FOSRestController {

    /**
     * Get project
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Gets project by id",
     *   output = "ApiBundle\Entity\Project",
     *   statusCodes = {
     *     200 = "OK",
     *     404 = "Not found"
     *   }
     * )
     *
     * @Rest\View(templateVar="project")
     *
     * @param int $id
     *
     * @return array
     *
     * @throws NotFoundHttpException
     */
    public function viewAction($id) {
        return $this->loadProject($id);
    }

    /**
     * Create a project
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Creates a new project from the submitted data.",
     *   input = "ApiBundle\Form\ProjectType",
     *   statusCodes = {
     *     201 = "Project created",
     *     400 = "Form has validation errors"
     *   }
     * )
     *
     * @Rest\View(
     *  template = "ApiBundle:Project:create.html.twig",
     *  templateVar = "form"
     * )
     *
     * @param Request $request the request object
     *
     * @return ProjectType|View
     */
    public function createAction(Request $request) {

        // get request params
        $params = $this->getParamsFromRequest($request);

        try {
            /** @var Project $project */
            $project = $this->container->get('api.project.handler')->make($params);

            return $this->routeRedirectView('project_view', array(
                'id' => $project->getId(),
                '_format' => $request->get('_format')
            ), Codes::HTTP_CREATED);

        } catch (InvalidFormException $exception) {
            return $this->view($exception->getForm(), Codes::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Presents the form to use to create a new project.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "OK"
     *   }
     * )
     *
     * @Rest\View(
     *  template = "ApiBundle:Project:create.html.twig",
     *  templateVar = "form"
     * )
     *
     * @return ProjectType
     */
    public function newAction() {
        return $this->createForm(new ProjectType());
    }

    /**
     * Update project from the submitted data or create a new project at a specific location.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "ApiBundle\Form\ProjectType",
     *   statusCodes = {
     *     201 = "Returned when the Project is created",
     *     204 = "Returned when the Project is updated",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Rest\View(
     *  template = "ApiBundle:Project:update.html.twig",
     *  templateVar = "form"
     * )
     *
     * @param Request $request the request object
     * @param int     $id      the project id
     *
     * @return ProjectType
     *
     * @throws NotFoundHttpException when project not exist
     */
    public function putAction(Request $request, $id) {
        try {
            if (!($project = $this->container->get('api.project.handler')->get($id))) {
                $statusCode = Codes::HTTP_CREATED;
                $project = $this->container->get('api.project.handler')->make(
                    $this->getParamsFromRequest($request)
                );
            } else {
                $statusCode = Codes::HTTP_NO_CONTENT;
                $project = $this->container->get('api.project.handler')->put(
                    $project,
                    $this->getParamsFromRequest($request)
                );
            }

            return $this->routeRedirectView('project_view', array(
                'id' => $project->getId(),
                '_format' => $request->get('_format')
            ), $statusCode);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
     * Update existing project from the submitted data.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "ApiBundle\Form\ProjectType",
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Rest\View(
     *  template = "ApiBundle:Project:update.html.twig",
     *  templateVar = "form"
     * )
     *
     * @param Request $request the request object
     * @param int     $id      the project id
     *
     * @return ProjectType|View
     *
     * @throws NotFoundHttpException when project not exist
     */
    public function patchAction(Request $request, $id) {
        try {
            $project = $this->loadProject($id);

            $patchedProject = $this->container->get('api.project.handler')->patch(
                $project,
                $this->getParamsFromRequest($request)
            );

            return $this->routeRedirectView('project_view', array(
                'id' => $patchedProject->getId(),
                '_format' => $request->get('_format')
            ), Codes::HTTP_NO_CONTENT);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }
    /**
     * List all projects.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Rest\QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing projects.")
     * @Rest\QueryParam(name="limit", requirements="\d+", default="5", description="How many projects to return.")
     * @Rest\QueryParam(name="sorting", default="created_at desc", description="Sort order.")
     *
     * @Rest\View(
     *  template = "ApiBundle:Project:list.html.twig",
     *  templateVar="projects"
     * )
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @return array
     */
    public function listAction(Request $request, ParamFetcherInterface $paramFetcher) {

        if(!($offset = $paramFetcher->get('offset'))) {
            $offset = 0;
        }

        $limit = $paramFetcher->get('limit');

        $sorting = $paramFetcher->get('sorting');

        $handler = $this->container->get('api.project.handler');

        return array(
            'projects' => $handler->all($limit, $offset, $sorting),
            'projects_total' => (int)$handler->countAll()
        );

    }

    /**
     * Delete existing project.
     *
     * @ApiDoc(
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     404 = "Project not found"
     *   }
     * )
     *
     * @Rest\View(
     * )
     *
     * @param Request $request the request object
     * @param int     $id      the project id
     *
     * @return View
     *
     * @throws NotFoundHttpException when project not exist
     */
    public function deleteAction(Request $request, $id) {
        $project = $this->loadProject($id);
        $this->container->get('api.project.handler')->delete($project);
        return $this->view(null, Codes::HTTP_NO_CONTENT);
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function getParamsFromRequest(Request $request) {

        if($params = $request->request->get((new ProjectType())->getName())) {
            return $params;
        }

        return array();
    }

    /**
     * @param $id
     * @return IProject
     */
    protected function loadProject($id) {

        $project = $this->container
            ->get('api.project.handler')
            ->get($id);

        if(!$project) {
            throw new NotFoundHttpException(sprintf("Project with #%d not found", $id));
        }

        return $project;

    }



}